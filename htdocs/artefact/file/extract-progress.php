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
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');

$data['error'] = false;
$SESSION->set('unzipprogress', false);

if (!$unzip = $SESSION->get('unzip')) {
    $data['redirect'] = get_config('wwwroot') . 'artefact/file/index.php';
    json_reply(false, array('data' => $data));
}

if (function_exists('apache_setenv')) {
    // Turn off gzip if it's on, because it prevents output from being flushed
    apache_setenv('no-gzip', 1);
}

$data['artefacts'] = $unzip['artefacts'];

/**
 * Progress bar update
 *
 * @param int $artefacts   How many artefacts have been created
 */
function unzip_iframe_progress_handler($artefacts) {
    global $unzip, $SESSION;
    $percent = $artefacts / $unzip['artefacts'] * 100;
    $status = get_string('unzipprogress', 'artefact.file', $artefacts . '/' . $unzip['artefacts']);
    $status = hsc($status);
    $percent = intval($percent);
    $SESSION->set('unzipprogress', array('percent' => $percent, 'status' => $status));
    set_time_limit(10);
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
$data['finished'] = true;
$data['progress'] = array('percent' => 100, 'status' => $message);
$data['next'] = $next;

$SESSION->set('unzipprogress', 'done');
json_reply(false, array('data' => $data));
