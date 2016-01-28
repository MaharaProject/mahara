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
define('PUBLIC', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'site');
define('SECTION_PAGE', 'register');
require('init.php');
require_once('lib/antispam.php');
require_once('lib/institution.php');
define('TITLE', get_string('register'));
$key = param_alphanum('key', null);

/*
 * This page handles three different tasks:
 *   1) Showing a visitor the registration form
 *   2) Telling the visitor to check their e-mail for a message
 *   3) Given a key, display profile information to edit
 *
 * It uses the session to store some state
 */

// Logged in people can't register. If someone passes a key however, log the
// user out and see if this key registers someone
if (is_logged_in()) {
    if ($key) {
        $USER->logout();
    }
    else {
        redirect();
    }
}

// Step two of registration (first as it's the easiest): the user has
// registered, show them a screen telling them this.
if (!$key && !empty($_SESSION['registered'])) {
    unset($SESSION->registered);
    die_info(get_string('registeredok', 'auth.internal'));
}

// The user has registered with an institution that requires approval,
// tell them to wait.
if (!empty($_SESSION['registeredokawaiting'])) {
    unset($SESSION->registeredokawaiting);
    die_info(get_string('registeredokawaitingemail2', 'auth.internal'));
}

if (!empty($_SESSION['registrationcancelled'])) {
    unset($SESSION->registrationcancelled);
    die_info(get_string('registrationcancelledok', 'auth.internal'));
}

// Step three of registration - given a key register the user
if (isset($key)) {

    // Begin the registration form buliding
    if (!$registration = get_record_select('usr_registration', '"key" = ? AND pending != 1', array($key))) {
        die_info(get_string('registrationnosuchkey1', 'auth.internal'));
    }

    if (strtotime($registration->expiry) < time()) {
        die_info(get_string('registrationexpiredkey', 'auth.internal'));
    }

    // In case a new session has started, reset the session language
    // to the one selected during registration
    if (!empty($registration->lang)) {
        $SESSION->set('lang', $registration->lang);
    }

    function create_registered_user($profilefields=array()) {
        global $registration, $SESSION, $USER;
        require_once(get_config('libroot') . 'user.php');

        db_begin();

        // Move the user record to the usr table from the registration table
        $registrationid = $registration->id;
        unset($registration->id);
        unset($registration->expiry);
        if ($expirytime = get_config('defaultregistrationexpirylifetime')) {
            $registration->expiry = db_format_timestamp(time() + $expirytime);
        }
        $registration->lastlogin = db_format_timestamp(time());

        $authinstance = get_record('auth_instance', 'institution', $registration->institution, 'authname', $registration->authtype ? $registration->authtype : 'internal');
        if (false == $authinstance) {
            throw new ConfigException('No ' . ($registration->authtype ? $registration->authtype : 'internal') . ' auth instance for institution');
        }

        if (!empty($registration->extra)) {
            // Additional user settings were added during confirmation
            $extrafields = unserialize($registration->extra);
        }

        $user = new User();
        $user->active           = 1;
        $user->authinstance     = $authinstance->id;
        $user->firstname        = $registration->firstname;
        $user->lastname         = $registration->lastname;
        $user->email            = $registration->email;
        $user->username         = get_new_username($user->firstname . $user->lastname);
        $user->passwordchange   = 1;

        // Points that indicate the user is a "new user" who should be restricted from spammy activities.
        // We count these down when they do good things; when they have 0 they're no longer a "new user"
        if (is_using_probation()) {
            $user->probation = get_config('probationstartingpoints');
        }
        else {
            $user->probation = 0;
        }

        if ($registration->institution != 'mahara') {
            if (count_records_select('institution', "name != 'mahara'") == 1 || $registration->pending == 2) {
                if (get_config_plugin('artefact', 'file', 'institutionaloverride')) {
                    $user->quota = get_field('institution', 'defaultquota', 'name', $registration->institution);
                }
            }
        }

        create_user($user, $profilefields);

        // If the institution is 'mahara' then don't do anything
        if ($registration->institution != 'mahara') {
            $institutions = get_records_select_array('institution', "name != 'mahara'");

            // If there is only one available, join it without requiring approval
            if (count($institutions) == 1) {
                $user->join_institution($registration->institution);
            }
            // Else, since there are multiple, request to join
            else {
                if ($registration->pending == 2) {
                    if (get_config('requireregistrationconfirm')
                        || get_field('institution', 'registerconfirm', 'name', $registration->institution)) {
                        $user->join_institution($registration->institution);
                    }
                }
                else {
                    if ($registration->authtype && $registration->authtype != 'internal') {
                        $auth = AuthFactory::create($authinstance->id);
                        if ($auth->weautocreateusers) {
                            $user->join_institution($registration->institution);
                        }
                        else {
                            $user->add_institution_request($registration->institution);
                        }
                    }
                    else {
                        $user->add_institution_request($registration->institution);
                    }
                }
            }

            if (!empty($extrafields->institutionstaff)) {
                // If the user isn't a member yet, this does nothing, but that's okay, it'll
                // only be set after successful confirmation.
                set_field('usr_institution', 'staff', 1, 'usr', $user->id, 'institution', $registration->institution);
            }
        }


        if (!empty($registration->lang) && $registration->lang != 'default') {
            set_account_preference($user->id, 'lang', $registration->lang);
        }

        // Delete the old registration record
        delete_records('usr_registration', 'id', $registrationid);

        db_commit();

        // Log the user in and send them to the homepage
        $USER = new LiveUser();
        $USER->reanimate($user->id, $authinstance->id);

        if (function_exists('local_post_register')) {
            local_post_register($registration);
        }

        $SESSION->add_ok_msg(get_string('registrationcomplete', 'mahara', get_config('sitename')));
        $SESSION->set('resetusername', true);
        redirect();
    }
    create_registered_user();
}


// Default page - show the registration form
list($form, $registerconfirm) = auth_generate_registration_form('register', 'internal', '/register.php');
if (!$form) {
    die_info(get_string('registeringdisallowed'));
}
list($formhtml, $js) = auth_generate_registration_form_js($form, $registerconfirm);

$registerdescription = get_string('registerwelcome');
if ($registerterms = get_config('registerterms')) {
    $registerdescription .= ' ' . get_string('registeragreeterms');
}
$registerdescription .= ' ' . get_string('registerprivacy');

$smarty = smarty();
$smarty->assign('register_form', $formhtml);
$smarty->assign('registerdescription', $registerdescription);
if ($registerterms) {
    $smarty->assign('termsandconditions', '<a name="user_acceptterms"></a>' . get_site_page_content('termsandconditions'));
}
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('register.tpl');
