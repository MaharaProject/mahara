<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// NOTE: This script is VERY SIMILAR to the adminusers.php script, a bug fixed
// here might need to be fixed there too.
define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('institutionmembers', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'institutionusers');
define('MENUITEM', 'manageinstitutions/institutionusers');
require_once('institution.php');
$institutionelement = get_institution_selector(false);

if (empty($institutionelement)) {
    $smarty = smarty();
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

$institution = param_alphanum('institution', false);
if (!$institution || !$USER->can_edit_institution($institution)) {
    $institution = empty($institutionelement['value']) ? $institutionelement['defaultvalue'] : $institutionelement['value'];
}
else if (!empty($institution)) {
    $institutionelement['defaultvalue'] = $institution;
}

// Show either requesters, members, or nonmembers on the left hand side
$usertype = param_alpha('usertype', 'requesters');

$usertypeselectorelements = array(
            'usertype' => array(
                'type' => 'select',
                'title' => get_string('userstodisplay', 'admin'),
                'options' => array(
                    'requesters' => get_string('institutionusersrequesters', 'admin'),
                    'nonmembers' => get_string('institutionusersnonmembers', 'admin'),
                    'lastinstitution' => get_string('institutionuserslastinstitution', 'admin'),
                    'members' => get_string('institutionusersmembers', 'admin'),
                    'invited' => get_string('institutionusersinvited', 'admin'),
                ),
                'defaultvalue' => $usertype,
            ),
);

if ($usertype == 'lastinstitution') {
    // Change intitution dropdown to show possible last insitutions
    $lastinstitution = param_alphanum('lastinstitution', false);
    $usertypeselectorelements['lastinstitution'] = get_institution_selector(false, true);
    $usertypeselectorelements['lastinstitution']['title'] = get_string('lastinstitution', 'admin');
    if ($lastinstitution) {
        $usertypeselectorelements['lastinstitution']['defaultvalue'] = $lastinstitution;
    }
    else {
        $lastinstitution = $usertypeselectorelements['lastinstitution']['defaultvalue'];
    }
}

$usertypeselector = pieform(array(
    'name' => 'usertypeselect',
    'checkdirtychange' => false,
    'elements' => $usertypeselectorelements,
));

if ($usertype == 'requesters') {
    // LHS shows users who have requested membership, RHS shows users to be added
    $userlistelement = array(
        'title' => get_string('addnewmembers', 'admin'),
        'lefttitle' => get_string('usersrequested', 'admin'),
        'righttitle' => get_string('userstoaddorreject', 'admin'),
        'leftarrowlabel' => get_string('moveusersfromadd', 'admin'),
        'rightarrowlabel' => get_string('moveuserstoadd', 'admin'),
        'searchparams' => array('requested' => 1),
    );
    $submittext = get_string('addmembers', 'admin');
} else if ($usertype == 'members') {
    // LHS shows institution members, RHS shows users to be removed
    $userlistelement = array(
        'title' => get_string('removeusersfrominstitution', 'admin'),
        'lefttitle' => get_string('currentmembers', 'admin'),
        'righttitle' => get_string('userstoberemoved', 'admin'),
        'leftarrowlabel' => get_string('movemembersfromremove', 'admin'),
        'rightarrowlabel' => get_string('movememberstoremove', 'admin'),
        'searchparams' => array('member' => 1),
    );
    $submittext = get_string('removeusers', 'admin');
}
else if ($usertype == 'lastinstitution') {
    // LHS shows Users who have left institution "BLAH"
    // RHS shows users to be invited
    $lastinstitutionobj = new Institution($lastinstitution);
    $userlistelement = array(
        'title' => get_string('inviteuserstojoin', 'admin'),
        'lefttitle' => get_string('userswhohaveleft', 'admin', $lastinstitutionobj->displayname),
        'righttitle' => get_string('userstobeinvited', 'admin'),
        'leftarrowlabel' => get_string('moveusersfrominviteinstitution', 'admin', $lastinstitutionobj->displayname),
        'rightarrowlabel' => get_string('moveuserstoinviteinstitution', 'admin', $lastinstitutionobj->displayname),
        'searchparams' => array('member' => 0, 'invitedby' => 0, 'requested' => 0, 'lastinstitution' => $lastinstitution),
    );
    $submittext = get_string('inviteusers', 'admin');
}
else if ($usertype == 'nonmembers') {
    // Behaviour depends on whether we allow users to have > 1 institution
    // LHS either shows all nonmembers or just users with no institution
    // RHS shows users to be invited
    $userlistelement = array(
        'title' => get_string('inviteuserstojoin', 'admin'),
        'lefttitle' => get_string('Non-members', 'admin'),
        'righttitle' => get_string('userstobeinvited', 'admin'),
        'leftarrowlabel' => get_string('moveusersfrominvite', 'admin'),
        'rightarrowlabel' => get_string('moveuserstoinvite', 'admin'),
        'searchparams' => array('member' => 0, 'invitedby' => 0, 'requested' => 0)
    );
    $submittext = get_string('inviteusers', 'admin');
}
else if ($usertype == 'invited') {
    // Allow invitations to be revoked
    $userlistelement = array(
        'title' => get_string('revokeinvitations', 'admin'),
        'lefttitle' => get_string('invitedusers', 'admin'),
        'righttitle' => get_string('userstobeuninvited', 'admin'),
        'leftarrowlabel' => get_string('moveusersfromuninvited', 'admin'),
        'rightarrowlabel' => get_string('moveuserstouninvited', 'admin'),
        'searchparams' => array('member' => 0, 'invitedby' => 1),
    );
    $submittext = get_string('revokeinvitations', 'admin');
}

$userlistelement['type'] = 'userlist';
$userlistelement['searchscript'] = 'admin/users/userinstitutionsearch.json.php';
$userlistelement['defaultvalue'] = array();
$userlistelement['searchparams']['limit'] = 100;
$userlistelement['searchparams']['query'] = '';
$userlistelement['searchparams']['institution'] = $institution;

$userlistform = array(
    'name' => 'institutionusers',
    'checkdirtychange' => false,
    'elements' => array(
        'users' => $userlistelement,
        'institution' => array(
            'type' => 'hidden',
            'value' => $institution
        ),
        'usertype' => array(
            'type' => 'hidden',
            'value' => $usertype,
            'rules' => array('regex' => '/^[a-z]+$/')
        ),
        'submit' => array(
            'class' => 'btn-primary',
            'type' => 'submit',
            'value' => $submittext
        )
    )
);

if ($usertype == 'lastinstitution') {
    $userlistform['elements']['lastinstitution'] = array(
        'type' => 'hidden',
        'value' => $lastinstitution,
        'rules' => array('regex' => '/^[a-zA-Z0-9]+$/'),
    );
}

if ($usertype == 'requesters') {
    $userlistform['elements']['reject'] = array(
        'type' => 'submit',
        'class' => 'btn-default',
        'value' => get_string('declinerequests', 'admin'),
    );
}
if (($usertype == 'nonmembers' || $usertype == 'lastinstitution') && $USER->get('admin')) {
    $userlistform['elements']['add'] = array(
        'type' => 'submit',
         'class' => 'btn-default',
        'value' => get_string('addmembers', 'admin'),
    );
}

$userlistform = pieform($userlistform);

function institutionusers_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    $inst = $values['institution'];
    $url = '/admin/users/institutionusers.php?usertype=' . $values['usertype'] . (isset($values['lastinstitution']) ? '&lastinstitution=' . $values['lastinstitution'] : '') . '&institution=' . $inst;
    if (empty($inst) || !$USER->can_edit_institution($inst)) {
        $SESSION->add_error_msg(get_string('notadminforinstitution', 'admin'));
        redirect($url);
    }

    $dataerror = false;
    if (!in_array($values['usertype'], array('requesters', 'members', 'lastinstitution', 'nonmembers', 'invited'))
        || !is_array($values['users'])) {
        $dataerror = true;
    } else {
        foreach ($values['users'] as $id) {
            if (!is_numeric($id)) {
                $dataerror = true;
                break;
            }
        }
    }
    if ($dataerror) {
        $SESSION->add_error_msg(get_string('errorupdatinginstitutionusers', 'admin'));
        redirect($url);
    } else if (empty($values['users'])) {
        $SESSION->add_ok_msg(get_string('nousersupdated', 'admin'));
        redirect($url);
    }

    if ($values['usertype'] == 'members') {
        $action = 'removeMembers';
    } else if ($values['usertype'] == 'requesters') {
        $action = !empty($values['reject']) ? 'declineRequestFromUser' : 'addUserAsMember';
    }
    else if ($values['usertype'] == 'nonmembers') {
        $action = (!empty($values['add']) && $USER->get('admin')) ? 'addUserAsMember' : 'inviteUser';
    }
    else if ($values['usertype'] == 'lastinstitution') {
        $action = (!empty($values['add']) && $USER->get('admin')) ? 'addUserAsMember' : 'inviteUser';
    }
    else {
        $action = 'uninvite_users';
    }


    $institution = new Institution($values['institution']);
    $maxusers = $institution->maxuseraccounts;
    if (!empty($maxusers)) {
        $members = $institution->countMembers();
        if ($action == 'addUserAsMember' && $members + count($values['users']) > $maxusers) {
            $SESSION->add_error_msg(get_string('institutionuserserrortoomanyusers', 'admin'));
            redirect($url);
        }
        if ($action == 'inviteUser'
            && $members + $institution->countInvites() + count($values['users']) > $maxusers) {
            $SESSION->add_error_msg(get_string('institutionuserserrortoomanyinvites', 'admin'));
            redirect($url);
        }
    }

    if ($action == 'removeMembers') {
        $institution->removeMembers($values['users']);
    }
    else if ($action == 'addUserAsMember') {
        $institution->add_members($values['users']);
    }
    else if ($action == 'inviteUser') {
        $institution->invite_users($values['users']);
    }
    else if ($action == 'declineRequestFromUser') {
        $institution->decline_requests($values['users']);
    }
    else if ($action == 'uninvite_users') {
        $institution->uninvite_users($values['users']);
    }

    $SESSION->add_ok_msg(get_string('institutionusersupdated_'.$action, 'admin'));
    if (!$USER->get('admin') && !$USER->is_institutional_admin()) {
        redirect(get_config('wwwroot'));
    }
    redirect($url);
}

