<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

define('TITLE', get_string('bulkexporttitle', 'admin'));

/**
 * Convert a 2D array to a CSV file. This follows the basic rules from http://en.wikipedia.org/wiki/Comma-separated_values
 *
 * @param array $input 2D array of values: each line is an array of values
 */
function data_to_csv($input) {
    if (empty($input) or !is_array($input)) {
        return '';
    }

    $output = '';
    foreach ($input as $line) {
        $lineoutput = '';

        foreach ($line as $element) {
            $element = str_replace('"', '""', $element);
            if (!empty($lineoutput)) {
                $lineoutput .= ',';
            }
            $lineoutput .= "\"$element\"";
        }

        $output .= $lineoutput . "\r\n";
    }

    return $output;
}

/**
 * Create a zip archive containing the exported data.
 *
 * @param array $listing The list of usernames that were exported
 * @param array $files A list of archive files for each user
 */
function create_zipfile($listing, $files) {
    global $USER;

    if (empty($listing) or empty($files)) {
        return false;
    }
    if (count($listing) != count($files)) {
        throw new MaharaException("Files and listing don't match.");
    }

    // create temporary directories for the export
    $exportdir = get_config('dataroot') . 'export/'
        . $USER->get('id')  . '/' . time() .  '/';
    if (!check_dir_exists($exportdir)) {
        throw new SystemException("Couldn't create the temporary export directory $exportdir");
    }
    $usersdir = 'users/';
    if (!check_dir_exists($exportdir . $usersdir)) {
        throw new SystemException("Couldn't create the temporary export directory $usersdir");
    }

    // move user zipfiles into the export directory
    foreach ($files as $filename) {
        if (copy($filename, $exportdir . $usersdir . basename($filename))) {
            unlink($filename);
        }
        else {
            throw new SystemException("Couldn't move $filename to $usersdir");
        }
    }

    // write username listing to a file
    $listingfile = 'usernames.csv';
    if (!file_put_contents($exportdir . $listingfile, data_to_csv($listing))) {
        throw new SystemException("Couldn't write usernames to a file");
    }

    // zip everything up
    $filename = 'mahara-bulk-export-' . time() . '.zip';
    try {
        create_zip_archive($exportdir, $filename, array($listingfile, $usersdir));
    }
    catch (SystemException $e) {
        throw new SystemException('Failed to zip the export file: ' . $e->getMessage());
    }

    return $exportdir . $filename;
}

function bulkexport_submit(Pieform $form, $values) {
    global $SESSION;

    $usernames = array();

    // Read in the usernames explicitly specified
    foreach (explode("\n", $values['usernames']) as $username) {
        $username = trim($username);
        if (!empty($username)) {
            $usernames[] = $username;
        }
    }

    if (empty($usernames) and !empty($values['authinstance'])) {
        // Export all users from the selected institution
        $rs = get_recordset_select('usr', 'authinstance = ? AND deleted = 0', array($values['authinstance']), '', 'username');
        while ($record = $rs->FetchRow()) {
            $usernames[] = $record['username'];
        }
    }

    safe_require('export', 'leap');

    $listing = array();
    $files = array();
    $exportcount = 0;
    $exporterrors = array();

    $num_users = count($usernames);

    foreach ($usernames as $username) {
        if (!($exportcount % 5)) {
            set_progress_info('bulkexport', $exportcount, $num_users, get_string('validating', 'admin'));
        }

        $user = new User();
        try {
            $user->find_by_username($username);
        }
        catch (AuthUnknownUserException $e) {
            continue; // Skip non-existent users
        }

        $exporter = new PluginExportLeap($user, PluginExport::EXPORT_ALL_VIEWS_COLLECTIONS, PluginExport::EXPORT_ALL_ARTEFACTS);
        try {
            $zipfile = $exporter->export();
        }
        catch (Exception $e) {
            $exporterrors[] = $username;
            continue;
        }

        $listing[] = array($username, $zipfile);
        $files[] = $exporter->get('exportdir') . $zipfile;
        $exportcount++;
    }

    if (!$zipfile = create_zipfile($listing, $files)) {
        export_iframe_die(get_string('bulkexportempty', 'admin'));
    }

    log_info("Exported $exportcount users to $zipfile");
    if (!empty($exporterrors)) {
        $SESSION->add_error_msg(get_string('couldnotexportusers', 'admin', implode(', ', $exporterrors)));
    }

    // Store the filename in the session, and redirect the iframe to it to trigger
    // the download. Here it would be nice to trigger the download for everyone,
    // but alas this is not possible for people without javascript.
    $SESSION->set('exportfile', $zipfile);

    set_progress_done('bulkexport', array('redirect' => get_config('wwwroot') . 'admin/users/bulkexport.php'));

    // Download the export file once it has been generated
    require_once('file.php');
    serve_file($zipfile, basename($zipfile), 'application/x-zip', array('lifetime' => 0, 'forcedownload' => true));
    // TODO: delete the zipfile (and temporary files) once it's been downloaded
}

$authinstanceelement = array('type' => 'hidden', 'value' => '');

$authinstances = auth_get_auth_instances();
if (count($authinstances) > 0) {
    $options = array();

    foreach ($authinstances as $authinstance) {
        $options[$authinstance->id] = $authinstance->displayname. ': '.$authinstance->instancename;
    }
    $default = key($options);

    $authinstanceelement = array(
        'type' => 'select',
        'title' => get_string('institution'),
        'description' => get_string('bulkexportinstitution', 'admin'),
        'options' => $options,
        'defaultvalue' => $default
    );
}

$form = array(
    'name' => 'bulkexport',
    'jsform' => true,
    'jssuccesscallback' => 'pmeter_success',
    'jserrorcallback' => 'pmeter_error',
    'presubmitcallback' => 'pmeter_presubmit',
    'elements' => array(
        'authinstance' => $authinstanceelement,
        'usernames' => array(
            'type' => 'textarea',
            'rows' => 25,
            'cols' => 50,
            'title' => get_string('bulkexportusernames', 'admin'),
            'description' => get_string('bulkexportusernamesdescription', 'admin'),
        ),
        'progress_meter_token' => array(
            'type' => 'hidden',
            'value' => 'bulkexport',
            'readonly' => TRUE,
        ),
        'submit' => array(
            'type' => 'submit',
            'class' => 'btn-primary',
            'value' => get_string('bulkexport', 'admin')
        )
    )
);

set_progress_done('bulkexport');

$form = pieform($form);

$smarty = smarty();
$smarty->assign('bulkexportform', $form);
$smarty->assign('bulkexportdescription', get_string('bulkexportdescription', 'admin'));
$smarty->display('admin/users/bulkexport.tpl');
