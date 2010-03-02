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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('export', 'leap');
require_once('pieforms/pieform.php');
require_once('file.php');

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
    $zipfile = $exportdir . 'mahara-bulk-export-' . time() . '.zip';
    $cwd = getcwd();
    $command = sprintf('%s %s %s %s %s',
                       get_config('pathtozip'),
                       get_config('ziprecursearg'),
                       escapeshellarg($zipfile),
                       escapeshellarg($listingfile),
                       escapeshellarg($usersdir)
                       );
    $output = array();
    chdir($exportdir);
    exec($command, $output, $returnvar);
    chdir($cwd);
    if ($returnvar != 0) {
        throw new SystemException('Failed to zip the export file: return code ' . $returnvar);
    }

    return $zipfile;
}

function bulkexport_submit(Pieform $form, $values) {
    global $SESSION;

    $usernames = array();

    // Read in the usernames explicitly specified
    foreach (split("\n", $values['usernames']) as $username) {
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

    $listing = array();
    $files = array();
    $exportcount = 0;
    $exporterrors = array();

    foreach ($usernames as $username) {
        $user = new User();
        try {
            $user->find_by_username($username);
        } catch (AuthUnknownUserException $e) {
            continue; // Skip non-existent users
        }

        $exporter = new PluginExportLeap($user, PluginExport::EXPORT_ALL_VIEWS, PluginExport::EXPORT_ALL_ARTEFACTS);
        try {
            $zipfile = $exporter->export();
        } catch (Exception $e) {
            $exporterrors[] = $username;
            continue;
        }

        $listing[] = array($username, $zipfile);
        $files[] = $exporter->get('exportdir') . $zipfile;
        $exportcount++;
    }

    if (!$zipfile = create_zipfile($listing, $files)) {
        $SESSION->add_error_msg(get_string('bulkexportempty', 'admin'));
        redirect(get_config('wwwroot').'admin/users/bulkexport.php');
    }

    log_info('Exported ' . $exportcount . ' users');
    if (!empty($exporterrors)) {
        $SESSION->add_error_msg(get_string('couldnotexportusers', 'admin', implode(', ', $exporterrors)));
    }

    serve_file($zipfile, basename($zipfile), 'application/x-zip', array('lifetime' => 0, 'forcedownload' => true));
    exit;
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
    'elements' => array(
        'authinstance' => $authinstanceelement,
        'usernames' => array(
            'type' => 'textarea',
            'rows' => 25,
            'cols' => 50,
            'title' => get_string('bulkexportusernames', 'admin'),
            'description' => get_string('bulkexportusernamesdescription', 'admin'),
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('bulkexport', 'admin')
        )
    )
);

$form = pieform($form);

$smarty = smarty();
$smarty->assign('bulkexportform', $form);
$smarty->assign('bulkexportdescription', get_string('bulkexportdescription', 'admin'));
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->display('admin/users/bulkexport.tpl');
