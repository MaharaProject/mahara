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
define('PUBLIC', 1);
define('MENUITEM', 'groups/info');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
require_once('searchlib.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once(get_config('libroot') . 'view.php');
safe_require('artefact', 'file');

define('GROUP', param_integer('id'));
$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}

define('TITLE', $group->name);
$group->ctime = strftime(get_string('strftimedate'), $group->ctime);

$group->admins = get_column_sql("SELECT \"member\"
    FROM {group_member}
    WHERE \"group\" = ?
    AND \"role\" = 'admin'", array($group->id));

$role = group_user_access($group->id);
$group->role = $role;

if (is_logged_in()) {
    $afterjoin = param_variable('next', 'view');
    if ($role) {
        if ($role == 'admin') {
            $group->membershiptype = 'admin';
            $group->requests = count_records('group_member_request', 'group', $group->id);
        }
        else {
            $group->membershiptype = 'member';
        }
        $group->canleave = group_user_can_leave($group->id);
    }
    else if ($group->jointype == 'invite'
             and $invite = get_record('group_member_invite', 'group', $group->id, 'member', $USER->get('id'))) {
        $group->membershiptype = 'invite';
        $group->invite = group_get_accept_form('invite', $group->id, $afterjoin);
    }
    else if ($group->jointype == 'request'
             and $request = get_record('group_member_request', 'group', $group->id, 'member', $USER->get('id'))) {
        $group->membershiptype = 'request';
    }
    else if ($group->jointype == 'open') {
        $group->groupjoin = group_get_join_form('joingroup', $group->id, $afterjoin);
    }
}

$group->grouptypedescription = get_string('grouptypedescription', 'group', get_string('name', 'grouptype.' . $group->grouptype), get_string('membershiptype.'.$group->jointype, 'group'));

$filecounts = ArtefactTypeFileBase::count_user_files(null, $group->id, null);

// Latest forums posts
// NOTE: it would be nicer if there was some generic way to get information 
// from any installed interaction. But the only interaction plugin is forum, 
// and group info pages might be replaced with views anyway...
$foruminfo = null;
if ($role || $group->public) {
    $foruminfo = get_records_sql_array('
        SELECT
            p.id, p.subject, p.body, p.poster, p.topic, t.forum, pt.subject AS topicname
        FROM
            {interaction_forum_post} p
            INNER JOIN {interaction_forum_topic} t ON (t.id = p.topic)
            INNER JOIN {interaction_instance} i ON (i.id = t.forum)
            INNER JOIN {interaction_forum_post} pt ON (pt.topic = p.topic AND pt.parent IS NULL)
        WHERE
            i.group = ?
            AND i.deleted = 0
            AND t.deleted = 0
            AND p.deleted = 0
        ORDER BY
            p.ctime DESC
        LIMIT 5;
        ', array($group->id));
}

if ($role) {
    // For group members, display a list of views that others have
    // shared to the group
    $sharedviews = View::get_sharedviews_data(null, 0, $group->id);
    if (group_user_can_assess_submitted_views($group->id, $USER->get('id'))) {
        // Display a list of views submitted to the group
        $allsubmittedviews = View::get_submitted_views($group->id);
    }
}

if (group_allows_submission($group->grouptype) && ($userviewdata = View::get_user_views())) {
    // A user can submit more than one view to the same group, but no view can be
    // submitted to more than one group.

    // Display a list of views this user has submitted to this group, and a submission
    // form containing drop-down of their unsubmitted views.

    $mysubmittedviews = View::get_submitted_views($group->id, $USER->get('id'));

    if (!empty($mysubmittedviews)) {
        foreach ($mysubmittedviews as &$v) {
            $url = get_config('wwwroot') . 'view/view.php?id=' . $v['id'];
            if ($v['submittedtime']) {
                $v['strsubmitted'] = get_string('youhavesubmittedon', 'view', $url, $v['title'], format_date($v['submittedtime']));
            }
            else {
                $v['strsubmitted'] = get_string('youhavesubmitted', 'view', $url, $v['title']);
            }
        }
    }

    $group_view_submission_form = group_view_submission_form($group->id, $userviewdata);
}

$smarty = smarty();
$smarty->assign('group', $group);
$smarty->assign('groupid', $group->id);
$smarty->assign('foruminfo', $foruminfo);
$smarty->assign('membercount', count_records('group_member', 'group', $group->id));
$smarty->assign('viewcount', count_records('view', 'group', $group->id));
$smarty->assign('filecount', $filecounts->files);
$smarty->assign('foldercount', $filecounts->folders);

if (isset($sharedviews)) {
    $smarty->assign('sharedviews', $sharedviews->data);
}
if (isset($allsubmittedviews)) {
    $smarty->assign('allsubmittedviews', $allsubmittedviews);
}
if (isset($mysubmittedviews)) {
    $smarty->assign('mysubmittedviews', $mysubmittedviews);
}
if (isset($group_view_submission_form)) {
    $smarty->assign('group_view_submission_form', $group_view_submission_form);
}

$smarty->assign('role', $role);
$smarty->display('group/view.tpl');

?>
