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
$title = $view->get('title');

if (get_record('usr_watchlist_view', 'usr', $data->usr, 'view', $viewid)) {
    if (!delete_records('usr_watchlist_view', 'usr', $data->usr, 'view', $viewid)) {
        $result->message = get_string('updatewatchlistfailed', 'view');
        json_reply('local', $result);
    }
    $result->message = get_string('removedfromwatchlist', 'view');
    if ($artefact) {
        $result->newtext = get_string('addtowatchlistartefact', 'view', $title);
    }
    else {
        $result->newtext = get_string('addtowatchlist', 'view');
    }
    json_reply(false, $result);
}

if (!insert_record('usr_watchlist_view', $data)) {
    $result->message = get_string('updatewatchlistfailed', 'view');
    json_reply('local', $result);
}

$result->message = get_string('addedtowatchlist', 'view');
if ($artefact) {
    $result->newtext = get_string('removefromwatchlistartefact', 'view', $title);
}
else {
    $result->newtext = get_string('removefromwatchlist', 'view');
}
json_reply(false, $result);
