<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Ruslan Kabalin <ruslan.kabalin@luns.net.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
$addtype = param_variable('addtype');

// Prevent group membership changing done by ordinary members, Tutors can only
// add members to group and cannot remove anyone. Group admins can do anything.
// With regard to invitation, both admins and tutors can invite people.

$allgroups = array_unique(array_merge($initialgroups, $resultgroups));
$groupdata = get_records_select_assoc(
    'group',
    'id IN (' . join(',', array_fill(0, count($allgroups), '?')) . ')',
    $allgroups
);

foreach (group_get_grouptypes() as $grouptype) {
    safe_require('grouptype', $grouptype);
}

foreach ($allgroups as $groupid) {
    if (!$loggedinrole = group_user_access($groupid)) {
        json_reply('local', get_string('accessdenied', 'error'));
    }
    if ($loggedinrole == 'admin') {
        continue;
    }
    if (!in_array($loggedinrole, call_static_method('GroupType' . $groupdata[$groupid]->grouptype, 'get_view_assessing_roles'))) {
        json_reply('local', get_string('accessdenied', 'error'));
    }
    if (group_user_access($groupid, $userid) && in_array($groupid, array_diff($initialgroups, $resultgroups))) {
        json_reply('local', get_string('cantremovememberfromgroup', 'group', hsc($groupdata[$groupid]->name)));
    }
}

if ($addtype == 'add') {
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
        $n->message .= get_string_from_language($lang, 'addedtongroupsmessage', 'group', count($groupstoadd), display_name($USER, $userrecord), $groupstoaddmail);
    }
    if (isset($groupstoremovemail)) {
        $n->message .= get_string_from_language($lang, 'removedfromngroupsmessage', 'group', count($groupstoremove), display_name($USER, $userrecord), $groupstoremovemail);
    }

    require_once(get_config('libroot') . 'activity.php');
    activity_occurred('maharamessage', $n);
    $data['message'] = get_string('changedgroupmembership', 'group');
}
else if ($addtype == 'invite') {
    if ($groupstoadd = array_diff($resultgroups, $initialgroups)) {
        foreach ($groupstoadd as $groupid) {
            group_invite_user($groupdata[$groupid], $userid, $USER->get('id'));
        }
    }
    $data['message'] = get_string('userinvited', 'group');
}
json_reply(false, $data);
