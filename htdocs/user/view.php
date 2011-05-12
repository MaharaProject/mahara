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
// Technically these are lies, but we set them like this to hook in the right 
// plugin stylesheet. This file should be provided by artefact/internal anyway.
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'internal');
define('SECTION_PAGE', 'view');

require(dirname(dirname(__FILE__)).'/init.php');
require_once('group.php');
require_once('pieforms/pieform.php');
require_once(get_config('libroot') . 'view.php');

$loggedinid = $USER->get('id');
if (!empty($loggedinid)) {
    $userid = param_integer('id', $loggedinid);
}
else {
    $userid = param_integer('id');
}
if ($userid == 0) {
    redirect();
}

// Get the user's details

if (!$user = get_record('usr', 'id', $userid, 'deleted', 0)) {
    throw new UserNotFoundException("User with id $userid not found");
}
$is_friend = is_friend($userid, $loggedinid);

if ($loggedinid == $userid) {
    $view = $USER->get_profile_view();
}
else {
    $userobj = new User();
    $userobj->find_by_id($userid);
    $view = $userobj->get_profile_view();
}

$viewid = $view->get('id');
# access will either be logged in (always) or public as well
if (!$view || !can_view_view($viewid)) {
    throw new AccessDeniedException(get_string('youcannotviewthisusersprofile', 'error'));
}

$javascript = array('paginator', 'jquery', 'lib/pieforms/static/core/pieforms.js', 'artefact/resume/resumeshowhide.js');
$javascript = array_merge($javascript, $view->get_blocktype_javascript());

// Set up theme
$viewtheme = $view->get('theme');
if ($viewtheme && $THEME->basename != $viewtheme) {
    $THEME = new Theme($viewtheme);
}
$stylesheets = array('<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/views.css">');

$name = display_name($user);
define('TITLE', $name);
$smarty = smarty(
    $javascript,
    $stylesheets,
    array(),
    array(
        'stylesheets' => array('style/views.css'),
        'sidebars'    => false,
    )
);

$sql = "SELECT g.*, a.type FROM {group} g JOIN (
SELECT gm.group, 'invite' AS type
    FROM {group_member_invite} gm WHERE gm.member = ?
UNION
SELECT gm.group, 'request' AS type
    FROM {group_member_request} gm WHERE gm.member = ?
