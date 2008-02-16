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
require_once('pieforms/pieform.php');
require('group.php');
$groupid = param_integer('id');
$userid = param_integer('user');

$group = get_record('group', 'id', $groupid, 'deleted', 0);
if (!$group) {
    throw new GroupNotFoundException(get_string('groupnotfound', 'group', $groupid));
}

$user = get_record('usr', 'id', $userid, 'deleted', 0);
if (!$user) {
    throw new UserNotFoundException(get_string('usernotfound', 'group', $userid));
}

if ($group->jointype != 'invite'
    || record_exists('group_member', 'group', $groupid, 'member', $userid)
    || record_exists('group_member_invite', 'group', $groupid, 'member', $userid)
    || $group->owner != $USER->get('id')) {
    throw new AccessDeniedException(get_string('cannotinvitetogroup', 'group'));
}

define('TITLE', get_string('invitemembertogroup', 'group', display_name($userid), $group->name));

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
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('invite', 'group'), get_string('cancel')),
            'goto' => get_config('wwwroot') . 'user/view.php?id=' . $userid,
        )
    ),
));

$smarty = smarty();
$smarty->assign('heading', TITLE);
$smarty->assign('form', $form);
$smarty->assign('group', $group);
$smarty->display('group/invite.tpl');

function invitetogroup_submit(Pieform $form, $values) {
    global $SESSION, $USER, $group, $user;
    
    $data = new StdClass;
    $data->group = $group->id;
    $data->member= $user->id;
    $data->ctime = db_format_timestamp(time());
    $data->tutor = 0;
    insert_record('group_member_invite', $data);
    $lang = get_user_language($user->id);
    activity_occurred('maharamessage', 
        array('users'   => array($user->id), 
              'subject' => get_string_from_language($lang, 'invitetogroupsubject', 'group'),
              'message' => get_string_from_language($lang, 'invitetogroupmessage', 'group', display_name($USER, $user), $group->name),
              'url'     => get_config('wwwroot') 
              . 'group/view.php?id=' . $group->id));
    $SESSION->add_ok_msg(get_string('userinvited', 'group'));
    redirect('/user/view.php?id=' . $user->id);
}
?>
