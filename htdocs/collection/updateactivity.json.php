<?php
/**
 *
 * @package    mahara
 * @subpackage collection
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('collection.php');
require_once(dirname(dirname(__FILE__)). '/group/outcomes.php');
json_headers();

// If we are coming from the outcome overview page
$collectionid = param_integer('collectionid', null);
$activityid = param_integer('activityid', null);
$update_type = param_alpha('update_type', '');
$updatebyviewid = false;
// If we are coming from an activity page
$viewid = param_integer('view', null);
if ($viewid) {
    $collectionid = get_field('collection_view', 'collection', 'view', $viewid);
    $activityid = get_field('view_activity', 'id', 'view', $viewid);
    $update_type = get_field('view_activity', 'achieved', 'id', $activityid) ? 'unsignoff' : 'signoff';
    $updatebyviewid = true;
}
else {
    $viewid = get_field('view_activity', 'view', 'id', $activityid);
}

$collection = new Collection($collectionid);

$grouprole = group_user_access($collection->get('group'));
$actionsallowed = ($grouprole === 'admin' || $grouprole === 'tutor');

if ($actionsallowed) {
    $record = new stdClass();
    $result = null;
    switch($update_type) {
      case 'signoff':
        $result = set_field('view_activity', 'achieved', 1, 'id', $activityid);
        execute_sql("UPDATE {view_signoff_verify} SET signoff = ?, signofftime = ? WHERE view = ?", array(1, db_format_timestamp(time()), $viewid));
        $newstate = 1;
        break;
      case 'unsignoff':
        $result = set_field('view_activity', 'achieved', 0, 'id', $activityid);
        execute_sql("UPDATE {view_signoff_verify} SET signoff = ?, signofftime = ? WHERE view = ?", array(0, db_format_timestamp(time()), $viewid));
        $newstate = 0;
        break;
    }
    $message = get_string('activityupdated','collection');
    if ($updatebyviewid) {
        $data = new stdClass();
        $data->signoff_newstate = $newstate;
        json_reply(false, array('message' => $message,
                                'data' => $data));
    }
    else {
        $activity = get_outcome_activity_views($collectionid, $activityid);
        $smarty = smarty();
        $smarty->assign('querystring', get_querystring());
        $smarty->assign('actionsallowed', $actionsallowed ? 1 : 0);

        $smarty->assign('activityid', $activityid);
        $smarty->assign('viewid', $activity->view);
        $smarty->assign('title', $activity->title);
        $activity_achieved = get_field('view_activity', 'achieved', 'id', $activityid);
        $smarty->assign('signedoff', $activity_achieved);
        $html = $smarty->fetch('collection/activitytablerow.tpl');
        $data = array(
            'html' => $html,
            'error'    => false,
            'message'  => $message,
        );
        json_reply(false, $data);
    }
}

json_reply('local', get_string('activityeupdatefailed','collection'));
