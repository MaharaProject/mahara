<?php
/**
 *@package    mahara
* @subpackage core
* @author     Catalyst IT Ltd
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
* @copyright  For copyright information on Mahara, please see the README file distributed with this software.
*
*/

define('INTERNAL', 1);
define('JSON', 1);


require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');

$fromdate  = param_alphanumext('fromdate', null);
$todate   = param_alphanumext('todate', null);
$viewid = param_integer('view', 0);

if ($fromdate && $todate ) {
    $versions = View::get_versions($viewid, db_format_timestamp($fromdate), db_format_timestamp($todate));
}
else {
    $versions = View::get_versions($viewid);
}

$data = array();

if ($versions->count > 0) {
    end($versions->data);
    $lastkey = key($versions->data);
    reset($versions->data);
    foreach ($versions->data as $key => $value) {
        $view = new View($viewid);
        $value->blockdata_formatted = $view->format_versioning_data($value->blockdata);
        $data[$key] = array(
            "isSelected"=> ($key == $lastkey ? true : false),
            "taskTitle"=> $value->viewname,
            "taskSubTitle"=> $view->display_author() . ',' . format_date(strtotime($value->ctime)),
            "assignDate"=> date('d/m/Y\TH:i', strtotime($value->ctime)),
            "taskShortDate"=> date('j M', strtotime($value->ctime)),
            "taskDetails"=> $value->blockdata_formatted->html,
        );
    }
}

json_reply(false, array(
    'message' => null,
    'data' => $data
));
