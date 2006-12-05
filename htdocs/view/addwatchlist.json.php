<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage core
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

log_debug('addwatchlist.json.php');

$id = param_integer('id');
$type = param_variable('type');

log_debug($id . ' ' . $type);

$data = new StdClass;
if ($type == 'view') {
    $data->view = $id;
}
else if ($type == 'artefact') {
    $data->artefact = $id;
}
else {
    json_reply('local', get_string('invalidwatchlistitem'));
}

$table = 'usr_watchlist_' . $type;
$data->usr = $USER->get('id');
$data->ctime = db_format_timestamp(time());

log_debug($data);

if (record_exists($table, 'usr', $data->usr, $type, $id)) {
    json_reply(false, get_string('itemalreadyinwatchlist'));
}

if (!insert_record($table, $data)) {
    json_reply('local', get_string('updatewatchlistfailed'));
}

json_reply(false,get_string('itemaddedtowatchlist'));

?>
