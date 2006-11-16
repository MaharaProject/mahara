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
 * @subpackage admin
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

$type     = param_alpha('type');        // external list or admin file
$name     = param_variable('name');
$linkedto = param_variable('linkedto');
$itemid   = param_variable('itemid');
$public   = (int) param_boolean('public');

$data = new StdClass;
if ($type == 'adminfile') {
    // Get file id.
    $data->file = $linkedto;
}
else if ($type == 'externallink') {
    $data->url = $linkedto;
}
else { // Bad menu item type
    json_reply('local',get_string('badmenuitemtype','admin'));
}
$data->title = $name;

if ($itemid == 'new') {
    $data->public = $public;
    // set displayorder to be after all the existing menu items
    try {
        $displayorders = get_rows('site_menu', 'public', $data->public, '', 'displayorder');
        $max = 0;
        if ($displayorders) {
            foreach ($displayorders as $r) {
                $max = $r['displayorder'] >= $max ? $r['displayorder'] + 1 : $max;
            }
        }
        $data->displayorder = $max;
        insert_record('site_menu', $data);
    }
    catch (Exception $e) {
        json_reply('local',get_string('savefailed','admin'));
    }
}
else {
    $data->id = $itemid;
    try {
        update_record('site_menu', $data, 'id');
    }
    catch (Exception $e) {
        json_reply('local',get_string('savefailed','admin'));
    }
}

json_reply(false,get_string('menuitemsaved'));

?>
