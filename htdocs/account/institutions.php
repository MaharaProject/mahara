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
define('MENUITEM', 'settings/institutions');

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('institutionmembership'));
require_once('pieforms/pieform.php');

$institutions = get_records_assoc('institution', '', '', '', 'name,displayname,registerallowed');

// For all institutions the user is already a member of, create a
// button to leave the institution, unless the institution does not
// allow registration.
$member = $USER->get('institutions');
if (!empty($member)) {
    $elements = array();
    foreach ($member as $i) {
        if ($institutions[$i->institution]->registerallowed) {
            $elements[] = array(
                'type' => 'submit',
                'name' => '_leave_' . $i->institution,
                'confirm' => get_string('reallyleaveinstitution'),
                'title' => get_string('youareamemberof', 'mahara', $institutions[$i->institution]->displayname),
                'value' => get_string('leaveinstitution')
            );
        }
        else {
            $elements[] = array(
                'type' => 'html',
                'name' => '_noleave_' . $i->institution,
                'title' => get_string('youareamemberof', 'mahara', $institutions[$i->institution]->displayname),
                'value' => '',
            );
        }
        unset($institutions[$i->institution]);
    }
    $memberform = pieform(array(
        'name'        => 'leaveinstitution',
        'method'      => 'post',
        'plugintype'  => 'core',
        'pluginname'  => 'account',
        'elements'    => $elements
    ));
} else {
    $memberform = null;
}

function leaveinstitution_submit(Pieform $form, $values) {
    global $USER;
    foreach ($values as $k => $v) {
        if (preg_match('/^\_leave\_([a-z0-9]+)$/', $k, $m)) {
            $institution = $m[1];
            break;
        }
    }
    if (!empty($institution)) {
        $USER->leave_institution($institution);
    }
    redirect(get_config('wwwroot') . 'account/institutions.php');
}



// List all institutions the user has requested membership, with a
// cancel request button
$requested = get_column('usr_institution_request', 'institution', 
                        'usr', $USER->id, 'confirmedusr', 1);
if (!empty($requested)) {
    $elements = array();
    foreach ($requested as $i) {
        $elements[] = array(
            'type' => 'submit',
            'name' => '_cancelrequest_' . $i,
            'title' => get_string('youhaverequestedmembershipof', 'mahara', 
                                  $institutions[$i]->displayname),
            'value' => get_string('cancelrequest')
        );
        unset($institutions[$i]);
    }
    $requestedform = pieform(array(
        'name'        => 'cancelrequest',
        'method'      => 'post',
        'plugintype'  => 'core',
        'pluginname'  => 'account',
        'elements'    => $elements
    ));
} else {
    $requestedform = null;
}

function cancelrequest_submit(Pieform $form, $values) {
    global $USER;
    foreach ($values as $k => $v) {
        if (preg_match('/^\_cancelrequest\_([a-z0-9]+)$/', $k, $m)) {
            $institution = $m[1];
            break;
        }
    }
    if (!empty($institution)) {
        delete_records('usr_institution_request', 'usr', $USER->id, 'institution', $institution);
        handle_event('updateuser', $USER->id);
    }
    redirect(get_config('wwwroot') . 'account/institutions.php');
}



// List all institutions the user has been invited to join, with a
// confirm membership button
$invited = get_column('usr_institution_request', 'institution', 'usr', $USER->id, 'confirmedinstitution', 1);
if (!empty($invited)) {
    $elements = array();
    foreach ($invited as $i) {
        $elements[] = array(
            'type' => 'submit',
            'name' => '_confirminvite_' . $i,
            'title' => get_string('youhavebeeninvitedtojoin', 'mahara', 
                                  $institutions[$i]->displayname),
            'value' => get_string('joininstitution')
        );
        $elements[] = array(
            'type' => 'submit',
            'name' => '_declineinvite_' . $i,
            'value' => get_string('decline')
        );
        unset($institutions[$i]);
    }
    $invitedform = pieform(array(
        'name'        => 'confirminvite',
        'method'      => 'post',
        'plugintype'  => 'core',
        'pluginname'  => 'account',
        'elements'    => $elements
    ));
} else {
    $invitedform = null;
}

function confirminvite_submit(Pieform $form, $values) {
    global $USER;
    foreach ($values as $k => $v) {
        if (preg_match('/^\_confirminvite\_([a-z0-9]+)$/', $k, $m)) {
            $institution = $m[1];
            if (count_records('usr_institution_request', 'usr', $USER->id,
                              'institution', $institution, 'confirmedinstitution', 1)) {
                $USER->join_institution($institution);
                break;
            }
        }
        if (preg_match('/^\_declineinvite\_([a-z0-9]+)$/', $k, $m)) {
            $institution = $m[1];
            delete_records('usr_institution_request', 'usr', $USER->id,
                           'institution', $institution, 'confirmedinstitution', 1);
            break;
        }
    }
    redirect(get_config('wwwroot') . 'account/institutions.php');
}



foreach ($institutions as $k => $i) {
    if ($i->name == 'mahara' || !$i->registerallowed) {
        unset($institutions[$k]);
    }
}
// Request institution membership button for the remaining insitutions
if (!empty($institutions) &&
    (get_config('usersallowedmultipleinstitutions') || empty($member))) {
    $options = array();
    foreach ($institutions as $i) {
        if ($i->registerallowed) {
            $options[$i->name] = $i->displayname;
        }
    }
    natcasesort($options);
    $joinform = pieform(array(
        'name'        => 'requestmembership',
        'method'      => 'post',
        'plugintype'  => 'core',
        'pluginname'  => 'account',
        'elements'    => array(
            'institution' => array(
                'type' => 'select',
                'title' => get_string('institution'),
                'collapseifoneoption' => false,
                'options' => $options,
                'defaultvalue' => key($options),
             ),
            'studentid' => array(
                'type'         => 'text',
                'title'        => get_string('optionalinstitutionid'),
                'defaultvalue' => $USER->studentid,
             ),
            'submit' => array(
                'type' => 'submit',
                'value' => get_string('sendrequest'),
             ),
        )
    ));
} else {
    $joinform = null;
}

function requestmembership_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    if (!empty($values['institution'])) {
        if (get_field('institution', 'registerallowed', 'name', $values['institution'])) {
            $USER->add_institution_request($values['institution'], $values['studentid']);
        }
        else {
            $SESSION->add_error_msg(get_string('registrationnotallowed'));
        }
    }
    redirect(get_config('wwwroot') . 'account/institutions.php');
}

$smarty = smarty();
$smarty->assign('memberform', $memberform);
$smarty->assign('requestedform', $requestedform);
$smarty->assign('invitedform', $invitedform);
$smarty->assign('joinform', $joinform);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('account/institutions.tpl');
