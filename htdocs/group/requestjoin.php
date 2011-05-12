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
$returnto = param_alpha('returnto', 'mygroups');

define('GROUP', $groupid);
$group = group_current_group();

if ($group->jointype != 'request'
    || record_exists('group_member', 'group', $groupid, 'member', $USER->get('id'))
    || record_exists('group_member_request', 'group', $groupid, 'member', $USER->get('id'))) {
    throw new AccessDeniedException(get_string('cannotrequestjoingroup', 'group'));
}

define('TITLE', $group->name);

$goto = get_config('wwwroot') . 'group/' . $returnto . '.php' . ($returnto == 'view' ? ('?id=' . $groupid) : '');

$form = pieform(array(
    'name' => 'requestjoingroup',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'reason' => array(
            'type' => 'textarea',
            'title' => get_string('reason'),
            'cols'  => 50,
            'rows'  => 4,
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('request', 'group'), get_string('cancel')),
            'goto' => $goto
        ),
        'returnto' => array(
            'type' => 'hidden',
            'value' => $returnto
        )
    ),
));

$smarty = smarty();
$smarty->assign('subheading', get_string('requestjoinspecifiedgroup', 'group', $group->name));
$smarty->assign('form', $form);
$smarty->display('group/requestjoin.tpl');

function requestjoingroup_submit(Pieform $form, $values) {
    global $SESSION, $USER, $group, $goto;
    insert_record(
        'group_member_request',
        (object)array(
            'group' => $group->id,
            'member' => $USER->get('id'),
            'ctime' => db_format_timestamp(time()),
            'reason' => isset($values['reason']) ? $values['reason'] : null            
        )
    );
    // Send request to all group admins
    require_once('activity.php');
    $groupadmins = get_column('group_member', 'member', 'group', $group->id, 'role', 'admin');
    foreach ($groupadmins as $groupadmin) {
        $adminlang = get_user_language($groupadmin);
        if (isset($values['reason']) && $values['reason'] != '') {
            $message = get_string_from_language($adminlang, 'grouprequestmessagereason', 'group', display_name($USER, get_record('usr', 'id', $groupadmin)), $group->name, $values['reason']);
        } 
        else {
            $message = get_string_from_language($adminlang, 'grouprequestmessage', 'group', display_name($USER, get_record('usr', 'id', $groupadmin)), $group->name);
        }
        activity_occurred('maharamessage', array(
            'users'   => array($groupadmin),
            'subject' => get_string_from_language($adminlang, 'grouprequestsubject', 'group'),
            'message' => $message,
            'url'     => get_config('wwwroot') . 'group/members.php?id=' . $group->id . '&membershiptype=request',
            'strings' => (object) array(
                'urltext' => (object) array(
                    'key'     => 'pendingmembers',
                    'section' => 'group',
                ),
            ),
        ));
    }
    $SESSION->add_ok_msg(get_string('grouprequestsent', 'group'));
    redirect($goto);
}
