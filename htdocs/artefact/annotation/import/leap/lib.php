<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-annotation-import-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Implements LEAP2A import of annotation and their feedback entries into Mahara
 *
 */
class LeapImportAnnotation extends LeapImportArtefactPlugin {

    /**
     * Import an entry as an annotation
     * on an artefact or view
     */
    const STRATEGY_IMPORT_AS_ANNOTATION = 1;
    const STRATEGY_IMPORT_AS_ANNOTATION_FEEDBACK = 2;

    /**
     * Hack to allow annotations to be created before the thing they
     * comment on (a view or another artefact) gets created.
     *
     * On creation, point annotations at this temporary view until the
     * setup_relationships stage of the import, at which time the
     * correct annotation relationship will be restored.
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
                $title = get_string('entriesimportedfromleapexport', 'artefact.annotation');
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
            && $entry->xpath('mahara:artefactplugin[@mahara:type="annotation"]')) {

            // Check that the entry has something that reflects on it.
            $otherentries = array();
            foreach ($entry->link as $link) {
                // Don't include the view it reflects_on.
                if ($importer->curie_equals($link['rel'], $importer->get_leap2a_namespace(), 'reflected_on_by') && isset($link['href'])) {
                    // These should be the feedback.
                    $otherentries[] = (string)$link['href'];
                }
            }
            // Annotation may not have anything reflecting on it.
            $strategies[] = array(
                'strategy' => self::STRATEGY_IMPORT_AS_ANNOTATION,
                'score'    => 100,  // This needs to be created first before the feedback -> give it a higher score.
                'other_required_entries' => $otherentries,
            );
        }
        else if (PluginImportLeap::is_rdf_type($entry, $importer, 'entry')
            && $entry->xpath('mahara:artefactplugin[@mahara:type="annotationfeedback"]')) {

            // Check that the entry 'reflects_on' something
            $otherentries = array();
            foreach ($entry->link as $link) {
                if ($importer->curie_equals($link['rel'], $importer->get_leap2a_namespace(), 'reflects_on') && isset($link['href'])) {
                    // This should be the annotation.
                    $otherentries[] = (string) $link['href'];
                }
            }
            if (count($otherentries) == 1) {
                // Annotation feedback should only reflect on 1 item.
                $strategies[] = array(
                    'strategy' => self::STRATEGY_IMPORT_AS_ANNOTATION_FEEDBACK,
                    'score'    => 90,
                    'other_required_entries' => $otherentries,
                );
            }
        }

        return $strategies;
    }

    /**
     * Logic to figure out how to process an entry into an annotation
     * Used by import_using_strategy() and add_import_entry_request_using_strategy().
     *
     * @param SimpleXMLElement $entry
     * @param PluginImportLeap $importer
     * @param array $otherentries
     * @return array An array of config stuff to either create the annotation, or store an import request.
     * @throws ImportException
     */
    private static function get_annotation_entry_data(SimpleXMLElement $entry, PluginImportLeap $importer, array $otherentries) {

        $description = PluginImportLeap::get_entry_content($entry, $importer);
        $type = isset($entry->content['type']) ? (string)$entry->content['type'] : 'text';
        if ($type == 'text') {
            $description = format_whitespace($description);
        }
        if (isset($entry->author->name) && strlen($entry->author->name)) {
            $authorname = (string)$entry->author->name;
        }
        else {
            $author = $importer->get('usr');
        }
        $allowcomments = (int) PluginImportLeap::is_correct_category_scheme($entry, $importer, 'readiness', 'Ready');

        // First, the annotation.
        $config = array(
            'owner'   => $importer->get('usr'),
            'type'    => 'annotation',
            'content' => array(
                'title'        => (string) $entry->title,
                'description'  => $description,
                'allowcomments' => $allowcomments,
                'ctime'        => (string) $entry->published,
                'mtime'        => (string) $entry->updated,
                'authorname'   => isset($authorname) ? $authorname : null,
                'author'       => isset($author) ? $author : null,
                'tags'         => PluginImportLeap::get_entry_tags($entry),
            ),
        );

        // Then, the annotation feedback.
        $config['annotationfeedback'] = array();
        foreach ($otherentries as $entryid) {
            $feedbackentry = $importer->get_entry_by_id($entryid);
            if (!$feedbackentry) {
                // TODO: what to do here? Also - should this be checked here or earlier?
                $importer->trace("WARNING: Annotation feedback $entry->id claims to have part $entryid which doesn't exist.");
                continue;
            }
            $config['annotationfeedback'][] = array(
                'feedbackentry'             => $feedbackentry,
                'annotationfeedbackentryid' => $entryid,
                'annotationentryid'         => (string) $entry->id
            );
        }
        return $config;
    }

