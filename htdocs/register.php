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
define('SECTION_PAGE', 'register');
require('init.php');
require_once('pieforms/pieform.php');
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
if (!session_id()) {
    session_start();
}

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
if (!empty($_SESSION['registered'])) {
    unset($_SESSION['registered']);
    die_info(get_string('registeredok', 'auth.internal'));
}

// The user has registered with an institution that requires approval,
// tell them to wait.
if (!empty($_SESSION['registeredokawaiting'])) {
    unset($_SESSION['registeredokawaiting']);
    die_info(get_string('registeredokawaitingemail2', 'auth.internal'));
}

if (!empty($_SESSION['registrationcancelled'])) {
    unset($_SESSION['registrationcancelled']);
    die_info(get_string('registrationcancelledok', 'auth.internal'));
}

// Step three of registration - given a key register the user
if (isset($key)) {

    // Begin the registration form buliding
    if (!$registration = get_record_select('usr_registration', '"key" = ? AND expiry >= ? AND pending != 1', array($key, db_format_timestamp(time())))) {
        die_info(get_string('registrationnosuchkey', 'auth.internal'));
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
        if ($expirytime = get_config('defaultaccountlifetime')) {
            $registration->expiry = db_format_timestamp(time() + $expirytime);
        }
        $registration->lastlogin = db_format_timestamp(time());

        $authinstance = get_record('auth_instance', 'institution', $registration->institution, 'authname', 'internal');
        if (false == $authinstance) {
            throw new ConfigException('No internal auth instance for institution');
        }

        $user = new User();
        $user->active           = 1;
        $user->authinstance     = $authinstance->id;
        $user->firstname        = $registration->firstname;
        $user->lastname         = $registration->lastname;
        $user->email            = $registration->email;
        $user->username         = get_new_username($user->firstname . $user->lastname);
        $user->passwordchange   = 1;

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
                    if ($confirm = get_field('institution', 'registerconfirm', 'name', $registration->institution)) {
                        $user->join_institution($registration->institution);
                    }
                }
                else {
                    $user->add_institution_request($registration->institution);
                }
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

        $SESSION->add_ok_msg(get_string('registrationcomplete', 'mahara', get_config('sitename')));
        $SESSION->set('resetusername', true);
        redirect();
    }
    create_registered_user();
}


// Default page - show the registration form