$wwwroot = get_config('wwwroot');
$js = <<< EOF
function reloadUsers() {
    var last = '';
    if ($('usertypeselect_lastinstitution')) {
        last = '&lastinstitution=' + $('usertypeselect_lastinstitution').value;
    }
    var inst = '';
    if ($('institutionselect_institution')) {
        inst = '&institution=' + $('institutionselect_institution').value;
    }
    window.location.href = '{$wwwroot}admin/users/institutionusers.php?usertype='+$('usertypeselect_usertype').value+last+inst;
}
addLoadEvent(function() {
    connect($('usertypeselect_usertype'), 'onchange', reloadUsers);
    if ($('usertypeselect_lastinstitution')) {
        connect($('usertypeselect_lastinstitution'), 'onchange', reloadUsers);
    }
    if ($('institutionselect_institution')) {
        connect($('institutionselect_institution'), 'onchange', reloadUsers);
    }
    formchangemanager.add('institutionusers');
    // Unbind the handler for standard pieform input
    // The JS code for updating the userlist will also update the formchangechecker state
    formchangemanager.unbindForm('institutionusers');
});
EOF;

$institutionselector = pieform(array(
    'name' => 'institutionselect',
    'class' => 'form-inline',
    'elements' => array(
        'institution' => $institutionelement,
    )
));

$smarty = smarty();
setpageicon($smarty, 'icon-university');
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('usertypeselector', $usertypeselector);
$smarty->assign('instructions', get_string('institutionusersinstructions' . $usertype . '1', 'admin', $userlistelement['lefttitle'], $userlistelement['righttitle']));
$smarty->assign('institutionusersform', $userlistform);
$smarty->assign('institutionselector', $institutionselector);
$smarty->display('admin/users/institutionusers.tpl');