    private static function get_annotationfeedback_entry_data(SimpleXMLElement $entry, PluginImportLeap $importer, $annotationentryid) {

        $description = PluginImportLeap::get_entry_content($entry, $importer);
        $type = isset($entry->content['type']) ? (string)$entry->content['type'] : 'text';
        if ($type == 'text') {
            $description = format_whitespace($description);
        }
        if (isset($entry->author->name) && strlen($entry->author->name)) {
            $authorname = (string)$entry->author->name;
        }
        else {
            $author = $importer->get('usr');
        }

        return array(
            'owner'   => $importer->get('usr'),
            'type'    => 'annotationfeedback',
            'parent'  => $annotationentryid,
            'content' => array(
                'title'       => (string) $entry->title,
                'description' => $description,
                'ctime'       => (string) $entry->published,
                'mtime'       => (string) $entry->updated,
                'authorname'  => isset($authorname) ? $authorname : null,
                'author'      => isset($author) ? $author : null,
                'tags'        => PluginImportLeap::get_entry_tags($entry),
                'private'     => (int) PluginImportLeap::is_correct_category_scheme($entry, $importer, 'audience', 'Private'),
                'onannotation' => $annotationentryid,
            ),
        );
    }

    /**
     * Add an import entry request for an annotation feedback.
     *
     * @param SimpleXMLElement $entry    The entry to create the annotation feedback from.
     * @param PluginImportLeap $importer The importer
     * @param int $annotationentryid     The ID of the import annotation entry in which to put the feedback against.
     */
    private static function add_import_entry_request_annotationfeedback(SimpleXMLElement $entry, PluginImportLeap $importer, $annotationentryid) {

        $config = self::get_annotationfeedback_entry_data($entry, $importer, $annotationentryid);

        return PluginImportLeap::add_import_entry_request(
            $importer->get('importertransport')->get('importid'),
            (string) $entry->id,
            self::STRATEGY_IMPORT_AS_ANNOTATION_FEEDBACK,
            'annotation',
            array(
                'owner'   => $importer->get('usr'),
                'type'    => 'annotationfeedback',
                'parent'  => $annotationentryid,
                'content' => $config['content'],
            )
        );
    }

