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

$collectionid = param_integer('collectionid');
$activityid = param_integer('activityid');
$update_type = param_alpha('update_type', '');

$collection = new Collection($collectionid);

$grouprole = group_user_access($collection->get('group'));
$actionsallowed = ($grouprole === 'admin' || $grouprole === 'tutor');

if ($actionsallowed) {
  $record = new stdClass();
  switch($update_type) {
    case 'signoff':

      break;
    case 'unsignoff':

      break;
  }
  // Should update record on DB here
  if ($activityid && $update_type ) {
    $activity = get_outcome_activity_views($collectionid, $activityid);

    $smarty = smarty();
    $smarty->assign('querystring', get_querystring());
    $smarty->assign('actionsallowed', $actionsallowed ? 1 : 0);

    $smarty->assign('activityid', $activityid);
    $smarty->assign('viewid', $activity->view);
    $smarty->assign('title', $activity->title);
    // TODO get signoff from DB
    $smarty->assign('signedoff', true);
    $html = $smarty->fetch('collection/activitytablerow.tpl');
    $data = array(
      'html' => $html,
      'error'    => false,
      'message'  => get_string('activityupdated','collection'),
    );
    json_reply(false, $data);
  }
}

json_reply('local', get_string('activityeupdatefailed','collection'));
