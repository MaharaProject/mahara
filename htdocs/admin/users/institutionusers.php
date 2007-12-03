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
 * @subpackage admin
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

// NOTE: This script is VERY SIMILAR to the adminusers.php script, a bug fixed
// here might need to be fixed there too.
define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'configusers/institutionusers');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('adminusers', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'institutionusers');
require_once('pieforms/pieform.php');

require_once('institution.php');
$institutionelement = get_institution_selector();

global $USER;
$institution = param_alphanum('institution', false);
if (!$institution || !$USER->is_institutional_admin($institution)) {
    $institution = empty($institutionelement['value']) ? $institutionelement['defaultvalue'] : $institutionelement['value'];
}

// Show either requesters, members, or nonmembers on the left hand side
$usertype = param_alpha('usertype', 'requesters');

$usertypeselector = pieform(array(
    'name' => 'usertypeselect',
    'elements' => array(
        'usertype' => array(
            'type' => 'select',
            'title' => get_string('userstodisplay', 'admin'),
            'options' => array(
                'requesters' => get_string('institutionusersrequesters', 'admin'),
                'nonmembers' => get_string('institutionusersnonmembers', 'admin'),
                'members' => get_string('institutionusersmembers', 'admin'),
             ),
            'defaultvalue' => $usertype
        ),
    )
));

if ($usertype == 'requesters') {
    // LHS shows users who have requested membership, RHS shows users to be added
    $userlistelement = array(
        'title' => get_string('addnewmembers', 'admin'),
        'description' => get_string('addnewmembersdescription', 'admin'),
        'lefttitle' => get_string('usersrequested', 'admin'),
        'righttitle' => get_string('userstobeadded', 'admin'),
        'searchparams' => array('requested' => 1),
    );
    $submittext = get_string('addmembers', 'admin');
} else if ($usertype == 'members') {
    // LHS shows institution members, RHS shows users to be removed
    $userlistelement = array(
        'title' => get_string('removeusersfrominstitution', 'admin'),
        'description' => get_string('removeusersdescription', 'admin'),
        'lefttitle' => get_string('currentmembers', 'admin'),
        'righttitle' => get_string('userstoberemoved', 'admin'),
        'searchparams' => array('member' => 1),
    );
    $submittext = get_string('removeusers', 'admin');
} else { // $usertype == nonmembers
    // Behaviour depends on whether we allow users to have > 1 institution
    // LHS either shows all nonmembers or just users with no institution
    // RHS shows users to be invited
    $userlistelement = array(
        'title' => get_string('inviteuserstojoin', 'admin'),
        'description' => get_string('inviteusersdescription', 'admin'),
        'lefttitle' => get_string('Non-members', 'admin'),
        'righttitle' => get_string('userstobeinvited', 'admin'),
        'searchparams' => array('member' => 0, 'invited' => 0, 'requested' => 0)
    );
    $submittext = get_string('inviteusers', 'admin');
}

$userlistelement['type'] = 'userlist';
$userlistelement['filter'] = false;
$userlistelement['searchscript'] = 'admin/users/userinstitutionsearch.json.php';
$userlistelement['defaultvalue'] = array();
$userlistelement['searchparams']['limit'] = 100;
$userlistelement['searchparams']['query'] = '';
$userlistelement['searchparams']['institution'] = $institution;

$userlistform = pieform(array(
    'name' => 'institutionusers',
    'elements' => array(
        'users' => $userlistelement,
        'institution' => $institutionelement,
        'usertype' => array(
            'type' => 'hidden',
            'value' => $usertype,
            'rules' => array('regex' => '/^[a-z]+$/')
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => $submittext
        )
    )
));

function institutionusers_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    $inst = $values['institution'];
    if (empty($inst) || !$USER->is_institutional_admin($inst)) {
        $SESSION->add_error_msg(get_string('notadminforinstitution', 'admin'));
        redirect('/admin/users/institutionusers.php?usertype='.$values['usertype']);
    }

    $update = 'update_' . $values['usertype'];
    if ($update($values['users'], $values['institution'])) {
        $SESSION->add_ok_msg(get_string('usersupdated', 'admin'));
    } else {
        $SESSION->add_error_msg(get_string('dberrorupdatingusers', 'admin'));
    }
    redirect('/admin/users/institutionusers.php?usertype='.$values['usertype']);
}

function update_nonmembers($userids, $institution) {
    db_begin();
    foreach ($userids as $id) {
        invite_user_to_institution($id, $institution);
    }
    db_commit();
    return true;
}

function update_requesters($userids, $institution) {
    db_begin();
    foreach ($userids as $id) {
        add_user_to_institution($id, $institution);
    }
    db_commit();
    return true;
}

function update_members($userids, $institution) {
    delete_records_select('usr_institution', 'usr IN (' . join(',', $userids) . ') AND institution = ' . db_quote($institution));
    return true;
}

$wwwroot = get_config('wwwroot');
$js = <<< EOF
addLoadEvent(function() {
    connect($('usertypeselect_usertype'), 'onchange', function () {
        window.location.href = '{$wwwroot}admin/users/institutionusers.php?usertype='+$('usertypeselect_usertype').value;
    });
});
EOF;

$smarty = smarty();
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('usertypeselector', $usertypeselector);
$smarty->assign('institutionusersform', $userlistform);
$smarty->display('admin/users/institutionusers.tpl');

?>
