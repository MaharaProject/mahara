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
 * @subpackage export
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

// Download the export file if it's been generated
if ($exportfile = $SESSION->get('exportfile')) {
    $SESSION->set('exportdata', '');
    $SESSION->set('exportfile', '');
    require_once('file.php');
    serve_file($exportfile, basename($exportfile), 'application/x-zip', array('lifetime' => 0, 'forcedownload' => true));
    exit;
}

// Turn off gzip if it's on, because it prevents output from being flushed
apache_setenv('no-gzip', 1);

if (!$exportdata = $SESSION->get('exportdata')) {
    redirect('/export/');
}
$SESSION->set('exportdata', '');

$stylesheets = array_reverse($THEME->get_url('style/style.css', true));
?>
<html>
    <head>
        <title></title>
<?php foreach ($stylesheets as $stylesheet) { ?>
        <link rel="stylesheet" type="text/css" href="<?php echo hsc($stylesheet); ?>">
<?php } ?>
        <style type="text/css">
            html, body {
                margin: 0;
                padding: 0;
                background-color: #808080;
            }
        </style>
    </head>
    <body>
    <div style="width: 100%; background-color: #808080;" class="progress-bar"></div>
    <p class="progress-text"><?php echo get_string('Starting', 'export'); ?></p>
<?php
flush();

/**
 * Outputs enough HTML to make a pretty error message in the iframe
 *
 * @param string $message The message to display to the user
 */
function export_iframe_die($message) {
    echo '<div class="progress-bar" style="width: 100%;"><p>' . hsc($message) . '</p></div></body></html>';
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
    // "Erase" the current output with a new background div
    echo '<div style="width: 100%; background-color: #808080;" class="progress-bar"></div>';
    // The progress bar itself
    echo '<div class="progress-bar" style="width: ' . intval($percent) . '%;"></div>' . "\n";
    // The status text
    echo '<p class="progress-text">' . hsc($status) . "</p>\n";
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
    $exporter = new $class($user, PluginExport::EXPORT_ALL_VIEWS, PluginExport::EXPORT_ALL_ARTEFACTS, 'export_iframe_progress_handler');
    break;
case 'views':
    $exporter = new $class($user, $exportdata['views'], PluginExport::EXPORT_ARTEFACTS_FOR_VIEWS, 'export_iframe_progress_handler');
    break;
default:
    export_iframe_die(get_string('unabletoexportportfoliousingoptions', 'export'));
}

$zipfile = $exporter->export();

// Store the filename in the session, and redirect the iframe to it to trigger 
// the download. Here it would be nice to trigger the download for everyone, 
// but alas this is not possible for people without javascript.
$SESSION->set('exportfile', $exporter->get('exportdir') . $zipfile);
$wwwroot = get_config('wwwroot');
$strexportgeneratedsuccessfullyjs = get_string('exportgeneratedsuccessfullyjs', 'export', '<a href="' . $wwwroot . '" target="_top">', '</a>');
$strexportgeneratedsuccessfully   = get_string('exportgeneratedsuccessfully', 'export', '<a href="download.php" target="_top">', '</a>');
?>
        <script type="text/javascript">
            document.write('<div class="progress-bar" style="width: 100%;"><p><?php echo $strexportgeneratedsuccessfullyjs; ?></p></div>');
            if (!window.opera) {
                // Opera can't handle this for some reason - it vomits out the 
                // download inline in the iframe
                document.location = 'download.php';
            }
        </script>
        <div class="progress-bar" style="width: 100%;">
            <p><?php echo $strexportgeneratedsuccessfully; ?></p>
        </div>
    </body>
</html>
