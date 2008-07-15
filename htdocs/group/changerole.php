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
define('MENUITEM', 'groups');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
require_once(get_config('docroot') . 'interaction/lib.php');

$groupid = param_integer('group');
$userid = param_integer('user');
$newrole = param_alpha('role', null);
$remove = param_integer('remove', null);

if (!$group = get_record('group', 'id', $groupid, 'deleted', 0)) {
    throw new GroupNotFoundException("Couldn't find group with id $groupid");
}
if (!$user = get_record('usr', 'id', $userid, 'deleted', 0)) {
    throw new UserNotFoundException("Couldn't find user with id $userid");
}
$userrole = group_user_access($groupid, $userid);
if (!$userrole) {
    throw new UserNotFoundException("Couldn't find user with id $userid in group $groupid");
}
$role = group_user_access($groupid);
if ($role != 'admin') {
    throw new AccessDeniedException();
}

if ($newrole && $newrole != $userrole) {
    set_field('group_member', 'role', $newrole, 'group', $groupid, 'member', $userid);
    $SESSION->add_ok_msg(get_string('rolechanged', 'group'));
    redirect('/group/members.php?id='.$groupid);
}
else if ($remove) {
    delete_records('group_member', 'group', $groupid, 'member', $userid);
    $SESSION->add_ok_msg(get_string('userremoved', 'group'));
    redirect('/group/members.php?id='.$groupid);
}

define('TITLE', $group->name . ' - ' . get_string('Changerole', 'group'));

$roleinfo = group_get_role_info($groupid);

$smarty = smarty(array(), array(), array(), array('sideblocks' => array(interaction_sideblock($groupid, $role))));
$smarty->assign('group', $group);
$smarty->assign('groupid', $groupid);
$smarty->assign('userid', $userid);
$smarty->assign('userrole', $userrole);
$smarty->assign('subtitle', get_string('changeroleofuseringroup', 'group', display_name($user), $group->name));
$smarty->assign('roles', $roleinfo);

$smarty->display('group/changerole.tpl');

?>
