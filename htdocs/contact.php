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
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'site');
define('SECTION_PAGE', 'contact');
require('init.php');
require_once('pieforms/pieform.php');
require_once('lib/antispam.php');
define('TITLE', get_string('contactus'));
define('SPAM_SCORE', 3);

if ($USER->is_logged_in()) {
    $userid = $USER->get('id');
    $name = display_name($userid);
    $email = $USER->get('email');
}
else {
    $userid = 0;
    $name = '';
    $email = '';
}

// we're in the middle of processing the form, so read the time
// from the form rather than getting a new one
if ($_POST) {
    $time = $_POST['timestamp'];
}
else {
    $time = time();
}

$fields = array('name', 'email', 'subject', 'message', 'userid', 'submit', 'invisiblefield', 'invisiblesubmit');
$hashed_fields = hash_fieldnames($fields, $time);

$elements = array(
    'name' => array(
        'type'  => 'text',
        'name' => $hashed_fields['name'],
        'title' => get_string('name'),
        'defaultvalue' => $name,
        'rules' => array(
            'required'    => true
        ),
    ),
    'email' => array(
        'type'  => 'text',
        'name' => $hashed_fields['email'],
        'title' => get_string('email'),
        'defaultvalue' => $email,
        'rules' => array(
            'required'    => true,
            'email' => true,
        ),
    ),
    'subject' => array(
        'type'  => 'text',
        'name' => $hashed_fields['subject'],
        'title' => get_string('subject'),
        'defaultvalue' => '',
    ),
    'message' => array(
        'type'  => 'textarea',
        'name' => $hashed_fields['message'],
        'rows'  => 10,
        'cols'  => 60,
        'title' => get_string('message'),
        'defaultvalue' => '',
        'rules' => array(
            'required'    => true
        ),
    )
);

$elements['invisiblefield'] = array(
    'type' => 'text',
    'name' => $hashed_fields['invisiblefield'],
    'title' => get_string('spamtrap'),
    'defaultvalue' => '',
    'class' => 'dontshow',
);
$elements['userid'] = array(
    'type'  => 'hidden',
    'name' => $hashed_fields['userid'],
    'value' => $userid,
);
$elements['timestamp'] = array(
    'type' => 'hidden',
    'value' => $time,
);
$elements['invisiblesubmit'] = array(
    'type'  => 'submit',
    'name' => $hashed_fields['invisiblesubmit'],
    'value' => get_string('spamtrap'),
    'class' => 'dontshow',
);
$elements['submit'] = array(
    'type'  => 'submit',
    'name' => $hashed_fields['submit'],
    'value' => get_string('sendmessage'),
);

// swap the name and email fields at random
if (rand(0,1)) {
    $name = array_shift($elements);
    $email = array_shift($elements);
    array_unshift($elements, $email, $name);
}

$contactform = pieform(array(
    'name'     => 'contactus',
    'method'   => 'post',
    'action'   => '',
    'elements' => $elements
));

function contactus_validate(Pieform $form, $values) {
    global $SESSION;
    $error = false;
    $currenttime = time();
    // read the timestamp field
    $timestamp = $values['timestamp'];
    // recompute the field names
    $fields = array('name', 'email', 'subject', 'message', 'userid', 'submit', 'invisiblefield', 'invisiblesubmit');
    $hashed = hash_fieldnames($fields, $timestamp);
    // make sure the submission is less than a day, and more than 5 seconds old
    if ($currenttime - $timestamp < 5 || $currenttime - $timestamp > 86400) {
        $error = true;
    }
    // make sure the real submit button was used. If it wasn't, it won't exist.
    elseif (!isset($values[$hashed['submit']]) || isset($values[$hashed['invisiblesubmit']])) {
        $error = true;
    }
    // make sure the invisible field is empty
    elseif (!isset($values[$hashed['invisiblefield']]) || $values[$hashed['invisiblefield']] != '') {
        $error = true;
    }
    // make sure all the other data fields exist
    elseif (!(isset($values[$hashed['name']]) && isset($values[$hashed['email']]) &&
        isset($values[$hashed['subject']]) && isset($values[$hashed['message']]))) {
        $error = true;
    }
    else {
        $spamtrap = new_spam_trap(array(
            array(
                'type' => 'name',
                'value' => $values[$hashed['name']],
            ),
            array(
                'type' => 'email',
                'value' => $values[$hashed['email']],
            ),
            array(
                'type' => 'subject',
                'value' => $values[$hashed['subject']],
            ),
            array(
                'type' => 'body',
                'value' => $values[$hashed['message']],
            ),
        ));
        if ($spamtrap->is_spam()) {
            $error = true;
        }
    }
    if ($error) { 
        $msg = get_string('formerror');
        $emailcontact = get_config('emailcontact');
        if (!empty($emailcontact)) {
            $msg .= ' ' . get_string('formerroremail', 'mahara', $emailcontact, $emailcontact);
        }
        $SESSION->add_error_msg($msg);
        $form->set_error($hashed['submit'], '');
    }
}

function contactus_submit(Pieform $form, $values) {
    global $SESSION;
    // read the timestamp field
    $timestamp = $values['timestamp'];
    // recompute the field names
    $fields = array('name', 'email', 'subject', 'message', 'userid', 'submit', 'invisiblefield', 'invisiblesubmit');
    $hashed = hash_fieldnames($fields, $timestamp);
    $data = new StdClass;
    $data->fromname    = $values[$hashed['name']];
    $data->fromemail   = $values[$hashed['email']];
    $data->subject     = $values[$hashed['subject']];
    $data->message     = $values[$hashed['message']];
    if ($values[$hashed['userid']]) {
        $data->fromuser = $values[$hashed['userid']];
    }
    require_once('activity.php');
    activity_occurred('contactus', $data);
    $SESSION->add_ok_msg(get_string('messagesent'));
    redirect();
}

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('form', $contactform);
$smarty->display('form.tpl');

?>
