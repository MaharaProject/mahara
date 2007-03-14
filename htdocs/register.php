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
define('TITLE', get_string('register'));

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
if (is_logged_in()) {
    redirect();
}

// Step two of registration (first as it's the easiest): the user has
// registered, show them a screen telling them this.
if (!empty($_SESSION['registered'])) {
    unset($_SESSION['registered']);
    die_info(get_string('registeredok', 'auth.internal'));
}

$key = param_alphanum('key', null);
// Step three of registration - given a key, fill out mandatory profile fields,
// optional profile icon, and register the user
if (isset($key)) {

    function profileform_submit(Pieform $form, $values) {
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

        // Handle the profile image if uploaded
        if ($values['profileimg'] && $values['profileimg']['error'] == 0 && $values['profileimg']['size'] > 0) {
            // Entry in artefact table
            $artefact = new ArtefactTypeProfileIcon();
            $artefact->set('owner', $registration->id);
            $artefact->set('title', ($values['profileimgtitle']) ? $values['profileimgtitle'] : $values['profileimg']['name']);
            $artefact->set('note', $values['profileimg']['name']);
            $artefact->commit();

            $id = $artefact->get('id');

            $filesize = filesize($values['profileimg']['tmp_name']);
            set_field('usr', 'quotaused', $filesize, 'id', $registration->id);
            $registration->quotaused = $filesize;
            $registration->quota = get_config_plugin('artefact', 'file', 'defaultquota');
            set_field('usr', 'profileicon', $id, 'id', $registration->id);
            $registration->profileicon = $id;

            // Move the file into the correct place.
            $directory = get_config('dataroot') . 'artefact/internal/profileicons/' . ($id % 256) . '/';
            check_dir_exists($directory);
            move_uploaded_file($values['profileimg']['tmp_name'], $directory . $id);
        }
        else {
            $registration->quotaused = 0;
            $registration->quota = get_config_plugin('artefact', 'file', 'defaultquota');
        }

        db_commit();
        handle_event('createuser', $registration);

        // Log the user in and send them to the homepage
        $USER->login($registration);
        redirect();
    }
    
    function profileform_validate(Pieform $form, $values) {
        // Profile icon, if uploaded
        if ($values['profileimg'] && $values['profileimg']['error'] == 0 && $values['profileimg']['size'] > 0) {
            require_once('file.php');
            if (!is_image_mime_type(get_mime_type($values['profileimg']['tmp_name']))) {
                $form->set_error('profileimg', get_string('filenotimage'));
            }

            // Check the file isn't greater than 300x300
            list($width, $height) = getimagesize($values['profileimg']['tmp_name']);
            if ($width > 300 || $height > 300) {
                $form->set_error('profileimg', get_string('profileiconimagetoobig', 'artefact.internal', $width, $height));
            }
        }

        foreach(ArtefactTypeProfile::get_mandatory_fields() as $field => $type) {
            // @todo here and above, use the method for getting "always mandatory" fields
            if (in_array($field, array('firstname', 'lastname', 'email'))) {
                continue;
            }
            // @todo here, validate the fields using their static validate method
        }
    }


    // Begin the registration form buliding
    if (!$registration = get_record_select('usr_registration', 'key = ? AND expiry >= ?', array($key, db_format_timestamp(time())))) {
        die_info(get_string('registrationnosuchkey', 'auth.internal'));
    }

    $elements = array(
        'optionalheader' => array(
            'type'  => 'html',
            'value' => get_string('registerstep3fieldsoptional')
        ),
        'profileimg' => array(
            'type' => 'file',
            'title' => 'Profile Image'
        ),
        'profileimgtitle' => array(
            'type' => 'text',
            'title' => 'Title'
        )
    );

    $mandatoryheaderadded = false;
    safe_require('artefact', 'internal');
    foreach(ArtefactTypeProfile::get_mandatory_fields() as $field => $type) {
        if (in_array($field, array('firstname', 'lastname', 'email'))) {
            continue;
        }

        if (!$mandatoryheaderadded) {
            $elements['mandatoryheader'] = array(
                'type'  => 'html',
                'value' => get_string('registerstep3fieldsmandatory')
            );
            $mandatoryheaderadded = true;
        }

        $elements[$field] = array(
            'type'  => $type,
            'title' => get_string($field, 'artefact.internal'),
            'rules' => array('required' => true)
        );

        // @todo ruthlessly stolen from artefact/internal/index.php, could be merged
        if ($type == 'wysiwyg') {
            $elements[$field]['rows'] = 10;
            $elements[$field]['cols'] = 60;
        }
        if ($type == 'textarea') {
            $elements[$field]['rows'] = 4;
            $elements[$field]['cols'] = 60;
        }
        if ($field == 'country') {
            $elements[$field]['options'] = getoptions_country();
            $elements[$field]['defaultvalue'] = 'nz';
        }
    }

    $elements['key'] = array(
        'type' => 'hidden',
        'name' => 'key',
        'value' => $key
    );
    $elements['submit'] = array(
        'type' => 'submit',
        'value' => get_string('completeregistration', 'auth.internal')
    );

    $form = pieform(array(
        'name'     => 'profileform',
        'method'   => 'post',
        'action'   => '',
        'elements' => $elements
    ));

    $smarty = smarty();
    $smarty->assign('register_profile_form', $form);
    $smarty->display('register.tpl');
    exit;
}


