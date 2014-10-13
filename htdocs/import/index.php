<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2013 Catalyst IT Ltd and others; see:
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
 * @subpackage import
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2013 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/*
 * Interactive user self-import of LEAP
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/import');
require(dirname(dirname(__FILE__)) . '/init.php');

//TODO: Optimize!
raise_memory_limit("512M");

define('PRINTUPLOADFORM_ACT', 0);
define('PRINTIMPORTITEMSFORM_ACT', 1);
define('DOIMPORT_ACT', 2);

$TRANSPORTER = null;
$IMPORTER = null;

// Check if leap import plugin is enabled
$importplugins = plugins_installed('import');

if (!$importplugins) {
    die_info(get_string('noimportpluginsenabled', 'import'));
}
if (!array_key_exists('leap', $importplugins)) {
    die_info(get_string('noleapimportpluginsenabled', 'import'));
}
// Check if unzip is available
// This is required for extracting leap2a zip file
if (!is_executable(get_config('pathtounzip'))) {
    die_info(get_string('unzipnotinstalled', 'admin'));
}

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
        if (isset($_POST['import_submit'])) {
            save_decisions();
            // Do import and print the results
            do_import();
        }
        else if (isset($_POST['cancel_import_submit'])) {
            cancel_import();
        }
        db_commit();
        break;
}

function print_upload_form() {

    $form = pieform(array(
        'name'        => 'import',
        'method'      => 'post',
        'plugintype ' => 'core',
        'pluginname'  => 'import',
        'elements'    => array(
            'leap2afile' => array(
                'type'  => 'file',
                'title' => get_string('uploadleap2afile', 'admin'),
                'rules' => array(
                    'required' => true
                )
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('Import', 'import'),
            ),
        )
    ));
    $smarty = smarty();
    $smarty->assign('PAGEHEADING', get_string('importyourportfolio', 'import'));
    $smarty->assign('pagedescription', get_string('importportfoliodescription', 'import'));
    $smarty->assign('form', $form);
    $smarty->display('form.tpl');
}


function import_validate(Pieform $form, $values) {
    global $USER, $TRANSPORTER;

    if (!isset($values['leap2afile'])) {
        $form->set_error('leap2afile', $form->i18n('rule', 'required', 'required'));
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
    $fakeimportrecord = (object)array(
        'data' => array(
            'importfile'     => $values['leap2afile']['tmp_name'],
            'importfilename' => $values['leap2afile']['name'],
            'importid'       => $niceuser . '-' . $date,
            'mimetype'       => $mimetype,
        )
    );

    $TRANSPORTER = new LocalImporterTransport($fakeimportrecord);
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

function import_submit(Pieform $form, $values) {
    global $SESSION, $USER, $TRANSPORTER, $IMPORTER;

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
    if ($TRANSPORTER) {
        $SESSION->set('importid', $TRANSPORTER->get('importid'));
        $SESSION->set('extracted', $TRANSPORTER->get('extracted'));
        $SESSION->set('mimetype', $TRANSPORTER->get('mimetype'));
    }
    redirect('/import/index.php?action=' . PRINTIMPORTITEMSFORM_ACT);
}

function print_import_items_form() {
    global $SESSION, $USER, $TRANSPORTER, $IMPORTER;

    safe_require('import', 'leap');
    // Get $TRANSPORTER and $IMPORTER from $SESSION
    $importrecord = (object)array(
        'data' => array (
            'importid'     => $SESSION->get('importid'),
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

    try {
        $form = $IMPORTER->build_import_entry_requests_form();
    }
    catch (ImportException $e) {
        log_info("Leap2A import failed: " . $e->getMessage());
        die_info(get_string('importfailed', 'import'));
    }

    $smarty = smarty();
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

    safe_require('import', 'leap');
    // Accessing $_POST directly here because it's the most efficient way to handle the
    // many dynamically-generated fields created by the import items form.
    foreach ($_POST as $key => $value) {
        if (
                preg_match('/^decision_(\d+)_\d+$/', $key, $m)
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

function do_import() {
    global $SESSION, $USER, $TRANSPORTER, $IMPORTER;

    safe_require('import', 'leap');
    // Get $TRANSPORTER and $IMPORTER from $SESSION
    $importrecord = (object)array(
        'data' => array (
            'importid'     => $SESSION->get('importid'),
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

    try {
        $result = $IMPORTER->do_import_from_requests();
    }
    catch (ImportException $e) {
        log_info("Leap2A import failed: " . $e->getMessage());
        die_info(get_string('importfailed', 'import'));
    }

    $smarty = smarty();
    $smarty->assign('PAGEHEADING', get_string('importresult', 'import'));
    $smarty->assign('form', $result);
    $smarty->display('form.tpl');
}
/**
 * Remove all import  entry requests
 */
function cancel_import() {
    global $SESSION, $USER;

    delete_records('import_entry_requests', 'importid', $SESSION->get('importid'), 'ownerid', $USER->get('id'));
    redirect('/import/index.php');
}