$elements = array(
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
$sql = 'SELECT
            i.*
        FROM
            {institution} i,
            {auth_instance} ai
        WHERE
            ai.authname = \'internal\' AND
            ai.institution = i.name AND
            i.registerallowed = 1';
$institutions = get_records_sql_array($sql, array());
$registerconfirm = array();
$reason = false;

if (count($institutions) > 1) {
    $options = array();
    foreach ($institutions as $institution) {
        $options[$institution->name] = $institution->displayname;
        if ($registerconfirm[$institution->name] = $institution->registerconfirm) {
            $options[$institution->name] .= ' (' . get_string('approvalrequired') . ')';
            $reason = true;
        }
    }
    natcasesort($options);
    array_unshift($options, get_string('chooseinstitution', 'mahara'));
    $elements['institution'] = array(
        'type' => 'select',
        'title' => get_string('institution'),
        'options' => $options,
        'rules' => array(
            'required' => true
        )
    );
}
else if ($institutions) { // Only one option - probably mahara ('No Institution') but that's not certain

    $institution = array_shift($institutions);

    $elements['institution'] = array(
        'type' => 'hidden',
        'value' => $institution->name
    );
    $reason = (bool) $institution->registerconfirm;
}
else {
    die_info(get_string('registeringdisallowed'));
}

if ($reason) {
    $elements['reason'] = array(
        'type' => 'textarea',
        'title' => get_string('registrationreason', 'auth.internal'),
        'description' => get_string('registrationreasondesc1', 'auth.internal'),
        'class' => 'js-hidden',
        'rows' => 4,
        'cols' => 60,
    );
}

$registerterms = get_config('registerterms');
if ($registerterms) {
    $elements['tandc'] = array(
        'type' => 'radio',
        'title' => get_string('iagreetothetermsandconditions', 'auth.internal'),
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
}

$elements['submit'] = array(
    'type' => 'submit',
    'value' => get_string('register'),
);

// swap the name and email fields at random
if (rand(0,1)) {
    $emailelement = $elements['email'];
    unset($elements['email']);
    $elements = array('email' => $emailelement) + $elements;
}

$form = array(
    'name' => 'register',
    'method' => 'post',
    'plugintype' => 'core',
    'pluginname' => 'register',
    'action' => '',
    'showdescriptiononerror' => false,
    'renderer' => 'table',
    'elements' => $elements,
    'spam' => array(
        'secret'       => get_config('formsecret'),
        'mintime'      => 5,
        'hash'         => array('firstname', 'lastname', 'email', 'institution', 'reason', 'tandc', 'submit'),
    ),
);

/**
 * @todo add note: because the form select thing will eventually enforce
 * that the result for $values['institution'] was in the original lot,
 * and because that only allows authmethods that use 'internal' auth, we
 * can guarantee that the auth method is internal
 */
function register_validate(Pieform $form, $values) {
    global $SESSION, $registerterms;

    $spamtrap = new_spam_trap(array(
        array(
            'type' => 'name',
            'value' => $values['firstname'],
        ),
        array(
            'type' => 'name',
            'value' => $values['lastname'],
        ),
        array(
            'type' => 'email',
            'value' => $values['email'],
        ),
    ));

    if ($form->spam_error() || $spamtrap->is_spam()) {
        $msg = get_string('formerror');
        $emailcontact = get_config('emailcontact');
        if (!empty($emailcontact)) {
            $msg .= ' ' . get_string('formerroremail', 'mahara', $emailcontact, $emailcontact);
        }
        $form->set_error(null, $msg);
        return;
    }

    $institution = $values['institution'];
    safe_require('auth', 'internal');

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
    if ($registerterms && $values['tandc'] != 'yes') {
        $form->set_error('tandc', get_string('youmaynotregisterwithouttandc', 'auth.internal'));
    }

    $institution = get_record_sql('
        SELECT 
            i.name, i.maxuseraccounts, i.registerallowed, COUNT(u.id)
        FROM {institution} i
            LEFT OUTER JOIN {usr_institution} ui ON ui.institution = i.name
            LEFT OUTER JOIN {usr} u ON (ui.usr = u.id AND u.deleted = 0)
        WHERE
            i.name = ?
        GROUP BY
            i.name, i.maxuseraccounts, i.registerallowed', array($institution));

    if (!empty($institution->maxuseraccounts) && $institution->count >= $institution->maxuseraccounts) {
        $form->set_error($hashed['institution'], get_string('institutionfull'));
    }

    if (!$institution || !$institution->registerallowed) {
        $form->set_error('institution', get_string('registrationnotallowed'));
    }

}

function register_submit(Pieform $form, $values) {
    global $SESSION;

    // don't die_info, since reloading the page shows the login form.
    // instead, redirect to some other page that says this
    safe_require('auth', 'internal');
    $values['key']   = get_random_key();
    $values['lang'] = $SESSION->get('lang');

    // If the institution requires approval, mark the record as pending
    // @todo the expiry date should be configurable
    if ($confirm = get_field('institution', 'registerconfirm', 'name', $values['institution'])) {
        $values['pending'] = 1;
        $values['expiry'] = db_format_timestamp(time() + (86400 * 14)); // now + 2 weeks
    }
    else {
        $values['pending'] = 0;
        $values['expiry'] = db_format_timestamp(time() + 86400);
    }

    try {
        if (!record_exists('usr_registration', 'email', $values['email'])) {
            insert_record('usr_registration', $values);
        }
        else {
            update_record('usr_registration', $values, array('email' => $values['email']));
        }

        $user =(object) $values;
        $user->admin = 0;
        $user->staff = 0;

        // If the institution requires approval, notify institutional admins.
        if ($confirm) {
            $fullname = sprintf("%s %s", trim($user->firstname), trim($user->lastname));
            $institution = new Institution($values['institution']);
            $pendingregistrationslink = sprintf("%sadmin/users/pendingregistrations.php?institution=%s", get_config('wwwroot'), $values['institution']);

            // list of admins for this institution
            if (count($institution->admins()) > 0) {
                $admins = $institution->admins();
            }
            else {
                // use site admins if the institution doesn't have any
                $admins = get_column('usr', 'id', 'admin', 1, 'deleted', 0);
            }

            // email each admin
            // @TODO Respect the notification preferences of the admins.
            foreach ($admins as $admin) {
                $adminuser = new User();
                $adminuser->find_by_id($admin);
                email_user($adminuser, null,
                    get_string('pendingregistrationadminemailsubject', 'auth.internal', $institution->displayname, get_config('sitename')),
                    get_string('pendingregistrationadminemailtext', 'auth.internal',
                        $adminuser->firstname, $institution->displayname, $pendingregistrationslink,
                        $fullname, $values['email'], $values['reason'], get_config('sitename')),
                    get_string('pendingregistrationadminemailhtml', 'auth.internal',
                        $adminuser->firstname, $institution->displayname, $pendingregistrationslink, $pendingregistrationslink,
                        $fullname, $values['email'], $values['reason'], get_config('sitename'))
                    );
            }
            email_user($user, null,
                get_string('approvalemailsubject', 'auth.internal', get_config('sitename')),
                get_string('approvalemailmessagetext', 'auth.internal', $values['firstname'], get_config('sitename'), get_config('sitename')),
                get_string('approvalemailmessagehtml', 'auth.internal', $values['firstname'], get_config('sitename'), get_config('sitename')));
            $_SESSION['registeredokawaiting'] = true;
        }
        else {
            email_user($user, null,
                get_string('registeredemailsubject', 'auth.internal', get_config('sitename')),
                get_string('registeredemailmessagetext', 'auth.internal', $values['firstname'], get_config('sitename'), get_config('wwwroot'), $values['key'], get_config('sitename')),
                get_string('registeredemailmessagehtml', 'auth.internal', $values['firstname'], get_config('sitename'), get_config('wwwroot'), $values['key'], get_config('wwwroot'), $values['key'], get_config('sitename')));
            // Add a marker in the session to say that the user has registered
            $_SESSION['registered'] = true;
        }
    }
    catch (EmailException $e) {
        log_warn($e);
        die_info(get_string('registrationunsuccessful', 'auth.internal'));
    }
    catch (SQLException $e) {
        log_warn($e);
        die_info(get_string('registrationunsuccessful', 'auth.internal'));
    }

    redirect('/register.php');
}

$registerdescription = get_string('registerwelcome');
if ($registerterms) {
    $registerdescription .= ' ' . get_string('registeragreeterms');
}
$registerdescription .= ' ' . get_string('registerprivacy');

// The javascript needs to refer to field names, but they are obfuscated in this form,
// so construct and build the form in separate steps, so we can get the field names.
$form = new Pieform($form);
$institutionid = 'register_' . $form->hashedfields['institution'];
$reasonid = 'register_' . $form->hashedfields['reason'];
$formhtml = $form->build();

$js = '
var registerconfirm = ' . json_encode($registerconfirm) . ';
$j(function() {
    $j("#' . $institutionid . '").change(function() {
        if (this.value && registerconfirm[this.value] == 1) {
            $j("#' . $reasonid . '_container").removeClass("js-hidden");
            $j("#' . $reasonid . '_container textarea").removeClass("js-hidden");
            $j("#' . $reasonid . '_container").next("tr.textarea").removeClass("js-hidden");
        }
        else {
            $j("#' . $reasonid . '_container").addClass("js-hidden");
            $j("#' . $reasonid . '_container textarea").addClass("js-hidden");
            $j("#' . $reasonid . '_container").next("tr.textarea").addClass("js-hidden");
        }
    });
});
';

$smarty = smarty(array('jquery'));
$smarty->assign('register_form', $formhtml);
$smarty->assign('registerdescription', $registerdescription);
if ($registerterms) {
    $smarty->assign('termsandconditions', get_site_page_content('termsandconditions'));
}
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('register.tpl');
