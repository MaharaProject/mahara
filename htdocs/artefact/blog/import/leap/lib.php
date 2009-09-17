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
 * Implements LEAP2A import of blog related entries into Mahara
 *
 * For more information about LEAP blog importing, see:
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

    public static function get_import_strategies_for_entry(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $strategies = array();

        if (PluginImportLeap::is_rdf_type($entry, $importer, 'selection')
            && PluginImportLeap::is_correct_category_scheme($entry, $importer, 'selection_type', 'Blog')) {
            $otherrequiredentries = array();

            // Get entries that this blog feels are a part of it
            foreach ($entry->link as $link) {
                if ($importer->curie_equals($link['rel'], PluginImportLeap::NS_LEAP, 'has_part') && isset($link['href'])) {
                    $otherrequiredentries[] = (string)$link['href'];
                }
            }

            // TODO: Get entries that feel they should be a part of this blog. 
            // We can compare the lists and perhaps warn if they're different
            //    $otherentries = $importer->xml->xpath('//a:feed/a:entry/a:link[@rel="leap:is_part_of" and @href="' . $entryid . '"]/../a:id');

            $otherrequiredentries = array_unique($otherrequiredentries);
            $strategies[] = array(
                'strategy' => self::STRATEGY_IMPORT_AS_BLOG,
                'score'    => 100,
                'other_required_entries' => $otherrequiredentries,
            );
        }
        else {
            // The blog can import any entry as a literal blog post
            $strategies[] = array(
                'strategy' => self::STRATEGY_IMPORT_AS_ENTRY,
                'score'    => 10,
                'other_required_entries' => array(),
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
        switch ($strategy) {
        case self::STRATEGY_IMPORT_AS_BLOG:
            foreach ($otherentries as $entryid) {
                $blogpostentry = $importer->get_entry_by_id($entryid);
                // Get all attachments this blogpost things are attached to it
                // TODO: get all entries that think they're attached to the blogpost.
                // I think we can only look for files, Mahara doesn't understand 
                // attaching something that isn't a file to a blogpost
                if (!isset($blogpost->link)) {
                    continue;
                }
                foreach ($blogpostentry->link as $blogpostlink) {
                    $blogpost = null;
                    if ($importer->curie_equals($blogpostlink['rel'], PluginImportLeap::NS_LEAP, 'has_attachment') && isset($blogpostlink['href'])) {

                        if (!$blogpost) {
                            $artefactids = $importer->get_artefactids_imported_by_entryid((string)$blogpostentry->id);
                            $blogpost = new ArtefactTypeBlogPost($artefactids[0]);
                        }
                        $importer->trace("Attaching file $blogpostlink[href] to blog post $blogpostentry->id", PluginImportLeap::LOG_LEVEL_VERBOSE);
                        $artefactids = $importer->get_artefactids_imported_by_entryid((string)$blogpostlink['href']);
                        $blogpost->attach($artefactids[0]);
                    }
                    if ($blogpost) {
                        $blogpost->commit();
                    }
                }

                self::setup_outoflinecontent_relationship($blogpostentry, $importer);
            }
            break;
        case self::STRATEGY_IMPORT_AS_ENTRY:
            self::setup_outoflinecontent_relationship($entry, $importer);
            break;
        default:
            throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
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
        if (isset($entry->content['src']) && isset($entry->content['type'])) {
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

?>
