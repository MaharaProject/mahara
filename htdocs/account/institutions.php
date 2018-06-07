<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'engage/institutions');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'institutions');
require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('institutionmembership'));

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
                'class' => 'btn-secondary',
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
            'class' => 'btn-secondary',
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
        $elements[$i] = array(
            'type' => 'multisubmit',
            'name' => 'invite_' . $i,
            'options' => array('confirm', 'decline'),
            'primarychoice' => 'confirm',
            'title' => get_string('youhavebeeninvitedtojoin', 'mahara',
                                  $institutions[$i]->displayname),
            'class' => 'btn-secondary',
            'value' => array(get_string('joininstitution'), get_string('decline'))
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
}
else {
    $invitedform = null;
}

function confirminvite_submit(Pieform $form, $values) {
    global $USER;
    foreach ($values as $k => $v) {
        if (preg_match('/^invite\_([a-z0-9]+)$/', $k, $m)) {
            $institution = $m[1];
            if ($v == 'confirm') {
                if (count_records('usr_institution_request', 'usr', $USER->id,
                                  'institution', $institution, 'confirmedinstitution', 1)) {
                    $USER->join_institution($institution);
                    break;
                }
            }
            else if ($v == 'decline') {
                delete_records('usr_institution_request', 'usr', $USER->id,
                               'institution', $institution, 'confirmedinstitution', 1);
                break;
            }
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
                'rules'        => array( 'required' => true ),
             ),
            'studentid' => array(
                'type'         => 'text',
                'title'        => get_string('optionalinstitutionid'),
                'defaultvalue' => $USER->studentid,
             ),
            'submit' => array(
                'class' => 'btn-primary',
                'type'  => 'submit',
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
setpageicon($smarty, 'icon-university');
$smarty->assign('memberform', $memberform);
$smarty->assign('requestedform', $requestedform);
$smarty->assign('invitedform', $invitedform);
$smarty->assign('joinform', $joinform);
$smarty->display('account/institutions.tpl');
