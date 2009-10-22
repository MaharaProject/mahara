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
 * @subpackage artefact-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');

if (!$unzip = $SESSION->get('unzip')) {
    redirect('/artefact/file/');
}

// Turn off gzip if it's on, because it prevents output from being flushed
apache_setenv('no-gzip', 1);

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
    <p class="progress-text"><?php echo get_string('unzipprogress', 'artefact.file', '0/' . $unzip['artefacts']); ?></p>
<?php
flush();

/**
 * Progress bar update
 *
 * @param int $artefacts   How many artefacts have been created
 */
function unzip_iframe_progress_handler($artefacts) {
    global $unzip;
    $percent = $artefacts / $unzip['artefacts'] * 100;
    $status = get_string('unzipprogress', 'artefact.file', $artefacts . '/' . $unzip['artefacts']);
    set_time_limit(10);

    // "Erase" the current output with a new background div
    echo '<div style="width: 100%; background-color: #808080;" class="progress-bar"></div>';
    // The progress bar itself
    echo '<div class="progress-bar" style="width: ' . intval($percent) . '%;"></div>' . "\n";
    // The status text
    echo '<p class="progress-text">' . hsc($status) . "</p>\n";
    flush();
}

$file = artefact_instance_from_id($unzip['file']);
if ($file->get('group')) {
    require_once(get_config('libroot') . 'group.php');
}
$file->set_archive_info($unzip['zipinfo']);
$status = $file->extract('unzip_iframe_progress_handler');

$next = $unzip['from'];
$next .= (strpos($next, '?') === false ? '?' : '&') . 'folder=' . $status['basefolderid'];

$SESSION->set('unzip', false);

$message = get_string('extractfilessuccess', 'artefact.file', $status['folderscreated'], $status['filescreated']);
?>
        <div class="progress-bar" style="width: 100%;">
        <p><?php echo $message; ?> <a href="<?php echo $next; ?>" target="_top"><?php echo get_string('Continue', 'artefact.file'); ?></a></p>
        </div>
    </body>
</html>
