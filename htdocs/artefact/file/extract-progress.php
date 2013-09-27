<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');
require_once(get_config('docroot') . '/lib/htmloutput.php');

if (!$unzip = $SESSION->get('unzip')) {
    redirect('/artefact/file/index.php');
}

if (function_exists('apache_setenv')) {
    // Turn off gzip if it's on, because it prevents output from being flushed
    apache_setenv('no-gzip', 1);
}

$stylesheets = array_reverse($THEME->get_url('style/style.css', true));
print_extractprogress_head($stylesheets, $unzip['artefacts']);
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

    print_iframe_progress_handler($percent, $status);
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

$message = get_string('createdtwothings', 'artefact.file',
    get_string('nfolders', 'artefact.file', $status['folderscreated']),
    get_string('nfiles', 'artefact.file', $status['filescreated'])
);

print_extractprogress_footer($message, $next);
