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

if (param_variable('acceptfriend_submit', null)) {
    acceptfriend_form(param_integer('id'));
}
else if (param_variable('addfriend_submit', null)) {
    addfriend_form(param_integer('id'));
}

$loggedinid = $USER->get('id');

if ($profileurlid = param_alphanumext('profile', null)) {
    if (!$user = get_record('usr', 'urlid', $profileurlid, 'deleted', 0)) {
        throw new UserNotFoundException("User $profileurlid not found");
    }
    $userid = $user->id;
}
else if (!empty($loggedinid)) {
    $userid = param_integer('id', $loggedinid);
}
else {
    $userid = param_integer('id');
}
if ($userid == 0) {
    redirect();
}

// Get the user's details
if (!isset($user)) {
    if (!$user = get_record('usr', 'id', $userid, 'deleted', 0)) {
        throw new UserNotFoundException("User with id $userid not found");
    }
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

# access will either be logged in (always) or public as well
if (!$view) {
    // No access, so restrict profile view
    throw new AccessDeniedException(get_string('youcannotviewthisusersprofile', 'error'));
}

$viewid = $view->get('id');
$restrictedview = !can_view_view($viewid);
if (!$restrictedview) {
    $viewcontent = $view->build_columns();
}

$javascript = array('paginator', 'lib/pieforms/static/core/pieforms.js', 'artefact/resume/resumeshowhide.js');
$blocktype_js = $view->get_all_blocktype_javascript();
$javascript = array_merge($javascript, $blocktype_js['jsfiles']);
$inlinejs = "addLoadEvent( function() {\n" . join("\n", $blocktype_js['initjs']) . "\n});";

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
$smarty->assign('restrictedview', $restrictedview);

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

    // Get all groups where either:
    // - the logged in user is an admin, or
    // - the logged in user has a role which is allowed to assess submitted views, or
    // - the logged in user is a member & is allowed to invite friends (when the displayed user is a friend)
    $groups = array();
    foreach (group_get_user_groups() as $g) {
        if ($g->role == 'admin' || $g->see_submitted_views || ($is_friend && $g->invitefriends)) {
            $groups[] = $g;
        }
    }
    if ($groups) {
        $invitelist     = array(); // List of groups the displayed user can be invited to join
        $controlledlist = array(); // List of groups the displayed user can be directly added to

        foreach ($groups as $group) {
            if (array_key_exists($group->id, $allusergroups)) {
                if ($allusergroups[$group->id]->type == 'invite') {
                    $invitedlist[$group->id] = $group->name;
                }
                else if ($allusergroups[$group->id]->type == 'request') {
                    $requestedlist[$group->id] = $group->name;
                    $controlledlist[$group->id] = $group->name;
                    continue;
                }
                else {
                    continue; // Already a member
                }
            }
            $canadd = $group->role == 'admin' || $group->see_submitted_views;
            if ($canadd && $group->jointype == 'controlled') {
                $controlledlist[$group->id] = $group->name;
            }
            if (!isset($invitedlist[$group->id])) {
                $invitelist[$group->id] = $group->name;
            }
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
        $smarty->assign('message', $record->message);
        $smarty->assign('acceptform', acceptfriend_form($userid));
    }
    else {
        $relationship = 'none';
        $friendscontrol = get_account_preference($userid, 'friendscontrol');
        if ($friendscontrol == 'auto') {
            $smarty->assign('newfriendform', addfriend_form($userid));
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

$smarty->assign('INLINEJAVASCRIPT', $inlinejs);

$smarty->assign('institutions', get_institution_string_for_user($userid));
$smarty->assign('canmessage', $loggedinid != $userid && can_send_message($loggedinid, $userid));
$smarty->assign('USERID', $userid);
$smarty->assign('viewtitle', get_string('usersprofile', 'mahara', display_name($user, null, true)));
$smarty->assign('viewtype', 'profile');

$smarty->assign('user', $user);
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

if (!$restrictedview) {
    $smarty->assign('viewcontent', $viewcontent);
}

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

    $group = get_record('group', 'id', $values['group']);
    $ctitle = $group->name;
    $adduser = get_record('usr', 'id', $userid);

    try {
        group_add_user($values['group'], $userid, 'member');
        $lang = get_user_language($userid);
        require_once(get_config('libroot') . 'activity.php');
        activity_occurred('maharamessage', array(
            'users'   => array($userid),
            'subject' => get_string_from_language($lang, 'addedtogroupsubject', 'group'),
            'message' => get_string_from_language($lang, 'addedtogroupmessage', 'group', display_name($USER, $adduser), $ctitle),
            'url'     => group_homepage_url($group, false),
            'urltext' => $ctitle,
        ));
        $SESSION->add_ok_msg(get_string('useradded', 'group'));
    }
    catch (SQLException $e) {
        $SESSION->add_error_msg(get_string('adduserfailed', 'group'));
    }
    redirect(profile_url($adduser));
}
