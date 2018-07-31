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
class LeapImportPeerassessment extends LeapImportArtefactPlugin {

    /**
     * Import an entry as a peer assessment
     * on an artefact or view
     */
    const STRATEGY_IMPORT_AS_PEERASSESSMENT = 1;

    /**
     * Hack to allow peer assessments to be created before the thing they
     * comment on (a view) gets created.
     *
     * On creation, point assessments at this temporary view until the
     * setup_relationships stage of the import, at which time the
     * correct assessment relationship will be restored.
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
                $title = get_string('entriesimportedfromleapexport', 'artefact.peerassessment');
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
            && $entry->xpath('mahara:artefactplugin[@mahara:type="peerassessment"]')) {

            // Annotation may not have anything reflecting on it.
            $strategies[] = array(
                'strategy' => self::STRATEGY_IMPORT_AS_PEERASSESSMENT,
                'score'    => 100,
                'other_required_entries' => array(),
            );
        }

        return $strategies;
    }

    /**
     * Logic to figure out how to process an entry into an assessment
     * Used by import_using_strategy() and add_import_entry_request_using_strategy().
     *
     * @param SimpleXMLElement $entry
     * @param PluginImportLeap $importer
     * @param array $otherentries
     * @return array An array of config stuff to either create the assessment, or store an import request.
     * @throws ImportException
     */
    private static function get_peerassessment_entry_data(SimpleXMLElement $entry, PluginImportLeap $importer, array $otherentries) {

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

        // First, the assessment.
        $config = array(
            'owner'   => $importer->get('usr'),
            'type'    => 'peerassessment',
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

        return $config;
    }

    public static function add_import_entry_request_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        switch ($strategy) {
            case self::STRATEGY_IMPORT_AS_PEERASSESSMENT:
                $config = self::get_peerassessment_entry_data($entry, $importer, $otherentries);
                // Add the peerassessment entry.
                PluginImportLeap::add_import_entry_request(
                        $importer->get('importertransport')->get('importid'),
                        (string) $entry->id,
                        $strategy,
                        'peerassessment',
                        array(
                            'owner' => $config['owner'],
                            'type'    => $config['type'],
                            'content' => $config['content']
                        )
                );
                break;
            default:
                throw new ImportException($importer, get_string('unknownstrategyforimport', 'artefact.peerassessment'));
        }
    }

    public static function import_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {

        switch ($strategy) {
            case self::STRATEGY_IMPORT_AS_PEERASSESSMENT:
                $config = self::get_peerassessment_entry_data($entry, $importer, $otherentries);
                $content = $config['content'];

                $assessment = new ArtefactTypePeerassessment();
                $assessment->set('title', $content['title']);
                $assessment->set('description', $content['description']);
                if ($content['ctime']) {
                    $assessment->set('ctime', $content['ctime']);
                }
                if ($content['mtime']) {
                    $assessment->set('mtime', $content['mtime']);
                }
                $assessment->set('owner', $config['owner']);
                $assessment->set('allowcomments', (isset($config['allowcomments']) ? $config['allowcomments'] : true));

                if ($content['authorname']) {
                    $assessment->set('authorname', $content['authorname']);
                }
                else {
                    $assessment->set('author', $content['author']);
                }

                if (empty(self::$tempview)) {
                    self::create_temporary_view($config['owner']);
                }
                $assessment->set('view', self::$tempview);
                $assessment->set('tags', $content['tags']);
                $assessment->commit();
                $artefactmapping = array();

                $artefactmapping[(string) $entry->id] = array($assessment->get('id'));

                break;
            default:
                // Shouldn't come through on its own.
                throw new ImportException($importer, get_string('unknownstrategyforimport', 'artefact.peerassessment'));
        }

        return $artefactmapping;
    }

