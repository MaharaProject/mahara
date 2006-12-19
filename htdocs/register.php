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
define('MENUITEM', 'home');
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

// Logged in people can't register
if ($USER->is_logged_in()) {
    redirect(get_config('wwwroot'));
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
    die_info(get_string('registeredok', 'auth.internal'));
}

// Step three of registration - given a key, fill out mandatory profile fields,
// optional profile icon, and register the user
if (isset($_REQUEST['key'])) {

    function register_profile_submit($values) {
        global $registration, $SESSION, $USER;
        db_begin();

        // Move the user record to the usr table from the registration table
        $registrationid = $registration->id;
        unset($registration->id);
        unset($registration->expiry);
        if ($expirytime = get_field('institution', 'defaultaccountlifetime', 'name', $registration->institution)) {
            $registration->expiry = db_format_timestamp(time() + $expirytime);
        }
        $registration->lastlogin = db_format_timestamp(time());
        $registration->id = insert_record('usr', $registration, 'id', true);
        log_debug($registration);

        // Insert standard stuff as artefacts
        set_profile_field($registration->id, 'email', $registration->email);
        set_profile_field($registration->id, 'firstname', $registration->firstname);
        set_profile_field($registration->id, 'lastname', $registration->lastname);

        // Delete the old registration record
        delete_records('usr_registration', 'id', $registrationid);

        // Set mandatory profile fields 
        foreach(ArtefactTypeProfile::get_mandatory_fields() as $field => $type) {
            // @todo here and above, use the method for getting "always mandatory" fields
            if (in_array($field, array('firstname', 'lastname', 'email'))) {
                continue;
            }
            set_profile_field($registration->id, $field, $values[$field]);
        }

        db_commit();
        handle_event('createuser', $registration);

        // Log the user in and send them to the homepage
        $USER->login($registration);
        redirect(get_config('wwwroot'));
    }
    
    function register_profile_validate(Pieform $form, $values) {
        foreach(ArtefactTypeProfile::get_mandatory_fields() as $field => $type) {
            // @todo here and above, use the method for getting "always mandatory" fields
            if (in_array($field, array('firstname', 'lastname', 'email'))) {
                continue;
            }
            // @todo here, validate the fields using their static validate method
        }
    }


    if (!$registration = get_record_select('usr_registration', 'key = ? AND expiry >= ?', array($_REQUEST['key'], db_format_timestamp(time())))) {
        die_info(get_string('registrationnosuchkey', 'auth.internal'));
    }

    $elements = array();
    safe_require('artefact', 'internal');
    foreach(ArtefactTypeProfile::get_mandatory_fields() as $field => $type) {
        if (in_array($field, array('firstname', 'lastname', 'email'))) {
            continue;
        }
        $elements[$field] = array(
            'type'  => $type,
            'title' => get_string($field, 'artefact.internal'),
            'rules' => array('required' => true)
        );
    }

    // Not until when files infrastructure is in place...
    //$elements['optionalheader'] = array(
    //    'value' => '<tr><th colspan="2">Profile Image</th></tr><tr><td colspan="2">You may optionally choose a profile image to uploade</td></tr>'
    //);
    //$elements['profileimg'] = array(
    //    'type' => 'file',
    //    'title' => 'Profile Image',
    //);
    $elements['key'] = array(
        'type' => 'hidden',
        'name' => 'key',
        'value' => $_REQUEST['key']
    );
    $elements['submit'] = array(
        'type' => 'submit',
        'value' => get_string('completeregistration', 'auth.internal')
    );

    $form = array(
        'name'     => 'register_profile',
        'method'   => 'post',
        'action'   => '',
        'elements' => $elements
    );

    $smarty = smarty();
    $smarty->assign('register_profile_form', pieform($form));
    $smarty->display('register.tpl');
    exit;
}


// Default page - show the registration form

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

$institutions = get_records_select_array('institution', "registerallowed = 1 AND authplugin = 'internal'");
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
else if ($institutions) {
    $elements['institution'] = array(
        'type' => 'hidden',
        'value' => 'mahara'
    );
}
else {
    die_info(get_string('registeringdisallowed'));
}

$elements['tandc'] = array(
    'type' => 'radio',
    'title' => get_string('iagreetothetermsandconditions', 'auth.internal'),
    'description' => get_string('youmustagreetothetermsandconditions', 'auth.internal'),
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
    'renderer' => 'table',
    'elements' => $elements
);

/**
 * @todo add note: because the form select thing will eventually enforce
 * that the result for $values['institution'] was in the original lot,
 * and because that only allows authmethods that use 'internal' auth, we
 * can guarantee that the auth method is internal
 */
function register_validate(Pieform $form, $values) {
    $institution = $values['institution'];
    safe_require('auth', 'internal');

    if (!$form->get_error('username') && !AuthInternal::is_username_valid($values['username'])) {
        $form->set_error('username', get_string('usernameinvalidform', 'auth.internal'));
    }

    if (!$form->get_error('username') && record_exists('usr', 'username', $values['username'])) {
        $form->set_error('username', get_string('usernamealreadytaken', 'auth.internal'));
    }

    password_validate($form, $values, $values['username'], $values['institution']);

    // First name and last name must contain at least one non whitespace
    // character, so that there's something to read
    if (!$form->get_error('firstname') && !preg_match('/\S/', $values['firstname'])) {
        $form->set_error('firstname', $form->i18n('required'));
    }

    if (!$form->get_error('lastname') && !preg_match('/\S/', $values['lastname'])) {
        $form->set_error('lastname', $form->i18n('required'));
    }

    // The e-mail address cannot already be in the system
    if (!$form->get_error('email')
        && (record_exists('usr', 'email', $values['email'])
        || record_exists('usr_registration', 'email', $values['email']))) {
        $form->set_error('email', get_string('emailalreadytaken', 'auth.internal'));
    }
    
    // If the user hasn't agreed to the terms and conditions, don't bother
    if ($values['tandc'] != 'yes') {
        $form->set_error('tandc', get_string('youmaynotregisterwithouttandc', 'auth.internal'));
    }
}

function register_submit($values) {
    global $SESSION;

    // store password encrypted
    // don't die_info, since reloading the page shows the login form.
    // instead, redirect to some other page that says this
    safe_require('auth', 'internal');
    $values['salt']     = substr(md5(rand(1000000, 9999999)), 2, 8);
    $values['password'] = AuthInternal::encrypt_password($values['password1'], $values['salt']);
    $values['key']   = get_random_key();
    // @todo the expiry date should be configurable
    $values['expiry'] = db_format_timestamp(time() + 86400);
    try {
        insert_record('usr_registration', $values);

        $user =(object) $values;
        email_user($user, null,
            get_string('registeredemailsubject', 'auth.internal', get_config('sitename')),
            get_string('registeredemailmessagetext', 'auth.internal', $values['key']),
            get_string('registeredemailmessagehtml', 'auth.internal', $values['key'], $values['key']));
    }
    catch (EmailException $e) {
        log_warn($e);
        die_info(get_string('registrationunsuccessful', 'auth.internal'));
    }
    catch (SQLException $e) {
        log_warn($e);
        die_info(get_string('registrationunsuccessful', 'auth.internal'));
    }

    // Add a marker in the session to say that the user has registered
    $_SESSION['registered'] = true;

    redirect(get_config('wwwroot') . 'register.php');
}

function register_cancel_submit() {
    redirect(get_config('wwwroot'));
}

$smarty = smarty();
$smarty->assign('register_form', pieform($form));
$smarty->display('register.tpl');

?>
