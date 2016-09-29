<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-blog-import-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Implements Leap2A import of blog related entries into Mahara
 *
 * For more information about Leap blog importing, see:
 * https://wiki.mahara.org/wiki/Developer_Area/Import//Export/LEAP_Import/Blog_Artefact_Plugin
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

    private static function get_blog_entry_data_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        // First, the blog
        $config = array(
            'owner'   => $importer->get('usr'),
            'type'    => 'blog',
            'content' => array(
                'title'       => (string)$entry->title,
                'description' => PluginImportLeap::get_entry_content($entry, $importer),
                'owner'       => $importer->get('usr'),
                'ctime'       => (string)$entry->published,
                'mtime'       => (string)$entry->updated,
                'tags'        => PluginImportLeap::get_entry_tags($entry),
            ),
        );


        // Then, the blog posts
        $config['blogentries'] = array();
        foreach ($otherentries as $entryid) {
            $blogentry = $importer->get_entry_by_id($entryid);
            if (!$blogentry) {
                // TODO: what to do here? Also - should this be checked here or earlier?
                $importer->trace("WARNING: Blog $entry->id claims to have part $entryid which doesn't exist");
                continue;
            }
            $config['blogentries'][] = array(
                    'blogentry' => $blogentry,
                    'oldentryid' => $entryid,
                    'newentryid' => (string)$entry->id
            );
        }
        return $config;
    }

    public static function add_import_entry_request_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $importid = $importer->get('importertransport')->get('importid');
        switch ($strategy) {
        case self::STRATEGY_IMPORT_AS_BLOG:
            $config = self::get_blog_entry_data_using_strategy($entry, $importer, $strategy, $otherentries);
            PluginImportLeap::add_import_entry_request($importid, (string)$entry->id, $strategy, 'blog', array(
                'owner'   => $config['owner'],
                'type'    => $config['type'],
                'content' => $config['content']
            ));
            foreach ($config['blogentries'] as $item) {
                self::add_import_entry_request_blogpost($item['blogentry'], $importer, $item['newentryid']);
            }
            break;
        case self::STRATEGY_IMPORT_AS_ENTRY:
            $catchallblogid = self::add_import_entry_request_catchall_blog($importer);
            self::add_import_entry_request_blogpost($entry, $importer, $catchallblogid);
            break;
        default:
            throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
        }
    }

