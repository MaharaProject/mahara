<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
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
require_once('community.php');
require_once('pieforms/pieform.php');

$userid = param_integer('id','');
$loggedinid = $USER->get('id');
$prefix = get_config('dbprefix');
$inlinejs = <<<EOF
    function usercontrol_success(formname, data) {
        
        if (formname != 'friend') {
            var dd = $(formname).elements['community'];
            if (dd.nodeName == 'INPUT') {
                $(formname).style.display = 'none';
                return true;
            }
            if (dd.nodeName == 'SELECT') {
                if (dd.options.length == 1) {
                    $(formname).style.display = 'none';
                    return true;
                }
                else {
                    removeElement(dd.options[dd.selectedIndex]);
                    if (dd.length > 0) {
                        return true;
                    }
                }
            }
        }
        
        swapDOM(formname, P(null, data.message));
        return true;
    }
EOF;

// Get the user's details

$profile = array();
$userfields = array();
if (!$user = get_record('usr', 'id', $userid)) {
    throw new UserNotFoundException("User with id $userid not found");
}

$name = display_name($user);
define('TITLE', $name);

// If the logged in user is on staff, get full name, institution, id number, email address
if ($USER->get('staff')) {
    $userfields['fullname']     = $user->firstname . ' ' . $user->lastname;
    $userfields['institution']  = $user->institution;
    $userfields['studentid']    = get_profile_field($user->id, 'studentid');
    $userfields['principalemailaddress'] = $user->email;
}

