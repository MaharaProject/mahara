<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

$viewid = param_integer('view');
$artefact = param_integer('artefact', null);

$data = new StdClass;
$data->view = $viewid;
$data->usr = $USER->get('id');
$data->ctime = db_format_timestamp(time());

$result = new StdClass;
require_once(get_config('libroot') . 'view.php');
$view = new View($viewid);
// Check that we can actually access the view and not just hacking the viewid passed in
if (!can_view_view($view)) {
    $result->message = get_string('updatewatchlistfailed', 'view');
    json_reply('local', $result);
}

$title = hsc($view->get('title'));

if (get_record('usr_watchlist_view', 'usr', $data->usr, 'view', $viewid)) {
    if (!delete_records('usr_watchlist_view', 'usr', $data->usr, 'view', $viewid)) {
        $result->message = get_string('updatewatchlistfailed', 'view');
        json_reply('local', $result);
    }
    $result->message = get_string('removedfromwatchlist', 'view');
    $result->newtext = '<span class="icon icon-eye left" role="presentation" aria-hidden="true"></span>';
    if ($artefact) {
        $result->newtext .= get_string('addtowatchlistartefact', 'view', $title);
    }
    else {
        $result->newtext .= get_string('addtowatchlist', 'view');
    }
    $result->watched = false;
    json_reply(false, $result);
}

if (!insert_record('usr_watchlist_view', $data)) {
    $result->message = get_string('updatewatchlistfailed', 'view');
    json_reply('local', $result);
}

$result->message = get_string('addedtowatchlist', 'view');
$result->newtext = '<span class="icon icon-eye-slash left" role="presentation" aria-hidden="true"></span>';
if ($artefact) {
    $result->newtext .= get_string('removefromwatchlistartefact', 'view', $title);
}
else {
    $result->newtext .= get_string('removefromwatchlist', 'view');
}
$result->watched = true;
json_reply(false, $result);
