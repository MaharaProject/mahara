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

define('PUBLIC', 1);
define('INTERNAL', 1);
define('MENUITEM', 'groups/members');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');

define('GROUP', param_integer('id'));

$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}

$role = group_user_access($group->id);
if ($role != 'admin') {
    throw new AccessDeniedException();
}

if ($group->jointype != 'controlled') {
    redirect(get_config('wwwroot') . 'group/members.php?id=' . GROUP);
}

define('TITLE', $group->name . ' - ' . get_string('addmembers', 'group'));

$form = pieform(array(
    'name' => 'addmembers',
    'elements' => array(
        'users' => array(
            'type' => 'userlist',
            'lefttitle' => get_string('potentialmembers', 'group'),
            'righttitle' => get_string('userstobeadded', 'group'),
            'searchscript' => 'group/membersearchresults.json.php',
            'defaultvalue' => array(),
            'searchparams' => array(
                'id' => GROUP,
                'limit' => 100,
                'html' => 0,
                'membershiptype' => 'nonmember',
            ),
        ),
        'submit' => array(
            'type' => 'submit',
            'class' => 'btn-primary',
            'value' => get_string('submit'),
        )
    )
));

$smarty = smarty();
$smarty->assign('subheading', get_string('addmembers', 'group'));
$smarty->assign('form', $form);
$smarty->display('group/form.tpl');
exit;

function addmembers_submit(Pieform $form, $values) {
    global $SESSION;

    if (empty($values['users'])) {
        redirect(get_config('wwwroot') . 'group/addmembers.php?id=' . GROUP);
    }

    db_begin();
    foreach ($values['users'] as $userid) {
        group_add_user(GROUP, $userid);
    }
    db_commit();

    $SESSION->add_ok_msg(get_string('newmembersadded', 'group', count($values['users'])));
    redirect(get_config('wwwroot') . 'group/members.php?id=' . GROUP);
}
