<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Clare Lenihan <clare@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require('group.php');
$groupid = param_integer('id');
$returnto = param_alpha('returnto', 'mygroups');

$group = get_record('group', 'id', $groupid, 'deleted', 0);
if (!$group) {
	throw new GroupNotFoundException(get_string('groupnotfound', 'group', $groupid));
}

if ($group->jointype != 'request'
    || record_exists('group_member', 'group', $groupid, 'member', $USER->get('id'))
    || record_exists('group_member_request', 'group', $groupid, 'member', $USER->get('id'))) {
    throw new AccessDeniedException(get_string('cannotrequestjoingroup', 'group'));
}

define('TITLE', get_string('requestjoinspecifiedgroup', 'group', $group->name));

$form = pieform(array(
    'name' => 'requestjoingroup',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'reason' => array(
            'type' => 'text',
            'title' => get_string('reason'),
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . ($returnto == 'find' ? 'group/find.php' : 'group/mygroups.php')
        ),
        'returnto' => array(
            'type' => 'hidden',
            'value' => $returnto
        )
    ),
));

$smarty = smarty();
$smarty->assign('heading', TITLE);
$smarty->assign('form', $form);
$smarty->display('group/requestjoin.tpl');

function requestjoingroup_submit(Pieform $form, $values) {
    global $SESSION, $USER, $group;
    insert_record(
        'group_member_request',
        (object)array(
            'group' => $group->id,
            'member' => $USER->get('id'),
            'ctime' => db_format_timestamp(time()),
            'reason' => isset($values['reason']) ? $values['reason'] : null            
        )
    );
    $ownerlang = get_user_language($group->owner);
    if (isset($values['reason']) && $values['reason'] != '') {
        $message = get_string_from_language($ownerlang, 'grouprequestmessagereason', 'group', display_name($USER, get_record('usr', 'id', $group->owner)), $group->name, $values['reason']);
    } 
    else {
        $message = get_string_from_language($ownerlang, 'grouprequestmessage', 'group', display_name($USER, get_record('usr', 'id', $group->owner)), $group->name);
    }
    require_once('activity.php');
    activity_occurred('maharamessage', 
        array('users'   => array($group->owner),
        'subject' => get_string_from_language($ownerlang, 'grouprequestsubject', 'group'),
        'message' => $message,
        'url'     => get_config('wwwroot') . 'group/view.php?id=' . $group->id));
    $SESSION->add_ok_msg(get_string('grouprequestsent', 'group'));
    redirect($values['returnto'] == 'find' ? '/group/find.php' : '/group/mygroups.php');
}
?>
