<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups/views');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'groupviews');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');
require_once('pieforms/pieform.php');

//@todo: group menu; group sideblock

$limit   = param_integer('limit', 5);
$offset  = param_integer('offset', 0);
$groupid = param_integer('group');
if (!$group = get_record('group', 'id', $groupid, 'deleted', 0)) {
    throw new GroupNotFoundException("Couldn't find group with id $groupid");
}
define('TITLE', $group->name . ' - ' . get_string('groupviews', 'view'));

$member = group_user_access($groupid);
$shared = param_boolean('shared', 0) && $member;
$can_edit = group_user_can_edit_views($groupid);

$smarty = smarty(array(), array(), array(), array('group' => $group));
$smarty->assign('heading', $group->name);

if ($can_edit) {
    $data = View::get_myviews_data($limit, $offset, $groupid);
}
else {
    $data = View::view_search(null, $groupid, null, null, $limit, $offset);
}

$userid = $USER->get('id');

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'view/?',
    'count' => $data->count,
    'limit' => $limit,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('view', 'view'),
    'resultcounttextplural' => get_string('views', 'view')
));

$smarty->assign('groupid', $groupid);
$smarty->assign('groupviews', 1);
$smarty->assign('groupname', $group->name);
$smarty->assign('grouptabs', group_get_menu_tabs($group));
$smarty->assign('member', $member);
$smarty->assign('views', $data->data);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('createviewform', pieform(create_view_form($groupid)));

if ($can_edit) { // && !$shared) {
    $smarty->display('view/index.tpl');
} else {
    $smarty->display('view/sharedviews.tpl');
}

?>
