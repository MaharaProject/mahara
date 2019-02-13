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
define('MENUITEM', 'engage/index');
define('MENUITEM_SUBPAGE', 'members');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
require_once(get_config('docroot') . 'interaction/lib.php');
define('SUBSECTIONHEADING', get_string('members'));
define('GROUP', param_integer('group'));
$group = group_current_group();

$userid = param_integer('user');
$newrole = param_alpha('role', null);

if (!$user = get_record('usr', 'id', $userid, 'deleted', 0)) {
    throw new UserNotFoundException("Couldn't find user with id $userid");
}
$currentrole = group_user_access($group->id, $userid);
if (!$currentrole) {
    throw new UserNotFoundException("Couldn't find user with id $userid in group $group->id");
}
$role = group_user_access($group->id);
if ($role != 'admin') {
    throw new AccessDeniedException();
}

$roles = group_get_role_info($group->id);
$rolechange_available = false;
foreach ($roles as &$r) {
    $disabled = !group_can_change_role($group->id, $userid, $r->role);
    if (!$disabled && $r->role != $currentrole) {
        $rolechange_available = true;
    }
    $r = array(
        'value'    => $r->display,
        'disabled' => $disabled,
    );
}

if (!$rolechange_available) {
    $SESSION->add_info_msg('This user has no roles they can change to');
    redirect('/group/members.php?id=' . $group->id);
}

$changeform = pieform(array(
    'name'        => 'changerole',
    'method'      => 'post',
    // 'renderer'    => 'oneline',
    'elements'    => array(
        'role' => array(
            'title' => get_string('changerolefromto', 'group', get_string($currentrole, 'grouptype.'.$group->grouptype)) . ': ',
            'type' => 'select',
            'collapseifoneoption' => false,
            'options' => $roles,
            'defaultvalue' => $currentrole,
        ),
        'submit' => array(
            'type' => 'submit',
            'class' => 'btn-primary btn sm',
            'value' => get_string('submit'),
        )
    )
));

function changerole_validate(Pieform $form, $values) {
    global $user, $group;
    if (!group_can_change_role($group->id, $user->id, $values['role'])) {
        $form->set_error('role', get_string('usercannotchangetothisrole', 'group'));
    }
}

function changerole_submit(Pieform $form, $values) {
    global $user, $group, $SESSION, $currentrole;
    if ($values['role'] != $currentrole) {
        group_change_role($group->id, $user->id, $values['role']);
        $SESSION->add_ok_msg(get_string('rolechanged', 'group'));
    }
    redirect('/group/members.php?id='.$group->id);
}

define('TITLE', $group->name . ' - ' . get_string('changerole', 'group'));

$smarty = smarty();
$smarty->assign('heading', $group->name);
$smarty->assign('subheading', get_string('changeroleofuseringroup', 'group', display_name($user), $group->name));
$smarty->assign('changeform', $changeform);

$smarty->display('group/changerole.tpl');
