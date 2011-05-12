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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

json_headers();

$name     = param_variable('name');
$itemid   = param_variable('itemid');

$data = new StdClass;
$data->title = $name;

if ($itemid == 'new') {
    try {
        $displayorders = get_records_array('group_category', '', '', '', 'displayorder');
        $max = 0;
        if ($displayorders) {
            foreach ($displayorders as $r) {
                $max = $r->displayorder >= $max ? $r->displayorder + 1 : $max;
            }
        }
        $data->displayorder = $max;
        insert_record('group_category', $data);
    }
    catch (Exception $e) {
        json_reply('local',get_string('savefailed','admin'));
    }
}
else {
    $data->id = (int)$itemid;
    try {
        update_record('group_category', $data, 'id');
    }
    catch (Exception $e) {
        json_reply('local',get_string('savefailed','admin'));
    }
}

json_reply(false, null);
