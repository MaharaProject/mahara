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
define('MENUITEM', 'engage/index');
define('MENUITEM_SUBPAGE', 'members');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');

define('GROUP', param_integer('id'));
define('SUBSECTIONHEADING', get_string('members'));
$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}

$friends = param_integer('friends', 0);
$role = group_user_access($group->id);

if ($role != 'admin') {
    // Allow non-admins to get to this page when invitefriends is
    // enabled and they're filtering by their friends.
    if (!$friends || !$role || !$group->invitefriends) {
        throw new AccessDeniedException();
    }
}

define('TITLE', $group->name . ' - ' . get_string('sendinvitations', 'group'));

$form = pieform(array(
    'name' => 'addmembers',
    'elements' => array(
        'users' => array(
            'type' => 'userlist',
            'lefttitle' => get_string('potentialmembers', 'group'),
            'righttitle' => get_string('userstobeinvited', 'group'),
            'searchscript' => 'group/membersearchresults.json.php',
            'defaultvalue' => array(),
            'searchparams' => array(
                'id' => GROUP,
                'limit' => 100,
                'html' => 0,
                'membershiptype' => 'notinvited',
                'friends' => $friends,
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
$smarty->assign('subheading', get_string('sendinvitations', 'group'));
$smarty->assign('form', $form);
$smarty->display('group/form.tpl');
exit;

function addmembers_submit(Pieform $form, $values) {
    global $SESSION, $group, $USER, $friends;

    if (empty($values['users'])) {
        redirect(get_config('wwwroot') . 'group/inviteusers.php?id=' . GROUP . ($friends ? '&friends=1' : ''));
    }

    db_begin();
    foreach ($values['users'] as $userid) {
        group_invite_user($group, $userid, $USER->get('id'), 'member', true);
    }
    db_commit();

    $SESSION->add_ok_msg(get_string('invitationssent', 'group', count($values['users'])));
    if ($friends) {
        redirect(group_homepage_url($group));
    }
    redirect(get_config('wwwroot') . 'group/members.php?id=' . GROUP);
}
