<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-blog-import-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Implements Leap2A import of blog related entries into Mahara
 *
 * For more information about Leap blog importing, see:
 * http://wiki.mahara.org/Developer_Area/Import//Export/LEAP_Import/Blog_Artefact_Plugin
 *
 * TODO:
 * - Get entries that feel they're part of the blog, not just entries the blog feels are part of it
 * - Import raw ATOM feed entries as blog posts
 */
class LeapImportBlog extends LeapImportArtefactPlugin {

    /**
     * Import an entry as a blog, with associated blog posts and attachments
     */
    const STRATEGY_IMPORT_AS_BLOG = 1;

    /**
     * Import entry as an simple blog post into a catch-all blog
     */
    const STRATEGY_IMPORT_AS_ENTRY = 2;

    private static $firstblogid = null;
    private static $importedablog = false;

    /**
     * Users get a default blog when they're created. We don't want this user
     * to have one if their import includes a blog. So we remember the default
     * blog ID here in order to delete it later if necessary.
     */
    public static function setup(PluginImportLeap $importer) {
        self::$firstblogid = get_field('artefact', 'id', 'owner', $importer->get('usr'), 'artefacttype', 'blog');
    }

    public static function get_import_strategies_for_entry(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $strategies = array();

        if (PluginImportLeap::is_rdf_type($entry, $importer, 'selection')) {
            if (PluginImportLeap::is_correct_category_scheme($entry, $importer, 'selection_type', 'Blog')) {
                $score = 100;
            } else {
                // the blog plugin can either fall back to importing single entries
                // or handle the case where things are a selection that have no other strategies either.
                // however, in the case where the otherrequiredentries for the selection have a higher strategy elsewhere,
                // we need to still fallback to importing a selection post as a blog post by itself, to avoid dataloss.
                $score = 20; // other things *can* be imported as blogs
            }
            $otherrequiredentries = array();

            // Get entries that this blog/selection feels are a part of it
            foreach ($entry->link as $link) {
                if ($importer->curie_equals($link['rel'], $importer->get_leap2a_namespace(), 'has_part') && isset($link['href'])) {
                    $otherrequiredentries[] = (string)$link['href'];
                }
            }

            // TODO: Get entries that feel they should be a part of this blog/selection
            // We can compare the lists and perhaps warn if they're different
            //    $otherentries = $importer->xml->xpath('//a:feed/a:entry/a:link[@rel="leap:is_part_of" and @href="' . $entryid . '"]/../a:id');

            $otherrequiredentries = array_unique($otherrequiredentries);
            $strategies[] = array(
                'strategy' => self::STRATEGY_IMPORT_AS_BLOG,
                'score'    => $score,
                'other_required_entries' => $otherrequiredentries,
            );
            if ($score == 20) {
                $strategies[] = array(
                    'strategy' => self::STRATEGY_IMPORT_AS_ENTRY,
                    'score'    => 10,
                    'other_required_entries' => array(),
                );
            }
        }
        else {
            // The blog can import any entry as a literal blog post
            // Get files that this blogpost/catchall feels are a part of it
            $otherrequiredentries = array();
            foreach ($entry->link as $link) {
                if ($importer->curie_equals($link['rel'], '', 'enclosure') && isset($link['href'])) {
                    $otherrequiredentries[] = (string)$link['href'];
                }
            }
            $strategies[] = array(
                'strategy' => self::STRATEGY_IMPORT_AS_ENTRY,
                'score'    => 10,
                'other_required_entries' => $otherrequiredentries,
            );
        }

        return $strategies;
    }

