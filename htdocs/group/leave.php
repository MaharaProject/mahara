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

define('GROUP', $groupid);
$group = group_current_group();

define('TITLE', $group->name);

if (!group_user_access($group->id)) {
    throw new AccessDeniedException(get_string('notamember', 'group'));
}

if (!group_user_can_leave($group)) {
    throw new AccessDeniedException(get_string('cantleavegroup', 'group'));
}

$goto = get_config('wwwroot') . 'group/index.php';

$form = pieform(array(
    'name' => 'leavegroup',
    'renderer' => 'div',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'class' => 'btn-primary',
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => $goto
        )
    ),
));

$smarty = smarty();
$smarty->assign('headingclass', 'page-header');
$smarty->assign('subheading', get_string('leavespecifiedgroup', 'group', $group->name));
$smarty->assign('form', $form);
$smarty->assign('message', get_string('groupconfirmleave', 'group'));
$smarty->assign('group', $group);
$smarty->display('group/leave.tpl');

function leavegroup_submit(Pieform $form, $values) {
    global $USER, $SESSION, $groupid, $goto;
    group_remove_user($groupid, $USER->get('id'));
    $SESSION->add_ok_msg(get_string('leftgroup', 'group'));
    redirect($goto);
}
