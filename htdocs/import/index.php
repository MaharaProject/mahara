<?php
/**
 * @package    mahara
 * @subpackage import
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/*
 * Interactive user self-import of LEAP
 */

define('INTERNAL', 1);
define('MENUITEM', 'manage/import');
require(dirname(dirname(__FILE__)) . '/init.php');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'import');
define('SECTION_PAGE', 'index');
define('TITLE', get_string('importyourportfolio', 'import'));

define('PRINTUPLOADFORM_ACT', 0);
define('PRINTIMPORTITEMSFORM_ACT', 1);
define('DOIMPORT_ACT', 2);

// Check if leap import plugin is enabled
$importplugins = plugins_installed('import');

if (!$importplugins) {
    die_info(get_string('noimportpluginsenabled', 'import'));
}
if (!array_key_exists('leap', $importplugins)) {
    die_info(get_string('noleapimportpluginsenabled', 'import'));
}

get_importer_from_session();

$action = param_integer('action', PRINTUPLOADFORM_ACT);

switch ($action) {
    case PRINTUPLOADFORM_ACT:
    default:
        print_upload_form();
        break;
    case PRINTIMPORTITEMSFORM_ACT:
        print_import_items_form();
        break;
    case DOIMPORT_ACT:
        db_begin();
        if (param_exists('import_submit')) {
            save_decisions();
            // Do import and print the results
            do_import();
        }
        else if (param_exists('cancel_import_submit')) {
            cancel_import();
        }
        db_commit();
        break;
}

/**
 * Returns the global LocalImportTransport object and leap2a importer object from the current session if exists
 */
function get_importer_from_session() {
    global $SESSION, $USER, $IMPORTER;

    // Get $IMPORTER from $SESSION
    safe_require('import', 'leap');
    $importid = $SESSION->get('importid');
    if (!empty($importid)) {
        $importrecord = (object)array(
            'data' => array (
                'importid'     => $SESSION->get('importid'),
                'manifestfile' => $SESSION->get('manifestfile'),
                'extracted'    => $SESSION->get('extracted'),
                'mimetype'     => $SESSION->get('mimetype'),
            ));
        $TRANSPORTER = new LocalImporterTransport($importrecord);

        $importdata = (object)array(
                'token'      => '',
                'usr'        => $USER->get('id'),
                'queue'      => (int) false,
                'ready'      => 0, // set this to 0 so that if it gets queued, the cron won't process it
                'expirytime' => db_format_timestamp(time()+(60*60*24)),
                'format'     => 'leap',
                'loglevel'   => PluginImportLeap::LOG_LEVEL_STANDARD,
                'logtargets' => LOG_TARGET_FILE,
                'profile'    => true,
        );
        $IMPORTER = PluginImport::create_importer(null, $TRANSPORTER, $importdata);

    }
    else {
        $IMPORTER = null;
    }
}

/**
 * Store the transport info in the current session
 */
function set_importer_to_session() {
    global $SESSION, $IMPORTER;

    if ($IMPORTER) {
        $TRANSPORTER = $IMPORTER->get('importertransport');
        $SESSION->set('importid', $TRANSPORTER->get('importid'));
        $SESSION->set('manifestfile', $TRANSPORTER->get('manifestfile'));
        $SESSION->set('extracted', $TRANSPORTER->get('extracted'));
        $SESSION->set('mimetype', $TRANSPORTER->get('mimetype'));
    }
}

/**
 * Remove the transport info from the current session if exists
 */
function remove_importer_from_session() {
    global $SESSION;

    $importid = $SESSION->get('importid');
    if (!empty($importid)) {
        $SESSION->clear('importid');
        $SESSION->clear('manifestfile');
        $SESSION->clear('extracted');
        $SESSION->clear('mimetype');
    }
}

/**
 * Create a pieform to display for the interactive self-import
 */
function print_upload_form() {

    $form = pieform(array(
        'name'        => 'import',
        'method'      => 'post',
        'plugintype ' => 'core',
        'pluginname'  => 'import',
        'elements'    => array(
            'leap2afile' => array(
                'type'  => 'file',
                'class' => 'last',
                'title' => get_string('uploadleap2afile', 'admin'),
                'rules' => array(
                    'required' => true
                ),
                'maxfilesize'  => get_max_upload_size(true),
                'accept' => '.zip, .xml'
            ),
            'submit' => array(
                'class' => 'btn-primary',
                'type'  => 'submit',
                'value' => get_string('Import', 'import'),
            ),
        )
    ));
    $smarty = smarty();
    setpageicon($smarty, 'icon-download');
    $smarty->assign('pagedescription', get_string('importportfoliodescription', 'import'));
    $smarty->assign('form', $form);
    $smarty->display('form.tpl');
}


/**
 * Validate a user import
 *
 * @param  Pieform $form
 * @param  array $values
 */
