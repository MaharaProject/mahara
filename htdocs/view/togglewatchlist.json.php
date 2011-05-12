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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