    /**
     * Create/import new peer assessments from the import_entry_requests
     * records in the database.
     *
     * @param PluginImportLeap $importer
     */
    public static function import_from_requests(PluginImportLeap $importer) {
        $importid = $importer->get('importertransport')->get('importid');
        if ($entry_requests = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importid, 'peerassessment'))) {
            foreach ($entry_requests as $entry_request) {

                // Note, once the view is created, we'll need to come back to this peer assessment
                // and populate the artefact_peerassessment.view column.

                $assessmentid = self::create_artefact_from_request($importer, $entry_request);
            }
        }
    }

    public static function get_peerassessment_instance(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $artefactids = $importer->get_artefactids_imported_by_entryid((string) $entry->id);
        return new ArtefactTypePeerassessment($artefactids[0]);
    }

    /**
     * Get the id of the entry reflected on by a peerassessment entry -> this should be the view.
     */
    public static function get_referent_entryid(SimpleXMLElement $entry, PluginImportLeap $importer) {
        foreach ($entry->link as $link) {
            if ($importer->curie_equals($link['rel'], $importer->get_leap2a_namespace(), 'reflects_on') && isset($link['href'])) {
                return (string) $link['href'];
            }
        }

        // Shouldn't happen -- this was checked when offering the strategy
        throw new ImportException($importer, get_string('noreflectionentryfound', 'artefact.peerassessment'));
    }

    /**
     * Fix peerassessments to point to the right view.
     */
    public static function setup_view_relationships_from_requests(PluginImportLeap $importer) {
        // Get all the peerassessments imported.
        if ($entry_requests = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importer->get('importertransport')->get('importid'), 'peerassessment'))) {
            foreach ($entry_requests as $entry_request) {
                $peerassessmentids = $importer->get_artefactids_imported_by_entryid($entry_request->entryid);

                $peerassessment = new ArtefactTypePeerassessment($peerassessmentids[0]);

                if (!$peerassessment->get('id')) {
                    continue;
                }

                $peerassessment_entry = $importer->get_entry_by_id($entry_request->entryid);
                $view_entry_request = self::get_referent_entryid($peerassessment_entry, $importer);
                // Now see which view had this entryid.
                if ($viewid = $importer->get_viewid_imported_by_entryid($view_entry_request)) {
                    // Set the view on the peerassessment.
                    $peerassessment->set('view', $viewid);
                    $peerassessment->commit();
                }
                else {
                    // Nothing to link this peerassessment to, so leave it in the temporary view.
                    self::$savetempview = true;
                }
            }
        }
    }

    /**
     * Fix peerassessments to point to the right view.  Probably more
     * appropriate in setup_relationships.  To do that we would have
     * to change that call to happen after views are created.
     */
    public static function setup_view_relationships(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $peerassessment = self::get_peerassessment_instance($entry, $importer);

        if (!$peerassessment->get('id')) {
            return;
        }

        $view_entry_request = self::get_referent_entryid($entry, $importer);
        if ($viewid = $importer->get_viewid_imported_by_entryid($view_entry_request)) {
            $peerassessment->set('view', $viewid);
            $peerassessment->commit();
        }
        else {
            // Nothing to link this peerassessment to, so leave it in the temporary view.
            self::$savetempview = true;
        }
    }

    /**
     * Render import entry requests for Mahara peerassessments
     * @param PluginImportLeap $importer
     * @return HTML code for displaying peerassessments and choosing how to import them
     */
    public static function render_import_entry_requests(PluginImportLeap $importer) {
        $importid = $importer->get('importertransport')->get('importid');
        // Get import entry requests for Mahara peerassessments
        $entrypeerassessments = array();
        if ($ierpeerassessments = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importid, 'peerassessment'))) {
            foreach ($ierpeerassessments as $ierpeerassessment) {
                $peerassessment = unserialize($ierpeerassessment->entrycontent);
                $peerassessment['id'] = $ierpeerassessment->id;
                $peerassessment['decision'] = $ierpeerassessment->decision;
                if (is_string($ierpeerassessment->duplicateditemids)) {
                    $ierpeerassessment->duplicateditemids = unserialize($ierpeerassessment->duplicateditemids);
                }
                if (is_string($ierpeerassessment->existingitemids)) {
                    $ierpeerassessment->existingitemids = unserialize($ierpeerassessment->existingitemids);
                }

                $peerassessment['disabled'][PluginImport::DECISION_IGNORE] = false;
                $peerassessment['disabled'][PluginImport::DECISION_ADDNEW] = false;
                $peerassessment['disabled'][PluginImport::DECISION_APPEND] = true;
                $peerassessment['disabled'][PluginImport::DECISION_REPLACE] = true;
                if (!empty($ierpeerassessment->duplicateditemids)) {
                    $duplicated_peerassessment = artefact_instance_from_id($ierpeerassessment->duplicateditemids[0]);
                    $peerassessment['duplicateditem']['id'] = $duplicated_peerassessment->get('id');
                    $peerassessment['duplicateditem']['title'] = $duplicated_peerassessment->get('title');
                    $res = $duplicated_peerassessment->render_self(array());
                    $peerassessment['duplicateditem']['html'] = $res['html'];
                }
                else if (!empty($ierpeerassessment->existingitemids)) {
                    foreach ($ierpeerassessment->existingitemids as $id) {
                        $existing_peerassessment = artefact_instance_from_id($id);
                        $res = $existing_peerassessment->render_self(array());
                        $peerassessment['existingitems'][] = array(
                            'id'    => $existing_peerassessment->get('id'),
                            'title' => $existing_peerassessment->get('title'),
                            'html'  => $res['html'],
                        );
                    }
                }

                $entrypeerassessments[] = $peerassessment;
            }
        }
        $smarty = smarty_core();
        $smarty->assign('displaydecisions', $importer->get('displaydecisions'));
        $smarty->assign('entrypeerassessments', $entrypeerassessments);
        return $smarty->fetch('artefact:peerassessment:import/assessments.tpl');
    }
}
