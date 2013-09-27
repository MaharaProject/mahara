<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Hugh Davenport <hugh@catalyst.net.nz
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALSTAFF', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require(get_config('libroot') . 'registration.php');

$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$extradata = json_decode(param_variable('extradata'));
$institution = (isset($extradata->institution) ? $extradata->institution : 'mahara');

$type = param_alpha('type', 'users');
$subpages = array('users', 'views', 'content', 'historical');
if (!in_array($type, $subpages)) {
    $type = 'users';
}

if ($type == 'historical') {
    $field = (isset($extradata->field) ? $extradata->field : 'count_members');
}

$institutiondata = institution_statistics($institution, true);

switch ($type) {
case 'historical':
    $data = institution_historical_stats_table($limit, $offset, $field, $institutiondata);
    break;
case 'content':
    $data = institution_content_stats_table($limit, $offset, $institutiondata);
    break;
case 'views':
    $data = institution_view_stats_table($limit, $offset, $institutiondata);
    break;
case 'users':
default:
    $data = institution_user_stats_table($limit, $offset, $institutiondata);
}

json_reply(false, (object) array('message' => false, 'data' => $data));
