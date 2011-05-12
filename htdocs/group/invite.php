<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('group.php');
$groupid = param_integer('id');
$userid = param_integer('user');

define('GROUP', $groupid);
$group = group_current_group();

$user = get_record('usr', 'id', $userid, 'deleted', 0);
if (!$user) {
    throw new UserNotFoundException(get_string('usernotfound', 'group', $userid));
}

if ($group->jointype != 'invite'
    || group_user_access($groupid) != 'admin') {
    throw new AccessDeniedException(get_string('cannotinvitetogroup', 'group'));
}

if (record_exists('group_member', 'group', $groupid, 'member', $userid)
    || record_exists('group_member_invite', 'group', $groupid, 'member', $userid)) {
    throw new UserException(get_string('useralreadyinvitedtogroup', 'group'));
}

define('TITLE', get_string('invitemembertogroup', 'group', display_name($userid), $group->name));

$roles = group_get_role_info($groupid);
foreach ($roles as $k => &$v) {
    $v = $v->display;
}

safe_require('grouptype', $group->grouptype);
$form = pieform(array(
    'name' => 'invitetogroup',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'reason' => array(
            'type' => 'textarea',
            'cols'  => 50,
            'rows'  => 4,
            'title' => get_string('reason'),
        ),
        'role' => array(
            'type'    => 'select',
            'options' => $roles,
            'title'   => get_string('Role', 'group'),
            'defaultvalue' => call_static_method('GroupType' . $group->grouptype, 'default_role'),
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('invite', 'group'), get_string('cancel')),
            'goto' => get_config('wwwroot') . 'user/view.php?id=' . $userid,
        )
    ),
));

$smarty = smarty();
$smarty->assign('subheading', TITLE);
$smarty->assign('form', $form);
$smarty->display('group/invite.tpl');

function invitetogroup_submit(Pieform $form, $values) {
    global $SESSION, $USER, $group, $user;
    group_invite_user($group, $user->id, $USER, $values['role']);
    $SESSION->add_ok_msg(get_string('userinvited', 'group'));
    redirect('/user/view.php?id=' . $user->id);
}
