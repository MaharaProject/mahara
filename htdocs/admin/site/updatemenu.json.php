<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

json_headers();

$type     = param_alpha('type');        // external list or admin file
$name     = param_variable('name');
$linkedto = param_variable('linkedto');
$itemid   = param_variable('itemid');
$public   = (int) param_boolean('public');

$data = new StdClass;
if ($type == 'sitefile') {
    // Get file id.
    $data->file = $linkedto;
    $data->url = null;
}
else if ($type == 'externallink') {
    $data->url = $linkedto;
    $data->file = null;
}
else if (sanitize_url($linkedto) == '') {
    json_reply('local',get_string('badurl','admin'));
}
else { // Bad menu item type
    json_reply('local',get_string('badmenuitemtype','admin'));
}
$data->title = $name;

if ($itemid == 'new') {
    $data->public = $public;
    // set displayorder to be after all the existing menu items
    try {
        $displayorders = get_records_array('site_menu', 'public', $data->public, '', 'displayorder');
        $max = 0;
        if ($displayorders) {
            foreach ($displayorders as $r) {
                $max = $r->displayorder >= $max ? $r->displayorder + 1 : $max;
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

json_reply(false,get_string('menuitemsaved','admin'));
