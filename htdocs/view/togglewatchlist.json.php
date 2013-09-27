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

$view = param_integer('view');

$data = new StdClass;
$data->view = $view;
$data->usr = $USER->get('id');
$data->ctime = db_format_timestamp(time());

$result = new StdClass;

if (get_record('usr_watchlist_view', 'usr', $data->usr, 'view', $view)) {
    if (!delete_records('usr_watchlist_view', 'usr', $data->usr, 'view', $view)) {
        $result->message = get_string('updatewatchlistfailed', 'view');
        json_reply('local', $result);
    }
    $result->message = get_string('removedfromwatchlist', 'view');
    $result->newtext = get_string('addtowatchlist', 'view');
    json_reply(false, $result);
}

if (!insert_record('usr_watchlist_view', $data)) {
    $result->message = get_string('updatewatchlistfailed', 'view');
    json_reply('local', $result);
}

$result->message = get_string('addedtowatchlist', 'view');
$result->newtext = get_string('removefromwatchlist', 'view');
json_reply(false, $result);