// Default page - show the registration form

$elements = array(
    'username' => array(
        'type' => 'text',
        'title' => get_string('username'),
        'rules' => array(
            'required' => true
        ),
        'help' => true,
    ),
    'password1' => array(
        'type' => 'password',
        'title' => get_string('password'),
        'rules' => array(
            'required' => true
        ),
        'help' => true,
    ),
    'password2' => array(
        'type' => 'password',
        'title' => get_string('confirmpassword'),
        'rules' => array(
            'required' => true
        )
    ),
    'firstname' => array(
        'type' => 'text',
        'title' => get_string('firstname'),
        'rules' => array(
            'required' => true
        )
    ),
    'lastname' => array(
        'type' => 'text',
        'title' => get_string('lastname'),
        'rules' => array(
            'required' => true
        )
    ),
    'email' => array(
        'type' => 'text',
        'title' => get_string('emailaddress'),
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
        'options' => $options,
        'rules' => array(
            'required' => true
        )
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
    'separator' => ' &nbsp; '
);

$elements['captcha'] = array(
    'type' => 'html',
    'title' => get_string('captchatitle'),
    'description' => get_string('captchadescription'),
    'value' => '<img src="' . get_config('wwwroot') . 'captcha.php" alt="' . get_string('captchaimage') . '" style="padding: 2px 0;"><br>'
        . '<input type="text" class="text required" name="captcha" style="width: 137px;" tabindex="3">',
    'rules' => array('required' => true)
);

$elements['submit'] = array(
    'type' => 'submitcancel',
    'value' => array(get_string('register'), get_string('cancel'))
);

$form = array(
    'name' => 'register',
    'method' => 'post',
    'plugintype' => 'core',
    'pluginname' => 'register',
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
    global $SESSION;
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
        || record_exists('artefact_internal_profile_email', 'email', $values['email']))) {
        $form->set_error('email', get_string('emailalreadytaken', 'auth.internal'));
    }
    
    // If the user hasn't agreed to the terms and conditions, don't bother
    if ($values['tandc'] != 'yes') {
        $form->set_error('tandc', get_string('youmaynotregisterwithouttandc', 'auth.internal'));
    }

    // CAPTCHA image
    if (!isset($_POST['captcha']) || strtolower($_POST['captcha']) != strtolower($SESSION->get('captcha'))) {
        $form->set_error('captcha', get_string('captchaincorrect'));
    }
}

function register_submit(Pieform $form, $values) {
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
        $user->admin = 0;
        $user->staff = 0;
        email_user($user, null,
            get_string('registeredemailsubject', 'auth.internal', get_config('sitename')),
            get_string('registeredemailmessagetext', 'auth.internal', $values['firstname'], get_config('sitename'), $values['key'], get_config('sitename')),
            get_string('registeredemailmessagehtml', 'auth.internal', $values['firstname'], get_config('sitename'), $values['key'], $values['key'], get_config('sitename')));
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

    redirect('/register.php');
}

function register_cancel_submit() {
    redirect();
}

$smarty = smarty();
$smarty->assign('register_form', pieform($form));
$smarty->display('register.tpl');

?>
