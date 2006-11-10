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

error_log('updatemenu.json.php');

function send_fail_message() {
    $result = array();
    $result['error'] = 'local';
    $result['message'] = get_string('savefailed');
    echo json_encode($result);
    exit;
}

$type     = get_variable('type');
$name     = get_variable('name');
$linkedto = get_variable('linkedto');
$itemid   = get_variable('itemid');
$menu     = get_variable('menu');

error_log('updatemenu.json.php '.$type .' '. $name .' '. $linkedto .' '. $itemid);

$data = new StdClass;
if ($type == 'adminfile') {
    // Get file id.
    $data->file = $linkedto;
}
else if ($type == 'externallink') {
    $data->url = $linkedto;
}
else { // Bad menu item type
    send_fail_message();
}
$data->title = $name;
$data->public = $menu == 'public' ? 1 : 0;

if ($itemid == 'new') {
    // set displayorder to be after all the existing menu items
    try {
        $displayorders = get_rows('site_menu', '', '', '', 'displayorder');
        $max = 0;
        foreach ($displayorders as $r) {
            $max = $r['displayorder'] >= $max ? $r['displayorder'] + 1 : $max;
        }
        $data->displayorder = $max;
        insert_record('site_menu', $data);
    }
    catch (Exception $e) {
        send_fail_message();
    }
}
else {
    $data->id = $itemid;
    log_debug($data);
    try {
        update_record('site_menu', $data, 'id');
    }
    catch (Exception $e) {
        send_fail_message();
    }
}

$result = array();
$result['error'] = false;
$result['message'] = get_string('savedsuccessfully');
echo json_encode($result);
?>

