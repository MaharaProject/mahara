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

$userid = param_integer('id','');
$loggedinid = $USER->get('id');

// Get the user's details

$profile = array();
$userfields = array();
if (!$user = get_record('usr', 'id', $userid)) {
    $name = get_string('usernotfound');
}
else {
    $name = display_name($user);

    // If the logged in user is on staff, get full name, institution, id number, email address
    if ($USER->get('staff')) {
        $userfields['fullname'] = $user->firstname . ' ' . $user->lastname;
        $userfields['institution'] = $user->institution;
        $userfields['studentid'] = $user->studentid;
        $userfields['emailaddress'] = $user->email;
    }

    // Get public profile fields:
    safe_require('artefact', 'internal');
    $publicfields = call_static_method(generate_artefact_class_name('profile'),'get_public_fields');
    foreach (array_keys($publicfields) as $field) {
        $classname = generate_artefact_class_name($field);
        if ($field == 'email') {  // There may be multiple email records
            if ($emails = get_records_array('artefact_internal_profile_email', 'owner', $userid)) {
                foreach ($emails as $email) {
                    $fieldname = $email->principal ? 'principalemailaddress' : 'emailaddress';
                    $profile[$fieldname] = $email->email;
                }
            }
        }
        else {
            $c = new $classname(0, array('owner' => $userid)); // email is different
            if ($value = $c->render(FORMAT_ARTEFACT_LISTSELF, array('link' => true))) {
                $profile[$field] = $value;
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
}

$smarty = smarty();

// Get the logged in user's "invite only" communities
// @todo: check if user is already a community member.

if ($communities = @get_records_select_array('community',
                                             'owner = ' . $loggedinid . "AND jointype = 'invite'",
                                             null, 'name', 'id,name')) {
    $invitelist = array();
    foreach ($communities as $community) {
        $invitelist[$community->id] = $community->name;
    }
    require_once('pieforms/pieform.php');
    $inviteform = pieform(array(
        'name'                => 'invite',
        'ajaxpost'            => true,
        'elements'            => array(
            'community' => array(
                'type'    => 'select',
                'title'   => get_string('inviteusertojoincommunity'),
                'collapseifoneoption' => false,
                'options' => $invitelist
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('sendinvitation'),
            ),
        ),
    ));
    $smarty->assign('INVITEFORM',$inviteform);
}

$prefix = get_config('dbprefix');

// Get the "controlled membership" communities in which the logged in user is a tutor
// @todo: check if user is already a community member.

if ($communities = @get_records_sql_array('SELECT c.id, c.name
        FROM ' . $prefix . 'community c
        JOIN ' . $prefix . 'community_member cm ON c.id = cm.community
        WHERE cm.member = ' . $loggedinid . " AND cm.tutor = 1 AND c.jointype = 'controlled'",'')) {
    $controlledlist = array();
    foreach ($communities as $community) {
        $controlledlist[$community->id] = $community->name;
    }
    require_once('pieforms/pieform.php');
    $addform = pieform(array(
        'name'                => 'addmember',
        'ajaxpost'            => true,
        'elements'            => array(
            'community' => array(
                'type'    => 'select',
                'title'   => get_string('addusertocommunity'),
                'collapseifoneoption' => false,
                'options' => $controlledlist
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
    $smarty->assign('ADDFORM',$addform);
}

// Send an invitation to the user to join a community
function invite_submit($values) {
}

// Add the user as a member of a community
function addmember_submit($values) {
    $data = new StdClass;
    $data->community = $values['community'];
    $data->member    = $values['id'];
    $data->ctime     = db_format_timestamp(time());
    $data->tutor     = 0;
    try {
        insert_record('community_member', $data, 'community,member');
    }
    catch (SQLException $e) {
        json_reply('local', get_string('adduserfailed'));
    }
    json_reply(false, get_string('useradded'));
}


$smarty->assign('searchform',searchform());
$smarty->assign('NAME',$name);
$smarty->assign('USERFIELDS',$userfields);
$smarty->assign('PROFILE',$profile);
$smarty->assign('VIEWS',$views);
$smarty->display('user/view.tpl');

?>
