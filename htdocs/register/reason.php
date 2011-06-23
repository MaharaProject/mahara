<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2011 Catalyst IT Ltd and others; see:
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
 * @author     Stacey Walker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2011 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'site');
define('SECTION_PAGE', 'registerreason');
require(dirname(dirname(__FILE__)) .  '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('register'));

$id = param_integer('r', null);

if (!session_id()) {
    session_start();
}

if (!empty($_SESSION['registeredokawaiting'])) {
    unset($_SESSION['registeredokawaiting']);
    die_info(get_string('registeredokawaitingemail', 'auth.internal'));
}

if (!$registration = get_record_select('usr_registration', '"id" = ? AND expiry >= ?', array($id, db_format_timestamp(time())))) {
    die_info(get_string('registrationnosuchkey', 'auth.internal'));
}

$elements = array(
    'reason' => array(
        'type' => 'textarea',
        'title' => get_string('registrationreason', 'auth.internal'),
        'description' => get_string('registrationreasondesc', 'auth.internal'),
        'rows' => 4,
        'cols' => 30,
        'rules' => array(
            'required' => true
        )
    ),
    'email' => array(
        'type' => 'hidden',
        'value' => $registration->email,
    ),
    'firstname' => array(
        'type' => 'hidden',
        'value' => $registration->firstname,
    ),
    'lastname' => array(
        'type' => 'hidden',
        'value' => $registration->lastname,
    ),
    'submit' => array(
        'type' => 'submitcancel',
        'confirm' => array(0 => null, 1 => get_string('confirmcancelregistration', 'auth.internal')),
        'name' => 'submit',
        'value' => array(get_string('completeregistration', 'auth.internal'), get_string('cancel')),
    )
);

$form = array(
    'name' => 'reason',
    'method' => 'post',
    'plugintype' => 'core',
    'pluginname' => 'register',
    'action' => '',
    'showdescriptiononerror' => false,
    'renderer' => 'table',
    'elements' => $elements,
);

function reason_submit(Pieform $form, $values) {
    global $SESSION;

    safe_require('auth', 'internal');
    $values['key']   = get_random_key();
    // @todo the expiry date should be configurable
    $values['expiry'] = db_format_timestamp(time() + 86400); //86400 is the number of seconds in 1 day
    $values['lang'] = $SESSION->get('lang');
    try {
        if (record_exists('usr_registration', 'email', $values['email'])) {
            update_record('usr_registration', $values, array('email' => $values['email']));
        }

        $user =(object) $values;
        $user->admin = 0;
        $user->staff = 0;
        email_user($user, null,
            get_string('confirmemailsubject', 'auth.internal', get_config('sitename')),
            get_string('confirmemailmessagetext', 'auth.internal', $values['firstname'], get_config('sitename'), get_config('wwwroot'), $values['key'], get_config('sitename')),
            get_string('confirmemailmessagehtml', 'auth.internal', $values['firstname'], get_config('sitename'), get_config('wwwroot'), $values['key'], get_config('wwwroot'), $values['key'], get_config('sitename')));
    }
    catch (EmailException $e) {
        log_warn($e);
        die_info(get_string('registrationunsuccessful', 'auth.internal'));
    }
    catch (SQLException $e) {
        log_warn($e);
        die_info(get_string('registrationunsuccessful', 'auth.internal'));
    }

    $_SESSION['registeredokawaiting'] = true;

    redirect('/register/reason.php');
}

function reason_cancel_submit() {
    global $SESSION, $registration;

    try {
        if (record_exists('usr_registration', 'id', $registration->id)) {
            delete_records('usr_registration', 'id', $registration->id);
        }
    }
    catch (SQLException $e) {
        log_warn($e);
        die_info(get_string('registrationcancellationunsuccessful', 'auth.internal'));
    }

    $_SESSION['registrationcancelled'] = true;

    redirect('/register.php');
}

$smarty = smarty();
$smarty->assign('register_form', pieform($form));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('register.tpl');
