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

$name     = param_variable('name');
$itemid   = param_variable('itemid');

$data = new stdClass();
$data->title = $name;

try {
    if ($itemid == 'new') {
        $data->displayorder = 0; //Place holder.
        $itemid = insert_record('group_category',$data,'id',true);
    }
    else {
        $data->id = (int)$itemid;
        update_record('group_category', $data, 'id');
    }
    require_once('group.php');
    group_sort_categories();
}
catch (Exception $e) {
    json_reply('local',get_string('savefailed','admin'));
}
json_reply(false, array('id' => (int)$itemid));
