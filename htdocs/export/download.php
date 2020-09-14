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
require_once(get_config('docroot') . 'export/lib.php');

$SESSION->set('exportprogress', false);
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
    $data['redirect'] = get_config('wwwroot') . 'export/index.php';
    json_reply(false, array('data' => $data));
}

$SESSION->set('exportdata', '');

/**
 * Returns error message to the page via ajax
 *
 * @param string $message The message to display to the user
 */
function export_iframe_die($message, $link=null) {
    $data['message'] = $message;
    $data['redirect'] = $link;
    json_reply(false, array('data' => $data));
}

/**
 * Registered as the progress report handler for the export. Streams updates
 * back to the browser
 *
 * @param int $percent   How far complete the export is
 * @param string $status A human-readable string describing the current step
 */
function export_iframe_progress_handler($percent, $status) {
    global $SESSION;
    $status = hsc($status);
    $percent = intval($percent);
    $SESSION->set('exportprogress', array('percent' => $percent, 'status' => $status));
    set_time_limit(10);
}

// Bail if we don't have enough data to do an export
if (!isset($exportdata['what'])
    || !isset($exportdata['views'])) {
    export_iframe_die(get_string('unabletogenerateexport', 'export'));
}

$exportplugins = plugins_installed('export');
foreach ($exportplugins as $plugin) {
    safe_require('export', $plugin->name);
}

$user = new User();
$user->find_by_id($USER->get('id'));

switch($exportdata['what']) {
case 'all':
    $exporter = new PluginExportAll($user, PluginExport::EXPORT_ALL_VIEWS_COLLECTIONS, PluginExport::EXPORT_ALL_ARTEFACTS, 'export_iframe_progress_handler');
    break;
case 'views':
    $exporter = new PluginExportAll($user, $exportdata['views'], PluginExport::EXPORT_ARTEFACTS_FOR_VIEWS, 'export_iframe_progress_handler');
    break;
case 'collections':
    $exporter = new PluginExportAll($user, $exportdata['views'], PluginExport::EXPORT_LIST_OF_COLLECTIONS, 'export_iframe_progress_handler');
    break;
default:
    export_iframe_die(get_string('unabletoexportportfoliousingoptions', 'export'));
}

$exporter->includefeedback = $exportdata['includefeedback'];

// Get an estimate of how big the unzipped export file would be
// so we can check that we have enough disk space for it
$space = ($exporter->is_diskspace_available());
if (!$space) {
    export_iframe_die(get_string('exportfiletoobig', 'mahara'), get_config('wwwroot') . 'export/index.php');
}

try {
    $zipfile = $exporter->export();
}
catch (SystemException $e) {
    export_iframe_die('Failed to zip the export file: ' . $e->getMessage(), get_config('wwwroot') . 'export/index.php');
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

$data['finished'] = true;
$data['progress'] = array('percent' => 100, 'status' => $strexport);
$data['serve_file'] = get_config('wwwroot') . 'export/download.php?file=' . $filepath;

$SESSION->set('exportprogress', 'done');
json_reply(false, array('data' => $data));