    public static function import_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $artefactmapping = array();
        switch ($strategy) {
        case self::STRATEGY_IMPORT_AS_BLOG:
            // First, the blog
            $blog = new ArtefactTypeBlog();
            $blog->set('title', (string)$entry->title);
            $blog->set('description', PluginImportLeap::get_entry_content($entry, $importer));
            $blog->set('owner', $importer->get('usr'));
            if ($published = strtotime((string)$entry->published)) {
                $blog->set('ctime', $published);
            }
            if ($updated = strtotime((string)$entry->updated)) {
                $blog->set('mtime', $updated);
            }
            $blog->set('tags', PluginImportLeap::get_entry_tags($entry));
            $blog->commit();
            $artefactmapping[(string)$entry->id] = array($blog->get('id'));
            self::$importedablog = true;

            // Then, the blog posts
            foreach ($otherentries as $entryid) {
                $blogentry = $importer->get_entry_by_id($entryid);
                if (!$blogentry) {
                    // TODO: what to do here? Also - should this be checked here or earlier?
                    $importer->trace("WARNING: Blog $entry->id claims to have part $entryid which doesn't exist");
                    continue;
                }

                $artefactmapping[$entryid] = self::create_blogpost($blogentry, $importer, $blog->get('id'));
            }
            break;
        case self::STRATEGY_IMPORT_AS_ENTRY:
            $blogid = self::ensure_catchall_blog($importer);
            $artefactmapping[(string)$entry->id] = self::create_blogpost($entry, $importer, $blogid);
            break;
        default:
            throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
        }
        return $artefactmapping;
    }

    /**
     * Attaches files to blog posts
     *
     * We look at the leap relationships to add attachments. Currently this 
     * looks explicitly for the has_attachment relationship.
     *
     * If importing an entry resulted in importing a new file (caused by the 
     * entry having out-of-line content), we attach that file to the entry.
     */
    public static function setup_relationships(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $newartefactmapping = array();
        switch ($strategy) {
        case self::STRATEGY_IMPORT_AS_BLOG:
            foreach ($otherentries as $entryid) {
                $blogpostentry = $importer->get_entry_by_id($entryid);
                // Get all attachments this blogpost things are attached to it
                if (!isset($blogpostentry->link)) {
                    continue;
                }
                $blogpost = null;
                foreach ($blogpostentry->link as $blogpostlink) {
                    if (!$blogpost) {
                        $artefactids = $importer->get_artefactids_imported_by_entryid((string)$blogpostentry->id);
                        $blogpost = new ArtefactTypeBlogPost($artefactids[0]);
                    }
                    if ($id = $importer->create_attachment($entry, $blogpostlink, $blogpost)) {
                        $newartefactmapping[$link['href']][] = $id;
                    }
                    if ($blogpost) {
                        $blogpost->commit();
                    }
                }

                self::setup_outoflinecontent_relationship($blogpostentry, $importer);
            }
            break;
        case self::STRATEGY_IMPORT_AS_ENTRY:
            $blogpostids = $importer->get_artefactids_imported_by_entryid((string)$entry->id);
            if (!isset($blogpostids[0])) {
                // weird!
                break;
            }
            $blogpost = new ArtefactTypeBlogPost($blogpostids[0]);
            foreach ($entry->link as $link) {
                if ($id = $importer->create_attachment($entry, $link, $blogpost)) {
                    $newartefactmapping[$link['href']][] = $id;
                }
            }
            $blogpost->commit();
            self::setup_outoflinecontent_relationship($entry, $importer);
            break;
        default:
            throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
        }
        return $newartefactmapping;
    }

    /**
     * Attaches a file to a blogpost entry that was just linked directly, rather than having a Leap2a entry
     * See http://wiki.leapspecs.org/2A/files
     *
     * @param SimpleXMLElement $blogpostentry
     * @param SimpleXMLElement $blogpostlink
     * @param PluginImportLeap $importer
     */
    private static function attach_linked_file($blogpostentry, $blogpostlink, PluginImportLeap $importer) {
        $importer->trace($blogpostlink);
        $pathname = urldecode((string)$blogpostlink['href']);
        $dir = dirname($importer->get('filename'));
        $pathname = $dir . '/' . $pathname;
        if (!file_exists($pathname)) {
            return false;
        }
        // Note: this data is passed (eventually) to ArtefactType->__construct,
        // which calls strtotime on the dates for us
        require_once('file.php');
        $data = (object)array(
            'title' => (string)$blogpostentry->title . ' ' . get_string('attachment', 'artefact.blog'),
            'owner' => $importer->get('usr'),
            'filetype' => file_mime_type($pathname),
        );
        return ArtefactTypeFile::save_file($pathname, $data, $importer->get('usrobj'), true);
    }

    /**
     * Deletes the default blog that is created for all users
     */
    public static function cleanup(PluginImportLeap $importer) {
        if (self::$importedablog && self::$firstblogid) {
            $blog = artefact_instance_from_id(self::$firstblogid);
            if (!$blog->has_children()) { // TODO see #544160
                $blog->delete();
            }
        }
        $userid = $importer->get('usr');
        if (count_records('artefact', 'artefacttype', 'blog', 'owner', $userid) != 1) {
            set_account_preference($userid, 'multipleblogs', 1);
        }
    }

    /**
     * Creates a catch-all blog if one doesn't exist already
     *
     * @param PluginImportLeap $importer The importer
     * @return int The artefact ID of the catch-all blog
     */
    private static function ensure_catchall_blog(PluginImportLeap $importer) {
        static $blogid = null;
        if (is_null($blogid)) {
            $time = time(); // TODO maybe the importer will get a time field to record time of import
            $blog = new ArtefactTypeBlog();
            $title = $importer->get('xml')->xpath('//a:feed/a:title');
            $blog->set('title', get_string('dataimportedfrom', 'artefact.blog', (string)$title[0]));
            $blog->set('description', get_string('entriesimportedfromleapexport', 'artefact.blog'));
            $blog->set('owner', $importer->get('usr'));
            $blog->set('ctime', $time);
            $blog->set('mtime', $time);
            $blog->commit();
            $blogid = $blog->get('id');
            self::$importedablog = true;
        }

        return $blogid;
    }

    /**
     * Creates a blogpost from the given entry
     *
     * @param SimpleXMLElement $entry    The entry to create the blogpost from
     * @param PluginImportLeap $importer The importer
     * @param int $blogid                The blog in which to put the post
     * @return array A list of artefact IDs created, to be used with the artefact mapping. 
     *               There will either be one (the blogpost ID), or two. If there is two, the 
     *               second one will be the ID of the file created to hold the out-of-line 
     *               content associated with the blogpost
     */
    private static function create_blogpost(SimpleXMLElement $entry, PluginImportLeap $importer, $blogid) {
        $createdartefacts = array();
        $blogpost = new ArtefactTypeBlogPost();
        $blogpost->set('title', (string)$entry->title);
        // If the entry has out of line content, we import that separately as a 
        // file and set the content to refer to it
        if (LeapImportFile::is_file($entry, $importer)) {
            $file = LeapImportFile::create_file($entry, $importer);
            $createdartefacts[] = $file->get('id');

            $content = '<a href="' . get_config('wwwroot') . 'artefact/file/download.php?file=' . $file->get('id') . '"'
                . ' title="' . hsc($file->get('title')) . '">';
            if (is_image_mime_type($file->get('filetype'))) {
                $content .= '<img src="' . get_config('wwwroot') 
                    . 'artefact/file/download.php?file=' . $file->get('id') . '&amp;maxwidth=500&amp;maxheight=500"'
                    . ' alt="' . hsc($file->get('title')) . '">';
            }
            $content .= '</a>';
            $blogpost->set('description', $content);
        }
        else {
            $description = PluginImportLeap::get_entry_content($entry, $importer);
            $type = isset($entry->content['type']) ? (string)$entry->content['type'] : 'text';
            if ($type == 'text') {
                $description = format_whitespace($description);
            }
            $blogpost->set('description', $description);
        }
        if ($published = strtotime((string)$entry->published)) {
            $blogpost->set('ctime', $published);
        }
        if ($updated = strtotime((string)$entry->updated)) {
            $blogpost->set('mtime', $updated);
        }

        $draftpost = PluginImportLeap::is_correct_category_scheme($entry, $importer, 'readiness', 'Unready');
        $blogpost->set('published', $draftpost ? 0 : 1);

        $blogpost->set('owner', $importer->get('usr'));
        $blogpost->set('parent', $blogid);
        $blogpost->set('tags', PluginImportLeap::get_entry_tags($entry));
        $blogpost->commit();
        array_unshift($createdartefacts, $blogpost->get('id'));

        return $createdartefacts;
    }

    /**
     * Checks to see if a blogpost had out-of-line content, and if it did, 
     * attaches the generated file to it
     *
     * @param SimpleXMLElement $entry    The entry to check
     * @param PluginImportLeap $importer The importer
     */
    private static function setup_outoflinecontent_relationship(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $artefactids = $importer->get_artefactids_imported_by_entryid((string)$entry->id);
        if (count($artefactids) == 2) {
            // In this case, a file was created as a result of 
            // importing a blog entry with out-of-line content. We 
            // attach the file to this post.
            $blogpost = new ArtefactTypeBlogPost($artefactids[0]);
            $blogpost->attach($artefactids[1]);
            $blogpost->commit();
        }
    }

}