/**
 * Import from entry requests for Mahara blogs
 *
 * @param PluginImportLeap $importer
 * @return updated DB
 * @throw    ImportException
 */
    public static function import_from_requests(PluginImportLeap $importer) {
        $importid = $importer->get('importertransport')->get('importid');
        if ($entry_requests = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importid, 'blog'))) {
            foreach ($entry_requests as $entry_request) {
                if ($blogid = self::create_artefact_from_request($importer, $entry_request)) {
                    if ($blogpost_requests = get_records_select_array('import_entry_requests', 'importid = ? AND entryparent = ? AND entrytype = ?', array($importid, $entry_request->entryid, 'blogpost'))) {
                        foreach ($blogpost_requests as $blogpost_request) {
                            self::create_artefact_from_request($importer, $blogpost_request, $blogid);
                        }
                    }
                }
            }
        }
    }

    public static function import_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $artefactmapping = array();
        switch ($strategy) {
        case self::STRATEGY_IMPORT_AS_BLOG:
            $config = self::get_blog_entry_data_using_strategy($entry, $importer, $strategy, $otherentries);

            // First, the blog
            $blog = new ArtefactTypeBlog();
            $blog->set('title', $config['content']['title']);
            $blog->set('description', $config['content']['description']);
            $blog->set('owner', $config['owner']);
            if ($config['content']['ctime']) {
                $blog->set('ctime', $config['content']['ctime']);
            }
            if ($config['content']['mtime']) {
                $blog->set('mtime', $config['content']['mtime']);
            }
            $blog->set('tags', $config['content']['tags']);
            $blog->commit();
            $artefactmapping[(string)$entry->id] = array($blog->get('id'));
            self::$importedablog = true;

            // Then, the blog posts
            foreach ($config['blogentries'] as $item) {
                $artefactmapping[$item['oldentryid']] = self::create_blogpost(
                        $item['blogentry'],
                        $importer,
                        $blog->get('id')
                );
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
     */
    public static function setup_relationships_from_requests(PluginImportLeap $importer) {
        $importid = $importer->get('importertransport')->get('importid');
        if ($blogpost_requests = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importid, 'blogpost'))) {
            foreach ($blogpost_requests as $blogpost_request) {
                $blogpostentry = $importer->get_entry_by_id($blogpost_request->entryid);
                // Get all attachments this blogpost things are attached to it
                if (!isset($blogpostentry->link)) {
                    continue;
                }
                if ($blogpostids = $importer->get_artefactids_imported_by_entryid($blogpost_request->entryid)) {
                    $blogpost = new ArtefactTypeBlogPost($blogpostids[0]);
                    foreach ($blogpostentry->link as $blogpostlink) {
                        $importer->create_attachment($blogpostentry, $blogpostlink, $blogpost);
                    }
                    $blogpost->commit();
                }
                self::setup_outoflinecontent_relationship($blogpostentry, $importer);
            }
        }
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
     * Render import entry requests for Mahara blogs and their blogposts
     * @param PluginImportLeap $importer
     * @return HTML code for displaying blogs and choosing how to import them
     */
    public static function render_import_entry_requests(PluginImportLeap $importer) {
        $importid = $importer->get('importertransport')->get('importid');
        // Get import entry requests for Mahara blogs
        $entryblogs = array();
        if ($ierblogs = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importid, 'blog'))) {
            foreach ($ierblogs as $ierblog) {
                $blog = unserialize($ierblog->entrycontent);
                $blog['id'] = $ierblog->id;
                $blog['decision'] = $ierblog->decision;
                if (is_string($ierblog->duplicateditemids)) {
                    $ierblog->duplicateditemids = unserialize($ierblog->duplicateditemids);
                }
                if (is_string($ierblog->existingitemids)) {
                    $ierblog->existingitemids = unserialize($ierblog->existingitemids);
                }
                $blog['disabled'][PluginImport::DECISION_IGNORE] = false;
                $blog['disabled'][PluginImport::DECISION_ADDNEW] = false;
                $blog['disabled'][PluginImport::DECISION_APPEND] = true;
                $blog['disabled'][PluginImport::DECISION_REPLACE] = true;
                if (!empty($ierblog->duplicateditemids)) {
                    $duplicated_blog = artefact_instance_from_id($ierblog->duplicateditemids[0]);
                    $blog['duplicateditem']['id'] = $duplicated_blog->get('id');
                    $blog['duplicateditem']['title'] = $duplicated_blog->get('title');
                    $res = $duplicated_blog->render_self(array());
                    $blog['duplicateditem']['html'] = $res['html'];
                }
                else if (!empty($ierblog->existingitemids)) {
                    foreach ($ierblog->existingitemids as $id) {
                        $existing_blog = artefact_instance_from_id($id);
                        $res = $existing_blog->render_self(array());
                        $blog['existingitems'][] = array(
                            'id'    => $existing_blog->get('id'),
                            'title' => $existing_blog->get('title'),
                            'html'  => $res['html'],
                        );
                    }
                }
                // Get import entry requests of blogposts in the blog
                $entryposts = array();
                if ($ierposts = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ? AND entryparent = ?',
                        array($importid, 'blogpost', $ierblog->entryid))) {
                    foreach ($ierposts as $ierpost) {
                        $post = unserialize($ierpost->entrycontent);
                        $post['id'] = $ierpost->id;
                        $post['decision'] = $ierpost->decision;
                        if (is_string($ierpost->duplicateditemids)) {
                            $ierpost->duplicateditemids = unserialize($ierpost->duplicateditemids);
                        }
                        if (is_string($ierpost->existingitemids)) {
                            $ierpost->existingitemids = unserialize($ierpost->existingitemids);
                        }
                        $post['disabled'][PluginImport::DECISION_IGNORE] = false;
                        $post['disabled'][PluginImport::DECISION_ADDNEW] = false;
                        $post['disabled'][PluginImport::DECISION_APPEND] = true;
                        $post['disabled'][PluginImport::DECISION_REPLACE] = true;
                        if (!empty($ierpost->duplicateditemids)) {
                            $duplicated_post = artefact_instance_from_id($ierpost->duplicateditemids[0]);
                            $post['duplicateditem']['id'] = $duplicated_post->get('id');
                            $post['duplicateditem']['title'] = $duplicated_post->get('title');
                            $res = $duplicated_post->render_self(array());
                            $post['duplicateditem']['html'] = $res['html'];
                        }
                        else if (!empty($ierpost->existingitemids)) {
                            foreach ($ierpost->existingitemids as $id) {
                                $existing_post = artefact_instance_from_id($id);
                                $res = $existing_post->render_self(array());
                                $post['existingitems'][] = array(
                                    'id'    => $existing_post->get('id'),
                                    'title' => $existing_post->get('title'),
                                    'html'  => $res['html'],
                                );
                            }
                        }
                        $entryposts[] = $post;
                    }
                }
                $blog['entryposts'] = $entryposts;
                $entryblogs[] = $blog;
            }
        }
        $smarty = smarty_core();
        $smarty->assign('displaydecisions', $importer->get('displaydecisions'));
        $smarty->assign('entryblogs', $entryblogs);
        return $smarty->fetch('artefact:blog:import/blogs.tpl');
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
        );
        $data->oldextension = end(explode('.', $data->title));
        return ArtefactTypeFile::save_file($pathname, $data, $importer->get('usrobj'), true);
    }

    /**
     * Deletes the default blog that is created for all users
     */
    public static function cleanup(PluginImportLeap $importer) {
        global $USER;
        if (self::$importedablog && self::$firstblogid) {
            $blog = artefact_instance_from_id(self::$firstblogid);
            if (!$blog->has_children()) { // TODO see #544160
                $blog->delete();
            }
        }
        $userid = $importer->get('usr');
        if (count_records('artefact', 'artefacttype', 'blog', 'owner', $userid) != 1) {
            set_account_preference($userid, 'multipleblogs', 1);
            if ($userid == $USER->get('id')) {
                $USER->set_account_preference('multipleblogs', 1);
            }
        }
    }

    /**
     * Creates a catch-all blog if one doesn't exist already
     * TODO: Refactor this to share logic code with add_import_entry_request_catchall_blog()
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
     * Add a import entry request for a catch-all blog if one doesn't exist already
     * TODO: Refactor this to share logic code with ensure_catchall_blog()
     *
     * @param PluginImportLeap $importer The importer
     * @return int The import entry request ID of the catch-all blog
     */
    private static function add_import_entry_request_catchall_blog(PluginImportLeap $importer) {
        static $blogrequestid = null;
        if (is_null($blogrequestid)) {
            $time = db_format_timestamp(time()); // TODO maybe the importer will get a time field to record time of import
            $title = $importer->get('xml')->xpath('//a:feed/a:title');
            PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), 'portfolio:catchallblog', self::STRATEGY_IMPORT_AS_BLOG, 'blog', array(
                'owner'   => $importer->get('usr'),
                'type'    => 'blog',
                'parent'  => null,
                'content' => array(
                    'title'       => get_string('dataimportedfrom', 'artefact.blog', (string)$title[0]),
                    'description' => get_string('entriesimportedfromleapexport', 'artefact.blog'),
                    'ctime'       => $time,
                    'mtime'       => $time,
                ),
            ));
        }

        return 'portfolio:catchallblog';
    }

    private static function get_blogpost_entry_data(SimpleXMLElement $entry, PluginImportLeap $importer, $blogentryid) {
        // If the entry has out of line content, we import that separately as a
        // file and set the content to refer to it
        if (LeapImportFile::is_file($entry, $importer)) {
            $isfile = true;
        }
        else {
            $isfile = false;
            $description = PluginImportLeap::get_entry_content($entry, $importer);
            $type = isset($entry->content['type']) ? (string)$entry->content['type'] : 'text';
            if ($type == 'text') {
                $description = format_whitespace($description);
            }
        }
        return $config = array(
            'isfile' => $isfile,
            'owner'   => $importer->get('usr'),
            'type'    => 'blogpost',
            'parent'  => $blogentryid,
            'content' => array(
                'title'       => (string)$entry->title,
                'description' => isset($description) ? $description : null,
                // TODO: Support for this "file" mode in interactive import
//                 'file'        => isset($file) ? $file : null,
                'files'       => self::add_files_to_import_entry_request_blogpost($entry, $importer, $blogentryid),
                'ctime'       => (string)$entry->published,
                'mtime'       => (string)$entry->updated,
                'published'   => PluginImportLeap::is_correct_category_scheme($entry, $importer, 'readiness', 'Unready') ? 0 : 1,
                'tags'        => PluginImportLeap::get_entry_tags($entry),
            ),
        );
    }

    /**
     * Add file attachment information to import entry request for a blogpost
     *
     * @param SimpleXMLElement $entry    The entry to create the blogpost from
     * @param PluginImportLeap $importer The importer
     * @param int $blogentryid         The ID of the import entry of the blog in which to put the post
     */
    private static function add_files_to_import_entry_request_blogpost(SimpleXMLElement $entry, PluginImportLeap $importer, $blogentryid) {
        $files = array();
        if (isset($entry->link)) {
            foreach ($entry->link as $link) {
                if ($importer->curie_equals($link['rel'], '', 'related') && isset($link['href'])) {
                    if ($has_attachments = get_records_select_array('import_entry_requests', 'importid = ? AND entryid = ?', array($importer->get('importertransport')->get('importid'), (string)$link['href']))) {
                        foreach ($has_attachments as $has_attachment) {
                            $attachment = unserialize($has_attachment->entrycontent);
                            $files[] = array('title' => $attachment['title'],
                                             'description' => $attachment['description'],
                                             );
                        }
                    }
                }
            }
        }
        return $files;
    }

    /**
     * Add an import entry request for a blogpost
     *
     * @param SimpleXMLElement $entry    The entry to create the blogpost from
     * @param PluginImportLeap $importer The importer
     * @param int $blogentryid         The ID of the import entry of the blog in which to put the post
     */
    private static function add_import_entry_request_blogpost(SimpleXMLElement $entry, PluginImportLeap $importer, $blogentryid) {
        $config = self::get_blogpost_entry_data($entry, $importer, $blogentryid);
        if ($config['isfile']) {
            LeapImportFile::add_import_entry_request_file($entry, $importer);
        }
        return PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), (string)$entry->id, self::STRATEGY_IMPORT_AS_ENTRY, 'blog', array(
            'owner'   => $importer->get('usr'),
            'type'    => 'blogpost',
            'parent'  => $blogentryid,
            'content' => array(
                'title'       => $config['content']['title'],
                'description' => $config['content']['description'],
                // TODO: Support for this "file" mode in interactive import
//                 'file'        => isset($file) ? $file : null,
                'files'       => self::add_files_to_import_entry_request_blogpost($entry, $importer, $blogentryid),
                'ctime'       => (string)$entry->published,
                'mtime'       => (string)$entry->updated,
                'published'   => PluginImportLeap::is_correct_category_scheme($entry, $importer, 'readiness', 'Unready') ? 0 : 1,
                'tags'        => PluginImportLeap::get_entry_tags($entry),
            ),
        ));
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
        $config = self::get_blogpost_entry_data($entry, $importer, $blogid);

        $createdartefacts = array();
        $blogpost = new ArtefactTypeBlogPost();
        $blogpost->set('title', $config['content']['title']);
        // If the entry has out of line content, we import that separately as a
        // file and set the content to refer to it
        if ($config['isfile']) {
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
            $blogpost->set('description', $config['content']['description']);
        }
        if ($config['content']['ctime']) {
            $blogpost->set('ctime', $config['content']['ctime']);
        }
        if ($config['content']['mtime']) {
            $blogpost->set('mtime', $config['content']['mtime']);
        }

        $draftpost = PluginImportLeap::is_correct_category_scheme($entry, $importer, 'readiness', 'Unready');
        $blogpost->set('published', $config['content']['published']);

        $blogpost->set('owner', $config['owner']);
        $blogpost->set('parent', $blogid);
        $blogpost->set('tags', $config['content']['tags']);
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