function import_validate(Pieform $form, $values) {
    global $USER, $TRANSPORTER;

    if (!isset($values['leap2afile'])) {
        $form->set_error('leap2afile', $form->i18n('rule', 'required', 'required'));
        return;
    }

    require_once('uploadmanager.php');
    $um = new upload_manager('leap2afile');
    if ($error = $um->preprocess_file(array('zip'))) {
        $form->set_error('leap2afile', $error);
        return;
    }

    if ($values['leap2afile']['type'] == 'application/octet-stream') {
        require_once('file.php');
        $mimetype = file_mime_type($values['leap2afile']['tmp_name']);
    }
    else {
        $mimetype = trim($values['leap2afile']['type'], '"');
    }
    $date = time();
    $niceuser = preg_replace('/[^a-zA-Z0-9_-]/', '-', $USER->get('username'));
    safe_require('import', 'leap');
    $importrecord = (object)array(
        'data' => array(
            'importfile'     => $values['leap2afile']['tmp_name'],
            'importfilename' => $values['leap2afile']['name'],
            'importid'       => $niceuser . '-' . $date,
            'mimetype'       => $mimetype,
        )
    );

    $TRANSPORTER = new LocalImporterTransport($importrecord);
    try {
        $TRANSPORTER->extract_file();
        PluginImportLeap::validate_transported_data($TRANSPORTER);
    }
    catch (Exception $e) {
        $form->set_error('leap2afile', $e->getMessage());
        $TRANSPORTER->cleanup();
    }

    // Check if import data may exceed the user's file quota
    $importdata = $TRANSPORTER->files_info();
    require_once('function.dirsize.php');
    $importdatasize = dirsize($importdata['tempdir'] . 'extract/files');
    if (($USER->get('quotaused') + $importdatasize) > $USER->get('quota')) {
        $form->set_error('leap2afile', get_string('importexceedquota', 'import'));
        $TRANSPORTER->cleanup();
    }
}

/**
 * Submit a user import
 *
 * @param  Pieform $form
 * @param  array $values
 */
function import_submit(Pieform $form, $values) {
    global $USER, $TRANSPORTER, $IMPORTER;

    safe_require('import', 'leap');
    $importdata = (object)array(
            'token'      => '',
            'usr'        => $USER->get('id'),
            'queue'      => (int) false,
            'ready'      => 0, // set this to 0 so that if it gets queued, the cron won't process it
            'expirytime' => db_format_timestamp(time()+(60*60*24)),
            'format'     => 'leap',
            'loglevel'   => PluginImportLeap::LOG_LEVEL_STANDARD,
            'logtargets' => LOG_TARGET_FILE,
            'profile'    => true,
    );
    $IMPORTER = PluginImport::create_importer(null, $TRANSPORTER, $importdata);

    try {
        $IMPORTER->process(PluginImport::STEP_INTERACTIVE_IMPORT_FORM);
    }
    catch (ImportException $e) {
        log_info("Leap2A import failed: " . $e->getMessage());
        die_info(get_string('importfailed', 'import'));
    }

    set_importer_to_session();

    redirect('/import/index.php?action=' . PRINTIMPORTITEMSFORM_ACT);
}

/**
 * Create the form for when importing items
 *
 * @return void
 */
function print_import_items_form() {
    global $IMPORTER;

    safe_require('import', 'leap');
    $form = [];
    try {
        $form = $IMPORTER->build_import_entry_requests_form(DOIMPORT_ACT);
    }
    catch (ImportException $e) {
        log_info("Leap2A import failed: " . $e->getMessage());
        die_info(get_string('importfailed', 'import'));
    }

    $smarty = smarty();
    setpageicon($smarty, 'icon-download');
    $smarty->assign('PAGEHEADING', get_string('howimportyourportfolio', 'import'));
    $smarty->assign('pagedescription', get_string('howimportportfoliodescription', 'import'));
    $smarty->assign('form', $form);
    $smarty->display('form.tpl');
}

/**
 * Process the results of the user's decisions on the import items form, and update the import entry requests
 * in the database to reflect those decisions.
 */
function save_decisions() {
    global $USER;

    // Accessing $_POST directly here because it's the most efficient way to handle the
    // many dynamically-generated fields created by the import items form.
    foreach ($_POST as $key => $value) {
        if (
                preg_match('/^decision_(\d+)$/', $key, $m)
                && in_array(
                        $value,
                        array(PluginImport::DECISION_ADDNEW, PluginImport::DECISION_APPEND, PluginImport::DECISION_REPLACE, PluginImport::DECISION_IGNORE)
                )
        ) {
            update_record(
                    'import_entry_requests',
                    (object) array(
                        'decision' => $value,
                    ),
                    array(
                        'id' => $m[1],
                        'ownerid' => $USER->id,
                    )
            );
        }
    }
}

/**
 * Import the Leap2A items
 *
 * @return void
 */
function do_import() {
    global $IMPORTER;

    safe_require('import', 'leap');
    $result = '';
    try {
        $result = $IMPORTER->do_import_from_requests();
    }
    catch (ImportException $e) {
        log_info("Leap2A import failed: " . $e->getMessage());
        die_info(get_string('importfailed', 'import'));
    }

    if ($IMPORTER) {
        delete_records('import_entry_requests', 'importid', $IMPORTER->get('importertransport')->get('importid'), 'ownerid', $IMPORTER->get('usr'));
        remove_importer_from_session();
    }

    $smarty = smarty();
    setpageicon($smarty, 'icon-download');
    $smarty->assign('PAGEHEADING', get_string('importresult', 'import'));
    $smarty->assign('form', $result);
    $smarty->display('form.tpl');
}
/**
 * Remove all import  entry requests
 */
function cancel_import() {
    global $IMPORTER;

    if ($IMPORTER) {
        delete_records('import_entry_requests', 'importid', $IMPORTER->get('importertransport')->get('importid'), 'ownerid', $IMPORTER->get('usr'));
        remove_importer_from_session();
    }
    redirect('/import/index.php');
}
