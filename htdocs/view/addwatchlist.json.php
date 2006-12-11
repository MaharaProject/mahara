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

$view = param_integer('view');
$artefact = param_integer('artefact',null);
$recurse = param_boolean('recurse',true);

$data = new StdClass;
if ($artefact) {
    $data->artefact = $artefact;
    $type = 'artefact';
    $artefactfield = 'artefact';
    $fields = array('usr', 'view', 'artefact');
}
else {
    $type = 'view';
    $artefactfield = null;
    $fields = array('usr', 'view');
}
$table = 'usr_watchlist_' . $type;

$data->view = $view;
$data->usr = $USER->get('id');
$data->ctime = db_format_timestamp(time());
$data->recurse = $recurse ? 1 : 0;

$oldrecord = get_record($table, 'usr', $data->usr, 'view', $view, $artefactfield, $artefact);

if ($oldrecord) {
    if ($oldrecord->recurse == $recurse) {
        json_reply(false, get_string('alreadyinwatchlist', 'mahara', get_string($type)));
    }
    if (update_record($table, $data, $fields)) {
        json_reply(false, get_string('watchlistupdated'));
    }
    json_reply('local', get_string('updatewatchlistfailed'));
}

if (!insert_record($table, $data)) {
    json_reply('local', get_string('updatewatchlistfailed'));
}

json_reply(false,get_string('addedtowatchlist', 'mahara', get_string($type)));

?>
