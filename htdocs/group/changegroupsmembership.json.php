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
 * @author     Ruslan Kabalin <ruslan.kabalin@luns.net.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2009 Lancaster University Network Services Limited
 *                      http://www.luns.net.uk
 *
 */


define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');

$data['error'] = false;
$data['message'] = null;

$initialgroups = param_integer_list('initialgroups', array());
$resultgroups = param_integer_list('resultgroups', array());
$userid = param_integer('userid');
$jointype = param_variable('jointype');

// Prevent group membership changing done by ordinary members, Tutors can only
// add members to group and cannot remove anyone. Group admins can do anything.
// With regard to invitation, both admins and tutors can invite people.

foreach (array_unique(array_merge($initialgroups, $resultgroups)) as $groupid) {
    if (!group_user_access($groupid)) {
        json_reply('local', get_string('accessdenied', 'error'));
        break;
    }
    switch (group_user_access($groupid)) {
        case 'member':
            json_reply('local', get_string('accessdenied', 'error'));
            break;
        case 'tutor':
            if ($usertype = group_user_access($groupid, $userid)) {
                if (($usertype == 'member') && in_array($groupid, array_diff($initialgroups, $resultgroups))) {
                    json_reply('local', get_string('cantremovemember', 'group'));
                }
                elseif ($usertype != 'member' && in_array($groupid, array_diff($initialgroups, $resultgroups))) {
                    json_reply('local', get_string('cantremoveuserisadmin', 'group'));
                }
            }
    }
}

$groupdata = get_records_select_assoc('group', 'id IN (' . join(',', array_unique(array_merge($initialgroups, $resultgroups))) . ')');

if ($jointype == 'controlled') {
    db_begin();
    //remove group membership
    if ($groupstoremove = array_diff($initialgroups, $resultgroups)) {
        $groupstoremovemail = '';
        foreach ($groupstoremove as $groupid) {
            group_remove_user($groupid, $userid, $role=null);
            $groupstoremovemail .= $groupdata[$groupid]->name . "\n";
        }
    }
    //add group membership
    if ($groupstoadd = array_diff($resultgroups, $initialgroups)) {
        $groupstoaddmail = '';
        foreach ($groupstoadd as $groupid) {
            group_add_user($groupid, $userid, $role=null);
            $groupstoaddmail .= $groupdata[$groupid]->name . "\n";
        }
    }
    db_commit();

    // Users notification
    $userrecord = get_record('usr', 'id', $userid);
    $lang = get_user_language($userid);

    $n = new StdClass;
    $n->users = array($userid);
    $n->subject = get_string_from_language($lang, 'changedgroupmembershipsubject', 'group');
    $n->message = '';

    if (isset($groupstoaddmail)) {
        $n->message .= get_string_from_language($lang, 'addedtogroupsmessage', 'group', display_name($USER, $userrecord), $groupstoaddmail);
    }
    if (isset($groupstoremovemail)) {
        $n->message .= get_string_from_language($lang, 'removedfromgroupsmessage', 'group', display_name($USER, $userrecord), $groupstoremovemail);
    }

    require_once(get_config('libroot') . 'activity.php');
    activity_occurred('maharamessage', $n);
    $data['message'] = get_string('changedgroupmembership', 'group');
}
elseif ($jointype == 'invite') {
    if ($groupstoadd = array_diff($resultgroups, $initialgroups)) {
        foreach ($groupstoadd as $groupid) {
            group_invite_user($groupdata[$groupid], $userid, $USER->get('id'));
        }
    }
    $data['message'] = get_string('userinvited', 'group');
}
json_reply(false, $data);
