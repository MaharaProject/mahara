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
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
require(dirname(dirname(__FILE__)).'/init.php');
require_once('group.php');
require_once('pieforms/pieform.php');

$userid = param_integer('id','');
$loggedinid = $USER->get('id');

// Get the user's details

$profile = array();
$userfields = array();
if (!$user = get_record('usr', 'id', $userid, 'deleted', 0)) {
    throw new UserNotFoundException("User with id $userid not found");
}
$is_friend = is_friend($userid, $loggedinid);

$name = display_name($user);
define('TITLE', $name);

// If the logged in user is on staff, get full name, institution, id number, email address
if ($USER->is_staff_for_user($user)) {
    $userfields['fullname']     = $user->firstname . ' ' . $user->lastname;
    $institutions = get_column_sql('
        SELECT i.displayname
        FROM {institution} i, {usr_institution} ui 
        WHERE ui.usr = ? AND ui.institution = i.name', array($user->id));
    if (!empty($institutions)) {
        $userfields['institution'] = join(', ', $institutions);
    } else {
        $userfields['institution'] = get_field('institution', 'displayname', 'name', 'mahara');
    }
    $userfields['studentid']    = get_profile_field($user->id, 'studentid');
    $userfields['principalemailaddress'] = $user->email;
}

// Get public profile fields:
safe_require('artefact', 'internal');
if ($USER->is_admin_for_user($user)) {
    $publicfields = call_static_method(generate_artefact_class_name('profile'),'get_all_fields');
}
else {
    $publicfields = call_static_method(generate_artefact_class_name('profile'),'get_public_fields');
}
foreach (array_keys($publicfields) as $field) {
    $classname = generate_artefact_class_name($field);
    if ($field == 'email') {  // There may be multiple email records
        if ($emails = get_records_array('artefact_internal_profile_email', 'owner', $userid)) {
            foreach ($emails as $email) {
                $fieldname = $email->principal ? 'principalemailaddress' : 'emailaddress';
                $userfields[$fieldname] = $email->email;
            }
        }
    }
    else {
        $value = get_profile_field($userid, $field);
        if (!empty($value)) {
            $userfields[$field] = $value;
        }
    }
}
if (isset($userfields['country'])) {
    $userfields['country'] = get_string('country.' . $userfields['country']);
}

if (isset($userfields['firstname'])) {
    unset($userfields['firstname']);
}

if (isset($userfields['lastname'])) {
    unset($userfields['lastname']);
}

if (isset($userfields['introduction'])) {
    $introduction = $userfields['introduction'];
    unset($userfields['introduction']);
}

// Get viewable views
$views = array();
if ($allviews = get_records_array('view', 'owner', $userid)) {
    foreach ($allviews as $view) {
        if (can_view_view($view->id)) {
            $views[$view->id] = $view;
            $view->artefacts = array();
            $view->description = format_text($view->description);
        }
    }
}

if ($views) {
    $viewidlist = implode(', ', array_map(create_function('$a', 'return $a->id;'), $views));
    $artefacts = get_records_sql_array('SELECT va.view, va.artefact, a.title, a.artefacttype, t.plugin
        FROM {view_artefact} va
        INNER JOIN {artefact} a ON va.artefact = a.id
        INNER JOIN {artefact_installed_type} t ON a.artefacttype = t.name
        WHERE va.view IN (' . $viewidlist . ')
        GROUP BY 1, 2, 3, 4, 5
        ORDER BY a.title, va.artefact', '');
    if ($artefacts) {
        foreach ($artefacts as $artefactrec) {
            safe_require('artefact', $artefactrec->plugin);
            // Perhaps I shouldn't have to construct the entire
            // artefact object to render the name properly.
            $classname = generate_artefact_class_name($artefactrec->artefacttype);
            $artefactobj = new $classname(0, array('title' => $artefactrec->title));
            $artefactobj->set('dirty', false);
            if (!$artefactobj->in_view_list()) {
                continue;
            }
            $artname = $artefactobj->display_title(30);
            if (strlen($artname)) {
                $views[$artefactrec->view]->artefacts[] = array('id'    => $artefactrec->artefact,
                                                                'title' => $artname);
            }
        }
    }
}

// Group stuff
if (!$userassocgroups = get_associated_groups($userid, false)) {
    $userassocgroups = array();
}

foreach ($userassocgroups as $group) {
    $group->description = format_text($group->description);
}

if (is_postgres()) {
    $random = 'RANDOM()';
}
else if (is_mysql()) {
    $random = 'RAND()';
}
$records = get_records_select_array('usr_friend', 'usr1 = ? OR usr2 = ?', array($userid, $userid), $random, 'usr1, usr2', 0, 16);
$numberoffriends = count_records_select('usr_friend', 'usr1 = ? OR usr2 = ?', array($userid, $userid));
if ($numberoffriends > 16) {
    $friendsmessage = get_string('numberoffriends', 'group', $records ? count($records) : 0, $numberoffriends);
}
else {
    $friendsmessage = get_string('Friends', 'group');
}
// get the friends into a 4x4 array
$friends = array();
for ($i = 0; $i < 4; $i++) {
    $friends[$i] = array();
    for($j = 4 * $i; $j < ($i + 1 ) * 4; $j++) {
        if (isset($records[$j])) {
            if ($records[$j]->usr1 == $userid) {
                $friends[$i][] = $records[$j]->usr2;
            }
            else {
                $friends[$i][] = $records[$j]->usr1;
            }
        }
    }
}

$smarty = smarty();

if ($loggedinid != $userid) {
    // Get the logged in user's "invite only" groups
    if ($groups = get_owned_groups($loggedinid, 'invite')) {
        $invitelist = array();
        foreach ($groups as $group) {
            if (array_key_exists($group->id, $userassocgroups)) {
                continue;
            }
            $invitelist[$group->id] = $group->name;
        }
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

    // Get the "controlled membership" groups in which the logged in user is a tutor
    if ($groups = get_tutor_groups($loggedinid, 'controlled')) {
        $controlledlist = array();
        foreach ($groups as $group) {
            if (array_key_exists($group->id, $userassocgroups)) {
                continue;
            }
            $controlledlist[$group->id] = $group->name;
        }
        if (count($controlledlist) > 0) {
            $default = array_keys($controlledlist);
            $default = $default[0];
            $addform = pieform(array(
                'name'                => 'addmember',
                'successcallback'     => 'addmember_submit',
                'renderer'            => 'div',
                'elements'            => array(
                    'group' => array(
                        'type'    => 'select',
                        'title'   => get_string('addusertogroup', 'group'),
                        'collapseifoneoption' => false,
                        'options' => $controlledlist,
                        'defaultvalue' => $default,
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
        $smarty->assign('reason', $record->reason);
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

if (isset($introduction)) {
    $smarty->assign('introduction', $introduction);
}
$smarty->assign('canmessage', can_send_message($userid, $loggedinid));
$smarty->assign('NAME',$name);
$smarty->assign('USERID', $userid);
$smarty->assign('USERFIELDS',$userfields);
$smarty->assign('USERGROUPS',$userassocgroups);
$smarty->assign('VIEWS',$views);
$smarty->assign('friends', $friends);
$smarty->assign('friendsmessage', $friendsmessage);
$smarty->display('user/view.tpl');

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
    $data->tutor  = 0;
    $ctitle = get_field('group', 'name', 'id', $data->group);
    $adduser = get_record('usr', 'id', $data->member);

    try {
        insert_record('group_member', $data);
        $lang = get_user_language($userid);
        activity_occurred('maharamessage', 
            array('users'   => array($userid),
                  'subject' => get_string_from_language($lang, 'addedtogroupsubject', 'group'),
                  'message' => get_string_from_language($lang, 'addedtogroupmessage', 'group', display_name($USER, $adduser), $ctitle),
                  'url'     => get_config('wwwroot') . 'group/view.php?id=' . $values['group']));
        $SESSION->add_ok_msg(get_string('useradded', 'group'));
    }
    catch (SQLException $e) {
        $SESSION->ad_ok_msg(get_string('adduserfailed', 'group'));
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

?>
