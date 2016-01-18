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
define('MENUITEM', 'groups');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
$groupid = param_integer('id');
$userid = param_integer('user');

define('GROUP', $groupid);
$group = group_current_group();

$user = get_record('usr', 'id', $userid, 'deleted', 0);
if (!$user) {
    throw new UserNotFoundException(get_string('usernotfound', 'group', $userid));
}

$role = group_user_access($groupid);

if ($role != 'admin' && !group_user_can_assess_submitted_views($group->id, $USER->get('id'))) {
    if (!$group->invitefriends || !is_friend($user->id, $USER->get('id'))) {
        throw new AccessDeniedException(get_string('cannotinvitetogroup', 'group'));
    }
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
            'ignore'  => $role != 'admin',
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'class' => 'btn-primary',
            'value' => array(get_string('invite', 'group'), get_string('cancel')),
            'goto' => profile_url($user),
        )
    ),
));

$smarty = smarty();
$smarty->assign('subheading', TITLE);
$smarty->assign('form', $form);
$smarty->display('group/invite.tpl');

function invitetogroup_submit(Pieform $form, $values) {
    global $SESSION, $USER, $group, $user;
    group_invite_user($group, $user->id, $USER, isset($values['role']) ? $values['role'] : null);
    $SESSION->add_ok_msg(get_string('userinvited', 'group'));
    redirect(profile_url($user));
}