// Get public profile fields:
safe_require('artefact', 'internal');
if ($USER->get('admin')) {
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

// Get viewable views
$views = array();
if ($allviews = get_records_array('view', 'owner', $userid)) {
    foreach ($allviews as $view) {
        if (can_view_view($view->id)) {
            $views[$view->id] = $view->title;
        }
    }
}

// community stuff
if (!$userassoccommunities = get_associated_communities($userid)) {
    $userassoccommunities = array();
}

$smarty = smarty();

if ($loggedinid != $userid) {
    // Get the logged in user's "invite only" communities
    if ($communities = get_owned_communities($loggedinid, 'invite')) {
        $invitelist = array();
        foreach ($communities as $community) {
            if (array_key_exists($community->id, $userassoccommunities)) {
                continue;
            }
            $invitelist[$community->id] = $community->name;
        }
        if (count($invitelist) > 0) {
            $default = array_keys($invitelist);
            $default = $default[0];
            $inviteform = pieform(array(
                'name'              => 'invite',
                'jsform'            => true,
                'jssuccesscallback' => 'usercontrol_success',
                'elements'          => array(
                    'community' => array(
                        'type'                => 'select',
                        'title'               => get_string('inviteusertojoincommunity'),
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
                        'value' => get_string('sendinvitation'),
                    ),
                ),
            ));
            $smarty->assign('INVITEFORM',$inviteform);
        }
    }

    // Get the "controlled membership" communities in which the logged in user is a tutor
    if ($communities = get_tutor_communities($loggedinid, 'controlled')) {
        $controlledlist = array();
        foreach ($communities as $community) {
            if (array_key_exists($community->id, $userassoccommunities)) {
                continue;
            }
            $controlledlist[$community->id] = $community->name;
        }
        if (count($controlledlist) > 0) {
            $default = array_keys($controlledlist);
            $default = $default[0];
            $addform = pieform(array(
                'name'                => 'addmember',
                'jsform'              => true,
                'jssuccesscallback'   => 'add_success',
                'elements'            => array(
                    'community' => array(
                        'type'    => 'select',
                        'title'   => get_string('addusertocommunity'),
                        'collapseifoneoption' => false,
                        'options' => $controlledlist,
                        'defaultvalue' => $default,
                    ),
                    'id' => array(
                        'type'  => 'hidden',
                        'value' => $userid,
                    ),
                    'submit' => array(
                        'type'  => 'submit',
                        'value' => get_string('add'),
                    ),
                ),
           ));
            $inlinejs .= <<<EOF
    
    function add_success(data) {
        usercontrol_success('addmember');
    }
EOF;
            $smarty->assign('ADDFORM',$addform);
        } 
    }

    // adding this user to the currently logged in user's friends list
    // or removing or approving or rejecting or whatever else we can do.
    $friendform = array(
        'name'     => 'friend',
        'jsform'   => true,
        'elements' => array(),
        'jssuccesscallback' => 'usercontrol_success',
        );
    $friendsubmit = '';
    $friendtype = '';
    $friendformmessage = '';
    // already a friend ... we can remove.
    if (is_friend($userid, $loggedinid)) {
        $friendtype = 'remove';
        $friendsubmit = get_string('removefromfriendslist');
    } 
    // if there's a friends request already
    else if ($request = get_friend_request($userid, $loggedinid)) {
        if ($request->owner == $userid) {
            $friendformmessage = get_string('friendshipalreadyrequested', 'mahara', $name);
        }
        else {
            $friendform['elements']['requested'] = array(
                'type' => 'html', 
                'value' => get_string('friendshipalreadyrequestedowner', 'mahara', $name)
            );
            $friendform['elements']['rejectreason'] = array(
                'type'  => 'textarea',
                'title' => get_string('rejectfriendshipreason'),
                'cols'  => 10,
                'rows'  => 4,                                
            );    
            $friendsubmit = get_string('accept');
            $friendform['elements']['rejectsubmit'] = array(
                'type'  => 'submit',
                'value' => get_string('reject'),
            );
            $friendtype = 'accept';
        }
    }
    // check the preference
    else {
        $friendscontrol = get_account_preference($userid, 'friendscontrol');
        if ($friendscontrol == 'nobody') {
            $friendtype = '';
            $friendformmessage = get_string('userdoesntwantfriends');
        } 
        else if ($friendscontrol == 'auth') {
            $friendform['elements']['reason'] = array(
                'type'  => 'textarea',
                'title' => get_string('requestfriendship'),
                'cols'  => 40,
                'rows'  => 4,
            );
            $friendsubmit = get_string('request');
            $friendtype = 'request';
        } else {
            $friendsubmit = get_string('addtofriendslist');
            $friendtype = 'add';
        }
    }
}
// if we have a form to display, do it
if (!empty($friendtype)) {
    $friendform['elements']['type'] = array(
        'type'  => 'hidden',
        'value' => $friendtype,
    );
    $friendform['elements']['id'] = array(
        'type'  => 'hidden',
        'value' => $userid,
    );
    $friendform['elements']['submit'] = array(
        'type'  => 'submit',
        'value' => $friendsubmit,
    );
    // friend submit function lives in lib/user.php
    $friendform = pieform($friendform);
} 
else {
    $friendform = '';
    if (!empty($friendformmessage)) {
        $friendform = $friendformmessage;
    }
}

$smarty->assign('FRIENDFORM', $friendform);
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('NAME',$name);
$smarty->assign('USERID', $userid);
$smarty->assign('USERFIELDS',$userfields);
if ($USER->get('admin')) {
    $smarty->assign('USERCOMMUNITIES',$userassoccommunities);
}
$smarty->assign('VIEWS',$views);
$smarty->display('user/view.tpl');

////////// Functions to process ajax callbacks //////////


// Send an invitation to the user to join a community
function invite_submit(Pieform $form, $values) {
    global $USER;
    
    $data = new StdClass;
    $data->community = $values['community'];
    $data->member    = $values['id'];
    $data->ctime     = db_format_timestamp(time());
    $data->tutor     = 0;
    $ctitle = get_field('community', 'name', 'id', $data->community);
    $adduser = get_record('usr', 'id', $data->member);
    try {
        insert_record('community_member_invite', $data);
        activity_occurred('maharamessage', 
            array('users'   => array($values['id']), 
                  'subject' => get_string('invitetocommunitysubject'),
                  'message' => get_string('invitetocommunitymessage', 'mahara', display_name($USER, $adduser), $ctitle),
                  'url'     => get_config('wwwroot') 
                  . 'contacts/communities/view.php?id=' . $values['community']));
    }
    catch (SQLException $e) {
        $form->json_reply(PIEFORM_ERR, get_string('inviteuserfailed'));
    }
    $form->json_reply(PIEFORM_OK, get_string('userinvited'));
}

// Add the user as a member of a community
function addmember_submit(Pieform $form, $values) {
    global $USER;

    $data = new StdClass;
    $data->community = $values['community'];
    $data->member    = $values['id'];
    $data->ctime     = db_format_timestamp(time());
    $data->tutor     = 0;
    $ctitle = get_field('community', 'name', 'id', $data->community);
    $adduser = get_record('usr', 'id', $data->member);

    try {
        insert_record('community_member', $data);
        activity_occurred('maharamessage', 
            array('users'   => array($values['id']), 
                  'subject' => get_string('addedtocommunitysubject'),
                  'message' => get_string('addedtocommunitymessage', 'mahara', display_name($USER, $adduser), $ctitle),
                  'url'     => get_config('wwwroot') 
                  . 'contacts/communities/view.php?id=' . $values['community']));
    }
    catch (SQLException $e) {
        $form->json_reply(PIEFORM_ERR, get_string('adduserfailed'));
    }
    $form->json_reply(PIEFORM_OK, get_string('useradded'));
}

// friend submit function lives in lib/user.php
?>
