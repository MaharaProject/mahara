<?php
/**
 *
 * @package    mahara
 * @subpackage export
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('BULKEXPORT', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . '/lib/htmloutput.php');
raise_memory_limit("1024M");
raise_time_limit(300);

// Download the export file if it's been generated
if ($exportfile = $SESSION->get('exportfile')) {
    $SESSION->set('exportdata', '');
    $SESSION->set('exportfile', '');
    require_once('file.php');
    serve_file($exportfile, basename($exportfile), 'application/x-zip', array('lifetime' => 0, 'forcedownload' => true));
    exit;
    // TODO: delete the zipfile (and temporary files) once it's been downloaded
}

// Turn off all compression because it prevents output from being flushed
if (function_exists('apache_setenv')) {
    apache_setenv('no-gzip', 1);
}
@ini_set('zlib.output_compression', 0);

if (!$exportdata = $SESSION->get('exportdata')) {
    redirect(get_config('wwwroot').'admin/users/bulkexport.php');
}
$SESSION->set('exportdata', '');

$stylesheets = array_reverse($THEME->get_url('style/style.css', true));
print_export_head($stylesheets);
flush();

/**
 * Outputs enough HTML to make a pretty error message in the iframe
 *
 * @param string $message The message to display to the user
 */
function export_iframe_die($message) {
    print_export_iframe_die($message);
    exit;
}

/**
 * Registered as the progress report handler for the export. Streams updates
 * back to the browser
 *
 * @param int $percent   How far complete the export is
 * @param string $status A human-readable string describing the current step
 */
function export_iframe_progress_handler($percent, $status) {
    print_iframe_progress_handler($percent, $status);
    ob_flush();
}

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

// Bail if we don't have enough data to do an export
if (empty($exportdata)) {
    export_iframe_die(get_string('unabletogenerateexport', 'export'));
}

ob_start();
export_iframe_progress_handler(0, get_string('Setup', 'export'));

safe_require('export', 'leap');

$listing = array();
$files = array();
$exportcount = 0;
$exporterrors = array();

foreach ($exportdata as $username) {
    $user = new User();
    try {
        $user->find_by_username($username);
    } catch (AuthUnknownUserException $e) {
        continue; // Skip non-existent users
    }

    $percentage = (double)$exportcount / count($exportdata) * 100;
    $percentage = min($percentage, 98);
    export_iframe_progress_handler($percentage, get_string('exportingusername', 'admin', $username));

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

export_iframe_progress_handler(99, get_string('creatingzipfile', 'export'));

if (!$zipfile = create_zipfile($listing, $files)) {
    export_iframe_die(get_string('bulkexportempty', 'admin'));
}

export_iframe_progress_handler(100, get_string('Done', 'export'));
ob_end_flush();

log_info("Exported $exportcount users to $zipfile");
if (!empty($exporterrors)) {
    $SESSION->add_error_msg(get_string('couldnotexportusers', 'admin', implode(', ', $exporterrors)));
}

// Store the filename in the session, and redirect the iframe to it to trigger
// the download. Here it would be nice to trigger the download for everyone,
// but alas this is not possible for people without javascript.
$SESSION->set('exportfile', $zipfile);
$continueurljs = get_config('wwwroot');
$continueurl = 'bulkdownload.php';
$result = $SESSION->get('messages');
$SESSION->clear('messages');
print_export_footer(get_string('exportgeneratedsuccessfully1', 'export'), $continueurl, $continueurljs, $result, 'bulkdownload.php');
