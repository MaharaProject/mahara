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
define('STAFF', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require(get_config('libroot') . 'registration.php');

$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$extradata = json_decode(param_variable('extradata'));

$type = param_alpha('type', 'users');
$subpages = array('users', 'groups', 'views', 'content', 'historical', 'institutions', 'logins');
if (!in_array($type, $subpages)) {
    $type = 'users';
}

if ($type == 'historical') {
    $field = (isset($extradata->field) ? $extradata->field : 'count_usr');
}

if ($type == 'institutions' || $type == 'logins') {
    $sort = (isset($extradata->sort) ? $extradata->sort : 'displayname');
    $sortdesc = (isset($extradata->sortdesc) ? $extradata->sortdesc : false);
    $start = param_alphanumext('start', null);
    $end = param_alphanumext('end', null);
}

switch ($type) {
 case 'logins':
    $data = institution_logins_statistics($limit, $offset, $sort, $sortdesc, $start, $end);
    $data = $data['table'];
    break;
case 'institutions':
    $data = institution_comparison_stats_table($limit, $offset, $sort, $sortdesc);
    break;
case 'historical':
    $data = historical_stats_table($limit, $offset, $field);
    break;
case 'content':
    $data = content_stats_table($limit, $offset);
    break;
case 'groups':
    $data = group_stats_table($limit, $offset);
    break;
case 'views':
    $data = view_stats_table($limit, $offset);
    break;
case 'users':
default:
    $data = user_stats_table($limit, $offset);
}

json_reply(false, (object) array('message' => false, 'data' => $data));
