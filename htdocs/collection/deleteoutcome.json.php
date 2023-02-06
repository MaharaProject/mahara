<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('docroot') . 'lib/collection.php');

$dynamicindex = param_integer('dynamicindex');
$outcomeid = param_integer('outcomeid');
$collectionid =  param_integer('collection', null);

$collection = new Collection($collectionid);
// check if user admin
if (!($collection->get('group') && group_user_access($collection->get('group')) === 'admin')) {
  throw new AccessDeniedException();
}

json_headers();


// Get view activity info
$num_of_activities = get_records_array('outcome_view_activity', 'outcome', $outcomeid);
if ($num_of_activities) {
    $data['message'] = get_string('deletefailedoutcome', 'collection', $dynamicindex) . ' ' . get_string('deleteactivitiesfirst', 'collection');
    json_reply('local', $data);
}

try {
    delete_records('outcome', 'id', $outcomeid);
}
catch (Exception $e) {
    json_reply('false', get_string('deletefailed', 'admin'));
}

json_reply(false, get_string('outcomedeleted','collection'));