    public static function add_import_entry_request_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        switch ($strategy) {
            case self::STRATEGY_IMPORT_AS_ANNOTATION:
                $config = self::get_annotation_entry_data($entry, $importer, $otherentries);
                // Add the annotation entry.
                PluginImportLeap::add_import_entry_request(
                        $importer->get('importertransport')->get('importid'),
                        (string) $entry->id,
                        $strategy,
                        'annotation',
                        array(
                            'owner' => $config['owner'],
                            'type'    => $config['type'],
                            'content' => $config['content']
                        )
                );

                // Add the feedback entries.
                foreach ($config['annotationfeedback'] as $item) {
                    self::add_import_entry_request_annotationfeedback($item['feedbackentry'], $importer, $item['annotationentryid']);
                }

                break;
            case self::STRATEGY_IMPORT_AS_ANNOTATION_FEEDBACK:
                if (is_array($otherentries) && count($otherentries) == 1) {
                    // Only one annotation should be linked to a feedback.
                    foreach ($otherentries as $annotationentryid) {
                        self::add_import_entry_request_annotationfeedback($entry, $importer, $annotationentryid);
                    }
                }

                break;
            default:
                throw new ImportException($importer, get_string('unknownstrategyforimport', 'artefact.annotation'));
        }
    }

    public static function import_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {

        switch ($strategy) {
            case self::STRATEGY_IMPORT_AS_ANNOTATION:
                $config = self::get_annotation_entry_data($entry, $importer, $otherentries);
                $content = $config['content'];

                $annotation = new ArtefactTypeAnnotation();
                $annotation->set('title', $content['title']);
                $annotation->set('description', $content['description']);
                if ($content['ctime']) {
                    $annotation->set('ctime', $content['ctime']);
                }
                if ($content['mtime']) {
                    $annotation->set('mtime', $content['mtime']);
                }
                $annotation->set('owner', $config['owner']);
                $annotation->set('allowcomments', (isset($config['allowcomments']) ? $config['allowcomments'] : true));

                if ($content['authorname']) {
                    $annotation->set('authorname', $content['authorname']);
                }
                else {
                    $annotation->set('author', $content['author']);
                }

                if (empty(self::$tempview)) {
                    self::create_temporary_view($config['owner']);
                }
                $annotation->set('view', self::$tempview);
                $annotation->set('tags', $content['tags']);
                $annotation->commit();
                $artefactmapping = array();

                // Now insert the annotation feedback linked to this annotation.
                foreach ($config['annotationfeedback'] as $item) {
                    $annotationfeedbackid = self::create_annotationfeedback(
                            $item['feedbackentry'],
                            $importer,
                            $annotation->get('id')
                    );
                    $artefactmapping[$item['annotationfeedbackentryid']] = array($annotationfeedbackid);
                }

                $artefactmapping[(string) $entry->id] = array($annotation->get('id'));

                break;
            case self::STRATEGY_IMPORT_AS_ANNOTATION_FEEDBACK:
                throw new ImportException($importer, get_string('invalidcreateannotationfeedback', 'artefact.annotation'));
                break;
            default:
                // Shouldn't come through on its own.
                throw new ImportException($importer, get_string('unknownstrategyforimport', 'artefact.annotation'));
        }

        return $artefactmapping;
    }

    /**
     * Create an annotationfeedback record in the database for the feedback entry.
     */
    private static function create_annotationfeedback($entry, $importer, $annotationid) {
        $config = self::get_annotationfeedback_entry_data($entry, $importer, $annotationid);
        $content = $config['content'];

        $annotationfeedback = new ArtefactTypeAnnotationfeedback();
        $annotationfeedback->set('title', $content['title']);
        $annotationfeedback->set('description', $content['description']);
        if ($content['ctime']) {
            $annotationfeedback->set('ctime', $content['ctime']);
        }
        if ($content['mtime']) {
            $annotationfeedback->set('mtime', $content['mtime']);
        }
        $annotationfeedback->set('owner', $config['owner']);

        if ($content['authorname']) {
            $annotationfeedback->set('authorname', $content['authorname']);
        }
        else {
            $annotationfeedback->set('author', $content['author']);
        }

        $annotationfeedback->set('private', $content['private']);
        $annotationfeedback->set('onannotation', $annotationid);
        $annotationfeedback->commit();

        // return the newly created artefactid for the feedback.
        return $annotationfeedback->get('id');

    }

    /**
     * Create/import new annoations and their feedback from the import_entry_requests
     * records in the database.
     *
     * @param PluginImportLeap $importer
     */
    public static function import_from_requests(PluginImportLeap $importer) {
        $importid = $importer->get('importertransport')->get('importid');
        if ($entry_requests = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importid, 'annotation'))) {
            foreach ($entry_requests as $entry_request) {

                // Note, once the view is created, we'll need to come back to this annotation
                // and populate the artefact_annotation.view column.

                if ($annotationid = self::create_artefact_from_request($importer, $entry_request)) {
                    if ($annotationfeedback_requests = get_records_select_array('import_entry_requests', 'importid = ? AND entryparent = ? AND entrytype = ?', array($importid, $entry_request->entryid, 'annotationfeedback'))) {
                        foreach ($annotationfeedback_requests as $annotationfeedback_request) {

                            // When creating the feedback, it needs to have the 'onannotation' field set
                            // because null is not allowed in that field.
                            // Therefore, we need to populate the annotationid in the content
                            // with the annotation that was just created.
                            // Please note that we can't leave this for the 'setup_relationships_from_requests'
                            // since the annotation feedback aretefact won't get created unless the
                            // onannotation is set.

                            $content = unserialize($annotationfeedback_request->entrycontent);
                            $content['onannotation'] = $annotationid;

                            // Now serialize it again.
                            $annotationfeedback_request->entrycontent = serialize($content);

                            // Go ahead and create the feedback artefact and its related record
                            // in the table artefact_annotation_feedback.
                            self::create_artefact_from_request($importer, $annotationfeedback_request);
                        }
                    }
                }
            }
        }
    }

    public static function get_annotation_instance(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $artefactids = $importer->get_artefactids_imported_by_entryid((string) $entry->id);
        return new ArtefactTypeAnnotation($artefactids[0]);
    }

    /**
     * Get the id of the entry reflected on by a annotation entry -> this should be the view.
     */
    public static function get_referent_entryid(SimpleXMLElement $entry, PluginImportLeap $importer) {
        foreach ($entry->link as $link) {
            if ($importer->curie_equals($link['rel'], $importer->get_leap2a_namespace(), 'reflects_on') && isset($link['href'])) {
                return (string) $link['href'];
            }
        }

        // Shouldn't happen -- this was checked when offering the strategy
        throw new ImportException($importer, get_string('noreflectionentryfound', 'artefact.annotation'));
    }

    /**
     * Fix annotations to point to the right view.
     */
    public static function setup_view_relationships_from_requests(PluginImportLeap $importer) {
        // Get all the annotations imported.
        if ($entry_requests = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importer->get('importertransport')->get('importid'), 'annotation'))) {
            foreach ($entry_requests as $entry_request) {
                $annotationids = $importer->get_artefactids_imported_by_entryid($entry_request->entryid);

                $annotation = new ArtefactTypeAnnotation($annotationids[0]);

                if (!$annotation->get('id')) {
                    continue;
                }

                $annotation_entry = $importer->get_entry_by_id($entry_request->entryid);
                $view_entry_request = self::get_referent_entryid($annotation_entry, $importer);
                // Now see which view had this entryid.
                if ($viewid = $importer->get_viewid_imported_by_entryid($view_entry_request)) {
                    // Set the view on the annotation.
                    $annotation->set('view', $viewid);
                    $annotation->commit();
                }
                else {
                    // Nothing to link this annotation to, so leave it in the temporary view.
                    self::$savetempview = true;
                }
            }
        }
    }

    /**
     * Fix annotations to point to the right view.  Probably more
     * appropriate in setup_relationships.  To do that we would have
     * to change that call to happen after views are created.
     */
    public static function setup_view_relationships(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $annotation = self::get_annotation_instance($entry, $importer);

        if (!$annotation->get('id')) {
            return;
        }

        $view_entry_request = self::get_referent_entryid($entry, $importer);
        if ($viewid = $importer->get_viewid_imported_by_entryid($view_entry_request)) {
            $annotation->set('view', $viewid);
            $annotation->commit();
        }
        else {
            // Nothing to link this annotation to, so leave it in the temporary view.
            self::$savetempview = true;
        }
    }

    /**
     * Render import entry requests for Mahara annotations
     * @param PluginImportLeap $importer
     * @return HTML code for displaying annotations and choosing how to import them
     */
    public static function render_import_entry_requests(PluginImportLeap $importer) {
        $importid = $importer->get('importertransport')->get('importid');
        // Get import entry requests for Mahara annotations
        $entryannotations = array();
        if ($ierannotations = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importid, 'annotation'))) {
            foreach ($ierannotations as $ierannotation) {
                $annotation = unserialize($ierannotation->entrycontent);
                $annotation['id'] = $ierannotation->id;
                $annotation['decision'] = $ierannotation->decision;
                if (is_string($ierannotation->duplicateditemids)) {
                    $ierannotation->duplicateditemids = unserialize($ierannotation->duplicateditemids);
                }
                if (is_string($ierannotation->existingitemids)) {
                    $ierannotation->existingitemids = unserialize($ierannotation->existingitemids);
                }

                $annotation['disabled'][PluginImport::DECISION_IGNORE] = false;
                $annotation['disabled'][PluginImport::DECISION_ADDNEW] = false;
                $annotation['disabled'][PluginImport::DECISION_APPEND] = true;
                $annotation['disabled'][PluginImport::DECISION_REPLACE] = true;
                if (!empty($ierannotation->duplicateditemids)) {
                    $duplicated_annotation = artefact_instance_from_id($ierannotation->duplicateditemids[0]);
                    $annotation['duplicateditem']['id'] = $duplicated_annotation->get('id');
                    $annotation['duplicateditem']['title'] = $duplicated_annotation->get('title');
                    $res = $duplicated_annotation->render_self(array());
                    $annotation['duplicateditem']['html'] = $res['html'];
                }
                else if (!empty($ierannotation->existingitemids)) {
                    foreach ($ierannotation->existingitemids as $id) {
                        $existing_annotation = artefact_instance_from_id($id);
                        $res = $existing_annotation->render_self(array());
                        $annotation['existingitems'][] = array(
                            'id'    => $existing_annotation->get('id'),
                            'title' => $existing_annotation->get('title'),
                            'html'  => $res['html'],
                        );
                    }
                }

                // Get the feedback.
                $annotationfeedback = array();
                if ($ierannotationfeedback = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ? AND entryparent = ?',
                        array($importid, 'annotationfeedback', $ierannotation->entryid))) {
                    foreach ($ierannotationfeedback as $ierfeedback) {
                        $feedback = unserialize($ierfeedback->entrycontent);
                        $feedback['id'] = $ierfeedback->id;
                        $feedback['decision'] = $ierfeedback->decision;
                        if (is_string($ierfeedback->duplicateditemids)) {
                            $ierfeedback->duplicateditemids = unserialize($ierfeedback->duplicateditemids);
                        }
                        if (is_string($ierfeedback->existingitemids)) {
                            $ierfeedback->existingitemids = unserialize($ierfeedback->existingitemids);
                        }
                        $feedback['disabled'][PluginImport::DECISION_IGNORE] = false;
                        $feedback['disabled'][PluginImport::DECISION_ADDNEW] = false;
                        $feedback['disabled'][PluginImport::DECISION_APPEND] = true;
                        $feedback['disabled'][PluginImport::DECISION_REPLACE] = true;
                        if (!empty($ierfeedback->duplicateditemids)) {
                            $duplicated_feedback = artefact_instance_from_id($ierfeedback->duplicateditemids[0]);
                            $feedback['duplicateditem']['id'] = $duplicated_feedback->get('id');
                            $feedback['duplicateditem']['title'] = $duplicated_feedback->get('title');
                            $feedback['duplicateditem']['html'] = $duplicated_feedback->render_self(array());
                        }
                        else if (!empty($ierfeedback->existingitemids)) {
                            foreach ($ierfeedback->existingitemids as $id) {
                                $existing_feedback = artefact_instance_from_id($id);
                                $feedback['existingitems'][] = array(
                                    'id'    => $existing_feedback->get('id'),
                                    'title' => $existing_feedback->get('title'),
                                    'html'  => $existing_feedback->render_self(array()),
                                );
                            }
                        }
                        $annotationfeedback[] = $feedback;
                    }
                }
                $annotation['annotationfeedback'] = $annotationfeedback;
                $entryannotations[] = $annotation;
            }
        }
        $smarty = smarty_core();
        $smarty->assign('displaydecisions', $importer->get('displaydecisions'));
        $smarty->assign('entryannotations', $entryannotations);
        return $smarty->fetch('artefact:annotation:import/annotations.tpl');
    }
}
