<?php

/**
 *
 * @package    mahara
 * @subpackage checkpoint
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
// require_once('collection.php');
// require_once(dirname(dirname(__FILE__)) . '/group/outcomes.php');

json_headers();

$view_id = param_integer('id', 0);
$view = new View($view_id);
$view_activity_data = $view->get_view_activity_data();
$achievement_levels = get_records_array('view_activity_achievement_levels', 'activity', $view_activity_data->id);
asort($achievement_levels);
// check if user admin or tutor
if (!($view->get('group') && (group_user_access($view->get('group'))
))) {
  throw new AccessDeniedException();
}


if ($achievement_levels) {
  $smarty = smarty();
  $smarty->assign('achievement_levels', $achievement_levels);
  $html = $smarty->fetch('blocktype:checkpoint:achievement_levels_info.tpl');
  json_reply(false, array(
    'message' => null,
    'data' => array(
      'html' => $html
    )
  ));
}

json_reply('local', get_string('nooutcometypes', 'collection'));
