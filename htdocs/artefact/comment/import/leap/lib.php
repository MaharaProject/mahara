<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-comment-import-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Implements LEAP2A import of comment entries into Mahara
 *
 */
class LeapImportComment extends LeapImportArtefactPlugin {

    /**
     * Import an entry as a comment (with associated attachments)
     * on an artefact or view
     */
    const STRATEGY_IMPORT_AS_COMMENT = 1;


    /**
     * Hack to allow comments to be created before the thing they
     * comment on (a view or another artefact) gets created.
     *
     * On creation, point comments at this temporary view until the
     * setup_relationships stage of the import, at which time the
     * correct comment relationship will be restored.
     */
    private static $tempview = null;
    private static $savetempview = false;

    public static function create_temporary_view($user) {
        $time = db_format_timestamp(time());
        $viewdata = (object) array(
            'owner'      => $user,
            'title'      => '--',
            'type'       => 'portfolio',
            'numrows'    => 1,
            'ctime'      => $time,
            'mtime'      => $time,
            'atime'      => $time,
        );
        return self::$tempview = insert_record('view', $viewdata, 'id', true);
    }

    /**
     * Delete the temporary view
     */
    public static function cleanup(PluginImportLeap $importer) {
        if (self::$tempview) {
            if (self::$savetempview) {
                $title = get_string('entriesimportedfromleapexport', 'artefact.comment');
                set_field('view', 'title', $title, 'id', self::$tempview);
            }
            else {
                delete_records('view', 'id', self::$tempview);
            }
        }
    }