UNION
SELECT gm.group, gm.role AS type
    FROM {group_member} gm
    WHERE gm.member = ?
) AS a ON a.group = g.id
WHERE g.deleted = 0
ORDER BY g.name";
if (!$allusergroups = get_records_sql_assoc($sql, array($userid, $userid, $userid))) {
    $allusergroups = array();
}
if (!empty($loggedinid) && $loggedinid != $userid) {

    $invitedlist = array();   // Groups admin'ed by the logged in user that the displayed user has been invited to
    $requestedlist = array(); // Groups admin'ed by the logged in user that the displayed user has requested membership of

    // Get the logged in user's "invite only" groups
    if ($groups = get_records_sql_array("SELECT g.*
        FROM {group} g
        JOIN {group_member} gm ON (gm.group = g.id)
        WHERE gm.member = ?
        AND g.jointype = 'invite'
        AND gm.role = 'admin'
        AND g.deleted = 0", array($loggedinid))) {
        $invitelist = array();
        foreach ($groups as $group) {
            if (array_key_exists($group->id, $allusergroups)) {
                $invitedlist[$group->id] = $group->name;
                continue;
            }
            $invitelist[$group->id] = $group->name;
        }
        $smarty->assign('invitedlist', join(', ', $invitedlist));
        if (count($invitelist) > 0) {
            $default = array_keys($invitelist);
            $default = $default[0];
            $inviteform = pieform(array(
                'name'              => 'invite',
                'successcallback'   => 'invite_submit',
                'renderer'          => 'div',
                'elements'          => array(
                    'group' => array(
                        'type'                => 'select',
                        'title'               => get_string('inviteusertojoingroup', 'group'),
                        'collapseifoneoption' => false,
                        'options'             => $invitelist,
                        'defaultvalue'        => $default,
                    ),
                    'id' => array(
                        'type'  => 'hidden',
                        'value' => $userid,
                    ),
                    'submit' => array(
                        'type'  => 'submit',
                        'value' => get_string('sendinvitation', 'group'),
                    ),
                ),
            ));
            $smarty->assign('inviteform',$inviteform);
        }
    }

    // Get (a) controlled membership groups,
    //     (b) request membership groups where the displayed user has requested membership,
    // where the logged in user either:
    // 1. is a group admin, or;
    // 2. has a role in the list of roles who are allowed to assess submitted views for the given grouptype
    if ($groups = get_records_sql_array("SELECT g.*, gm.ctime
          FROM {group} g
          JOIN {group_member} gm ON (gm.group = g.id)
          JOIN {grouptype_roles} gtr ON (gtr.grouptype = g.grouptype AND gtr.role = gm.role)
          LEFT JOIN {group_member_request} gmr ON (gmr.member = ? AND gmr.group = g.id)
          WHERE gm.member = ?
          AND (g.jointype = 'controlled' OR (g.jointype = 'request' AND gmr.member = ?))
          AND (gm.role = 'admin' OR gtr.see_submitted_views = 1)
          AND g.deleted = 0", array($userid,$loggedinid,$userid))) {
        $controlledlist = array();
        foreach ($groups as $group) {
            if (array_key_exists($group->id, $allusergroups)) {
                continue;
            }
            if ($group->jointype == 'request') {
                $requestedlist[$group->id] = $group->name;
            }
            else {
                $controlledlist[$group->id] = $group->name;
            }
        }
        $smarty->assign('requestedlist', join(', ', $requestedlist));
        if (count($controlledlist) > 0) {
            $default = array_keys($controlledlist);
            $default = $default[0];
            $addform = pieform(array(
                'name'                => 'addmember',
                'successcallback'     => 'addmember_submit',
                'renderer'            => 'div',
                'autofocus'           => false,
                'elements'            => array(
                    'group' => array(
                        'type'    => 'select',
                        'title'   => get_string('addusertogroup', 'group'),
                        'collapseifoneoption' => false,
                        'options' => $controlledlist,
                        'defaultvalue' => $default,
                    ),
                    'member' => array(
                        'type'  => 'hidden',
                        'value' => $userid, 
                    ),
                    'submit' => array(
                        'type'  => 'submit',
                        'value' => get_string('add'),
                    ),
                ),
            ));
            $smarty->assign('addform',$addform);
        } 
    }

    if ($is_friend) {
        $relationship = 'existingfriend';
    }
    else if (record_exists('usr_friend_request', 'requester', $loggedinid, 'owner', $userid)) {
        $relationship = 'requestedfriendship';
    }
    else if ($record = get_record('usr_friend_request', 'requester', $userid, 'owner', $loggedinid)) {
        $relationship = 'pending';
        $requestform = pieform(array(
            'name' =>'approve_deny_friendrequest',
            'renderer' => 'oneline',
            'autofocus' => false,
            'elements' => array(
                'approve' => array(
                    'type' => 'submit',
                    'value' => get_string('approverequest', 'group'),
                ),
                'deny' => array(
                    'type' => 'submit',
                    'value' => get_string('denyrequest', 'group')
                ),
                'id' => array(
                    'type' => 'hidden',
                    'value' => $userid
                )
            )
        ));
        $smarty->assign('message', $record->message);
        $smarty->assign('requestform', $requestform);
    }
    else {
        $relationship = 'none';
        $friendscontrol = get_account_preference($userid, 'friendscontrol');
        if ($friendscontrol == 'auto') {
            $newfriendform = pieform(array(
                'name' => 'addfriend',
                'autofocus' => false,
                'renderer' => 'div',
                'elements' => array(
                    'add' => array(
                        'type' => 'submit',
                        'value' => get_string('addtomyfriends', 'group')
                    ),
                    'id' => array(
                        'type' => 'hidden',
                        'value' => $userid
                    )
                )
            ));
            $smarty->assign('newfriendform', $newfriendform);
        }
        $smarty->assign('friendscontrol', $friendscontrol);
    }
    $smarty->assign('relationship', $relationship);

}

if ($userid != $USER->get('id') && $USER->is_admin_for_user($user) && is_null($USER->get('parentuser'))) {
    $loginas = get_string('loginasuser', 'admin', display_username($user));
} else {
    $loginas = null;
}
$smarty->assign('loginas', $loginas);

$smarty->assign('institutions', get_institution_string_for_user($userid));
$smarty->assign('canmessage', $loggedinid != $userid && can_send_message($loggedinid, $userid));
$smarty->assign('USERID', $userid);
$smarty->assign('viewtitle', get_string('usersprofile', 'mahara', display_name($user, null, true)));
$smarty->assign('viewtype', 'profile');

if (get_config('viewmicroheaders')) {
    $smarty->assign('microheaders', true);
    $smarty->assign('microheadertitle', $view->display_title(true, false));
    if ($loggedinid && $loggedinid == $userid) {
        $microheaderlinks = array(
            array(
                'name' => get_string('editthisview', 'view'),
                'url' => get_config('wwwroot') . 'view/blocks.php?profile=1',
            ),
        );
        $smarty->assign('microheaderlinks', $microheaderlinks);
    }
}
else {
    if ($loggedinid && $loggedinid == $userid) {
        $smarty->assign('ownprofile', true);
    }
    $smarty->assign('pageheadinghtml', $view->display_title(false));
}

$smarty->assign('viewcontent', $view->build_columns());
$smarty->display('user/view.tpl');

mahara_log('views', "$viewid"); // Log view visits

// Send an invitation to the user to join a group
function invite_submit(Pieform $form, $values) {
    global $userid;
    redirect('/group/invite.php?id=' . $values['group'] . '&user=' . $userid);
}

// Add the user as a member of a group
function addmember_submit(Pieform $form, $values) {
    global $USER, $SESSION, $userid;

    $data = new StdClass;
    $data->group  = $values['group'];
    $data->member = $userid;
    $data->ctime  = db_format_timestamp(time());
    $data->role  = 'member'; // TODO: modify the dropdown to allow the role to be chosen
    $ctitle = get_field('group', 'name', 'id', $data->group);
    $adduser = get_record('usr', 'id', $data->member);

    try {
        insert_record('group_member', $data);
        delete_records('group_member_request', 'member', $userid, 'group', $data->group);
        $lang = get_user_language($userid);
        require_once(get_config('libroot') . 'activity.php');
        activity_occurred('maharamessage', array(
            'users'   => array($userid),
            'subject' => get_string_from_language($lang, 'addedtogroupsubject', 'group'),
            'message' => get_string_from_language($lang, 'addedtogroupmessage', 'group', display_name($USER, $adduser), $ctitle),
            'url'     => get_config('wwwroot') . 'group/view.php?id=' . $values['group'],
            'urltext' => $ctitle,
        ));
        $SESSION->add_ok_msg(get_string('useradded', 'group'));
    }
    catch (SQLException $e) {
        $SESSION->add_error_msg(get_string('adduserfailed', 'group'));
    }
    redirect('/user/view.php?id=' . $userid);
}

function approve_deny_friendrequest_submit(Pieform $form, $values) {
    if (isset($values['deny'])) {
        redirect('/user/denyrequest.php?id=' . $values['id'] . '&returnto=view');
    }
    else {
        acceptfriend_submit($form, $values);
    }
}
