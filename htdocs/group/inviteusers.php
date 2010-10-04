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

define('PUBLIC', 1);
define('INTERNAL', 1);
define('MENUITEM', 'groups/members');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
require_once('pieforms/pieform.php');

define('GROUP', param_integer('id'));

$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}

$role = group_user_access($group->id);
if ($role != 'admin') {
    throw new AccessDeniedException();
}

if ($group->jointype != 'invite') {
    redirect(get_config('wwwroot') . 'group/members.php?id=' . GROUP);
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
            'filter' => false,
            'searchparams' => array(
                'id' => GROUP,
                'limit' => 100,
                'html' => 0,
                'membershiptype' => 'notinvited',
            ),
        ),
        'submit' => array(
            'type' => 'submit',
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
    global $SESSION, $group, $USER;

    if (empty($values['users'])) {
        redirect(get_config('wwwroot') . 'group/inviteusers.php?id=' . GROUP);
    }

    db_begin();
    foreach ($values['users'] as $userid) {
        group_invite_user($group, $userid, $USER->get('id'), 'member', true);
    }
    db_commit();

    $SESSION->add_ok_msg(get_string('invitationssent', 'group', count($values['users'])));
    redirect(get_config('wwwroot') . 'group/members.php?id=' . GROUP);
}
