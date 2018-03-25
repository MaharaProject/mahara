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
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('docroot') . '/lib/htmloutput.php');

// Download the export file if it's been generated
$downloadfile = param_variable('file', null);
if ($downloadfile) {
    $exportfile = get_config('dataroot') . 'export/' . $USER->get('id') . '/' . $downloadfile;
    $SESSION->set('exportdata', '');
    $SESSION->set('exportfile', '');
    require_once('file.php');
    serve_file($exportfile, basename($exportfile), 'application/x-zip', array('lifetime' => 0, 'forcedownload' => true));
    exit;
}

if (function_exists('apache_setenv')) {
    // Turn off gzip if it's on, because it prevents output from being flushed
    apache_setenv('no-gzip', 1);
}

if (!$exportdata = $SESSION->get('exportdata')) {
    redirect('/export/index.php');
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
function export_iframe_die($message, $link=null) {
    print_export_iframe_die($message, $link);
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
    flush();
}


// Bail if we don't have enough data to do an export
if (!isset($exportdata['format'])
    || !isset($exportdata['what'])
    || !isset($exportdata['views'])) {
    export_iframe_die(get_string('unabletogenerateexport', 'export'));
    exit;
}

safe_require('export', $exportdata['format']);
$user = new User();
$user->find_by_id($USER->get('id'));
$class = generate_class_name('export', $exportdata['format']);

switch($exportdata['what']) {
case 'all':
    $exporter = new $class($user, PluginExport::EXPORT_ALL_VIEWS_COLLECTIONS, PluginExport::EXPORT_ALL_ARTEFACTS, 'export_iframe_progress_handler');
    break;
case 'views':
    $exporter = new $class($user, $exportdata['views'], PluginExport::EXPORT_ARTEFACTS_FOR_VIEWS, 'export_iframe_progress_handler');
    break;
case 'collections':
    $exporter = new $class($user, $exportdata['views'], PluginExport::EXPORT_LIST_OF_COLLECTIONS, 'export_iframe_progress_handler');
    break;
default:
    export_iframe_die(get_string('unabletoexportportfoliousingoptions', 'export'));
}

$exporter->includefeedback = $exportdata['includefeedback'];
// Get an estimate of how big the unzipped export file would be
// so we can check that we have enough disk space for it
$space = $exporter->is_diskspace_available();
if (!$space) {
    export_iframe_die(get_string('exportfiletoobig', 'mahara'), get_config('wwwroot') . 'view/index.php');
}

try {
    $zipfile = $exporter->export();
} catch (SystemException $e) {
    export_iframe_die($e->getMessage(), get_config('wwwroot') . 'view/index.php');
}

// Store the filename in the session, and redirect the iframe to it to trigger
// the download. Here it would be nice to trigger the download for everyone,
// but alas this is not possible for people without javascript.
$SESSION->set('exportfile', $exporter->get('exportdir') . $zipfile);
$filepath = str_replace(get_config('dataroot') . 'export/' . $USER->get('id') . '/', '', $exporter->get('exportdir') . $zipfile);
$continueurl = 'download.php';
$continueurljs = get_config('wwwroot') . 'export/index.php';
$result = $SESSION->get('messages');
if (empty($result)) {
    $strexport   = get_string('exportgeneratedsuccessfully1', 'export');
}
else {
    $SESSION->clear('messages');
    $strexport   = get_string('exportgeneratedwitherrors', 'export');
}
print_export_footer($strexport, $continueurl, $continueurljs, $result, get_config('wwwroot') . 'export/download.php?file=' . $filepath);