    public static function get_import_strategies_for_entry(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $strategies = array();

        if (PluginImportLeap::is_rdf_type($entry, $importer, 'entry')
            && $entry->xpath('mahara:artefactplugin[@mahara:type="comment"]')) {

            // Check that the entry 'reflects_on' something
            $otherentries = array();
            foreach ($entry->link as $link) {
                if ($importer->curie_equals($link['rel'], $importer->get_leap2a_namespace(), 'reflects_on') && isset($link['href'])) {
                    $otherentries[] = (string)$link['href'];
                }
            }
            if (count($otherentries) == 1) {
                $strategies[] = array(
                    'strategy' => self::STRATEGY_IMPORT_AS_COMMENT,
                    'score'    => 100,
                    'other_required_entries' => array(),
                );
            }
        }

        return $strategies;
    }

/**
 * Import from entry requests for Mahara comments
 *
 * @param PluginImportLeap $importer
 * @return updated DB
 * @throw    ImportException
 */
    public static function import_from_requests(PluginImportLeap $importer) {
        if ($entry_requests = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importer->get('importertransport')->get('importid'), 'comment'))) {
            foreach ($entry_requests as $entry_request) {
                self::create_artefact_from_request($importer, $entry_request);
            }
        }
    }

    /**
     * Logic to figure out how to process an entry into a comment
     * Used by import_using_strategy() and add_import_entry_request_using_strategy().
     *
     * @param SimpleXMLElement $entry
     * @param PluginImportLeap $importer
     * @param unknown_type $strategy
     * @param array $otherentries
     * @return array An array of config stuff to either create the comment, or store an import request.
     * @throws ImportException
     */
    private static function get_entry_data_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        if ($strategy != self::STRATEGY_IMPORT_AS_COMMENT) {
            throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
        }

        $description = PluginImportLeap::get_entry_content($entry, $importer);
        $type = isset($entry->content['type']) ? (string)$entry->content['type'] : 'text';
        if ($type == 'text') {
            $description = format_whitespace($description);
        }
        else {
            $description = ArtefactTypeComment::remove_comments_classes($description);
        }
        if (isset($entry->author->name) && strlen($entry->author->name)) {
            $authorname = (string)$entry->author->name;
        }
        else {
            $author = $importer->get('usr');
        }

        return array(
            'owner'   => $importer->get('usr'),
            'type'    => 'comment',
            'content' => array(
                'title'       => (string)$entry->title,
                'description' => $description,
                'ctime'       => (string)$entry->published,
                'mtime'       => (string)$entry->updated,
                'private'     => (int)PluginImportLeap::is_correct_category_scheme($entry, $importer, 'audience', 'Private'),
                'authorname'  => isset($authorname) ? $authorname : null,
                'author'      => isset($author) ? $author : null,
                'tags'        => PluginImportLeap::get_entry_tags($entry),
            ),
        );
    }

    public static function add_import_entry_request_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $config = self::get_entry_data_using_strategy($entry, $importer, $strategy, $otherentries);

        return PluginImportLeap::add_import_entry_request(
                $importer->get('importertransport')->get('importid'),
                (string)$entry->id,
                self::STRATEGY_IMPORT_AS_COMMENT,
                'comment',
                $config
        );
    }

    public static function import_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {

        $config = self::get_entry_data_using_strategy($entry, $importer, $strategy, $otherentries);
        $content = $config['content'];

        $comment = new ArtefactTypeComment();
        $comment->set('title', $content['title']);
        $comment->set('description', $content['title']);
        if ($content['ctime']) {
            $comment->set('ctime', $content['ctime']);
        }
        if ($content['mtime']) {
            $comment->set('mtime', $content['mtime']);
        }
        $comment->set('private', $content['private']);
        $comment->set('owner', $config['owner']);

        if ($content['authorname']) {
            $comment->set('authorname', $content['authorname']);
        }
        else {
            $comment->set('author', $content['author']);
        }

        if (empty(self::$tempview)) {
            self::create_temporary_view($config['owner']);
        }
        $comment->set('onview', self::$tempview);
        $comment->set('tags', $content['tags']);
        $comment->commit();

        $artefactmapping = array();
        $artefactmapping[(string)$entry->id] = array($comment->get('id'));
        return $artefactmapping;
    }


    /**
     * Add an import entry request as a comment from the given entry
     *
     * @param SimpleXMLElement $entry    The entry to create the comment from
     * @param PluginImportLeap $importer The importer
     */
    private static function add_import_entry_request_comment(SimpleXMLElement $entry, PluginImportLeap $importer) {
    }


    /**
     * Get the id of the entry reflected on by a comment entry
     */
    public static function get_referent_entryid(SimpleXMLElement $entry, PluginImportLeap $importer) {
        foreach ($entry->link as $link) {
            if ($importer->curie_equals($link['rel'], $importer->get_leap2a_namespace(), 'reflects_on') && isset($link['href'])) {
                return (string)$link['href'];
            }
        }

        // Shouldn't happen -- this was checked when offering the strategy
        throw new ImportException($importer, 'TODO: get_string: cannot find an entry for a comment to comment on');
    }


    public static function get_comment_instance(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $artefactids = $importer->get_artefactids_imported_by_entryid((string)$entry->id);
        return new ArtefactTypeComment($artefactids[0]);
    }

    /**
     * Relate comments to the artefacts they comment on
     * Attach comments to comments
     *
     */
    public static function setup_relationships_from_requests(PluginImportLeap $importer) {
        if ($entry_requests = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importer->get('importertransport')->get('importid'), 'comment'))) {
            foreach ($entry_requests as $entry_request) {
                $entry = $importer->get_entry_by_id($entry_request->entryid);
                self::setup_relationships($entry, $importer, null, array());
            }
        }
    }

    /**
     * Relate comments to the artefacts they comment on
     * Attach comments to comments
     */
    public static function setup_relationships(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $comment = null;
        $newartefacts = array(); // save any newly created extra ones (eg enclosures)
        $referentid = self::get_referent_entryid($entry, $importer);

        // Link artefact comments; view comments are done later
        if ($artefactids = $importer->get_artefactids_imported_by_entryid($referentid)) {
            $comment = self::get_comment_instance($entry, $importer);
            $comment->set('onartefact', $artefactids[0]);
            $comment->set('onview', null);
        }

        // Attachments
        foreach ($entry->link as $link) {
            if (!$comment) {
                $comment = self::get_comment_instance($entry, $importer);
            }
            if ($id = $importer->create_attachment($entry, $link, $comment)) {
                $newartefacts[] = $id;
            }
        }

        if ($comment) {
            $comment->commit();
        }
        return $newartefacts;
    }

    /**
     * Fix comments to point to the right view.  Probably more
     * appropriate in setup_relationships.  To do that we would have
     * to change that call to happen after views are created.
     */
    public static function setup_view_relationships_from_requests(PluginImportLeap $importer) {
        if ($entry_requests = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importer->get('importertransport')->get('importid'), 'comment'))) {
            foreach ($entry_requests as $entry_request) {
                $commentids = $importer->artefactids[$entry_request->entryid];
                $comment = new ArtefactTypeComment($commentids[0]);
                if ($comment->get('onartefact')) {
                    continue;
                }
                $entry = $importer->get_entry_by_id($entry_request->entryid);
                $referentid = self::get_referent_entryid($entry, $importer);
                if ($viewid = $importer->get_viewid_imported_by_entryid($referentid)) {
                    $comment->set('onview', $viewid);
                    $comment->commit();
                }
                else {
                    // Nothing to link this comment to, so leave it in the temporary view.
                    self::$savetempview = true;
                }
            }
        }
    }

    /**
     * Fix comments to point to the right view.  Probably more
     * appropriate in setup_relationships.  To do that we would have
     * to change that call to happen after views are created.
     */
    public static function setup_view_relationships(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $comment = self::get_comment_instance($entry, $importer);

        if ($comment->get('onartefact')) {
            return;
        }

        $referentid = self::get_referent_entryid($entry, $importer);
        if ($viewid = $importer->get_viewid_imported_by_entryid($referentid)) {
            $comment->set('onview', $viewid);
            $comment->commit();
        }
        else {
            // Nothing to link this comment to, so leave it in the temporary view.
            self::$savetempview = true;
        }
    }

    /**
     * Render import entry requests for Mahara comments
     * @param PluginImportLeap $importer
     * @return HTML code for displaying comments and choosing how to import them
     */
    public static function render_import_entry_requests(PluginImportLeap $importer) {
        $importid = $importer->get('importertransport')->get('importid');
        // Get import entry requests for Mahara comments
        $entrycomments = array();
        if ($iercomments = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importid, 'comment'))) {
            foreach ($iercomments as $iercomment) {
                $comment = unserialize($iercomment->entrycontent);
                $comment['id'] = $iercomment->id;
                $comment['decision'] = $iercomment->decision;
                $comment['disabled'][PluginImport::DECISION_IGNORE] = false;
                $comment['disabled'][PluginImport::DECISION_ADDNEW] = false;
                $comment['disabled'][PluginImport::DECISION_APPEND] = true;
                $comment['disabled'][PluginImport::DECISION_REPLACE] = true;
                $entrycomments[] = $comment;
            }
        }
        $smarty = smarty_core();
        $smarty->assign('displaydecisions', $importer->get('displaydecisions'));
        $smarty->assign('entrycomments', $entrycomments);
        return $smarty->fetch('artefact:comment:import/comments.tpl');
    }
}
