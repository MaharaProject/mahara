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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
require('init.php');

/*
 * This page handles three different tasks:
 *   1) Showing a visitor the registration form
 *   2) Telling the visitor to check their e-mail for a message
 *   3) Given a key, display profile information to edit
 *
 * It uses the session to store some state
 */
if (!session_id()) {
    session_start();
}

// @todo stuff to do for registration:
//   - captcha image for step 1
//   - show mandatory profile fields as configured sitewide. Relies on penny
//     doing profile database work (internal artefact)
//   - allow uploading of optional profile icon. requires profile stuffs

// Step two of registration (first as it's the easiest): the user has
// registered, show them a screen telling them this.
if (!empty($_SESSION['registered'])) {
    unset($_SESSION['registered']);
    die_info(get_string('registeredok'));
}

if (isset($_GET['key'])) {
    if (!$registration = get_record('usr_registration', 'regkey', $_GET['key'])) {
        die_info(get_string('registrationnosuchkey'));
    }

    // This should show mandatory profile fields, and the optional profile icon thing.
    // But until the database is ready, just do the stuff required to register the user.

    // Move the user record to the usr table from the registration table
    $registrationid = $registration->id;
    unset($registration->id);
    insert_record('usr', $registration);
    delete_records('usr_registration', 'id', $registrationid);

    // Log the user in and send them to the homepage
    $SESSION->login($registration);
    redirect(get_config('wwwroot'));
}

$elements = array(
    'username' => array(
        'type' => 'text',
        'title' => get_string('username'),
        'description' => get_string('usernamedescription'),
        'rules' => array(
            'required' => true
        )
    ),
    'password1' => array(
        'type' => 'password',
        'title' => get_string('password'),
        'description' => get_string('passworddescription'),
        'rules' => array(
            'required' => true
        )
    ),
    'password2' => array(
        'type' => 'password',
        'title' => get_string('confirmpassword'),
        'description' => get_string('password2description'),
        'rules' => array(
            'required' => true
        )
    ),
    'firstname' => array(
        'type' => 'text',
        'title' => get_string('firstname'),
        'description' => get_string('firstnamedescription'),
        'rules' => array(
            'required' => true
        )
    ),
    'lastname' => array(
        'type' => 'text',
        'title' => get_string('lastname'),
        'description' => get_string('lastnamedescription'),
        'rules' => array(
            'required' => true
        )
    ),
    'email' => array(
        'type' => 'text',
        'title' => get_string('emailaddress'),
        'description' => get_string('emailaddressdescription'),
        'rules' => array(
            'required' => true,
            'email' => true
        )
    )
);

$institutions = get_records_select('institution', "registerallowed = 1 AND authplugin = 'internal'");
if (count($institutions) > 1) {
    $options = array();
    foreach ($institutions as $institution) {
        $options[$institution->name] = $institution->displayname;
    }
    $elements['institution'] = array(
        'type' => 'select',
        'title' => get_string('institution'),
        'description' => get_string('institutiondescription'),
        'options' => $options
    );
}
else {
    $elements['institution'] = array(
        'type' => 'hidden',
        'value' => 'mahara'
    );
}

$elements['tandc'] = array(
    'type' => 'radio',
    'title' => get_string('iagreetothetermsandconditions'),
    'description' => get_string('youmustagreetothetermsandconditions'),
    'options' => array(
        'yes' => get_string('yes'),
        'no'  => get_string('no')
    ),
    'defaultvalue' => 'no',
    'rules' => array(
        'required' => true
    ),
    'separator' => '<br>'
);

$elements['submit'] = array(
    'type' => 'submitcancel',
    'value' => array(get_string('register'), get_string('cancel'))
);

$form = array(
    'name' => 'register',
    'method' => 'post',
    'action' => '',
    'elements' => $elements
);

/**
 * @todo add note: because the form select thing will eventually enforce
 * that the result for $values['institution'] was in the original lot,
 * and because that only allows authmethods that use 'internal' auth, we
 * can guarantee that the auth method is internal
 */
function register_validate(Form $form, $values) {
    $institution = $values['institution'];
    safe_require('auth', 'internal', 'lib.php', 'require_once');

    if (!$form->get_error('username') && !AuthInternal::is_username_valid($values['username'])) {
        $form->set_error('username', get_string('usernameinvalidform'));
    }

    if (!$form->get_error('username') && record_exists('usr', 'username', $values['username'])) {
        $form->set_error('username', get_string('usernamealreadytaken'));
    }

    if (!$form->get_error('password1') && !AuthInternal::is_password_valid($values['password1'])) {
        $form->set_error('password1', get_string('passwordinvalidform'));
    }

    if (!$form->get_error('password1') && $values['password1'] != $values['password2']) {
        $form->set_error('password2', get_string('passwordsdonotmatch'));
    }

    // First name and last name must contain at least one non whitespace
    // character, so that there's something to read
    if (!$form->get_error('firstname') && !preg_match('/\S/', $values['firstname'])) {
        $form->set_error('firstname', get_string('thisfieldisrequired'));
    }

    if (!$form->get_error('lastname') && !preg_match('/\S/', $values['lastname'])) {
        $form->set_error('lastname', get_string('thisfieldisrequired'));
    }

    // The e-mail address cannot already be in the system
    if (!$form->get_error('email')
        && (record_exists('usr', 'email', $values['email'])
        || record_exists('usr_registration', 'email', $values['email']))) {
        $form->set_error('email', get_string('emailalreadytaken'));
    }
    
    // If the user hasn't agreed to the terms and conditions, don't bother
    if ($values['tandc'] != 'yes') {
        $form->set_error('tandc', get_string('youmustagreetotheterms'));
    }
}

function register_submit($values) {
    global $SESSION;

    // store password encrypted
    // don't die_info, since reloading the page shows the login form.
    // instead, redirect to some other page that says this
    safe_require('auth', 'internal', 'lib.php', 'require_once');
    $values['salt']     = substr(md5(rand(1000000, 9999999)), 2, 8);
    $values['password'] = AuthInternal::encrypt_password($values['password1'], $values['salt']);
    $values['regkey']   = get_random_key();
    $values['timeregistered'] = db_format_timestamp(time());
    try {
        insert_record('usr_registration', $values);

        $user =(object) $values;
        email_user($user, null,
            get_string('registeredemailsubject'),
            get_string('registeredemailmessagetext', 'mahara', $values['regkey']),
            get_string('registeredemailmessagehtml', 'mahara', $values['regkey'], $values['regkey']));
    }
    catch (EmailException $e) {
        die_info(get_string('registrationunsuccessful'));
    }
    catch (SQLException $e) {
        die_info(get_string('registrationunsuccessful'));
    }

    // Add a marker in the session to say that the user has registered
    $_SESSION['registered'] = true;

    redirect(get_config('wwwroot') . 'register.php');
}

function register_cancel_submit() {
    redirect(get_config('wwwroot'));
}

$smarty = smarty();
$smarty->assign('register_form', form($form));
$smarty->display('register.tpl');

?>
