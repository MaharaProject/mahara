<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Stacey Walker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'actionregistration');
require_once('institution.php');

$id = param_integer('r');
$action = param_alpha('action');

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

if (!$registration = get_record_select('usr_registration', '"id" = ? AND pending = 1', array($id))) {
    die_info(get_string('registrationnosuchid', 'auth.internal'));
}
if (!$inst = get_record('institution', 'name', $registration->institution)) {
    die_info(get_string('nosuchinstitution', 'admin'));
}

if ($action == 'approve') {
    $message = get_string('approveregistrationmessage', 'admin', $inst->displayname);
    $submitbtn = get_string('approve', 'admin');
    define('TITLE', get_string('approveregistrationfor2', 'admin', $registration->firstname, $registration->lastname, $registration->email));
    if ($registration->institution != 'mahara') {
        $elements['institutionstaff'] = array(
            'type'         => 'switchbox',
            'title'        => get_string('institutionstaff', 'admin'),
            'description'  => get_string('makeuserinstitutionstaff', 'admin'),
            'defaultvalue' => 0,
        );
    }
}
else {
    $message = get_string('denyregistrationmessage', 'admin');
    $submitbtn = get_string('deny', 'admin');
    define('TITLE', get_string('denyregistrationfor', 'admin', $registration->firstname, $registration->lastname));
    $elements['message'] = array(
        'type'  => 'textarea',
        'title' => get_string('registrationdeniedreason', 'admin'),
        'description' => get_string('registrationdeniedreasondesc', 'admin'),
        'cols' => 50,
        'rows' => 10,
    );
}
foreach ((array)$registration as $key => $value) {
    $elements[$key] = array(
        'type'  => 'hidden',
        'value' => $value,
    );
}
$elements['submit'] = array(
    'type' => 'submitcancel',
    'value' => array($submitbtn, get_string('cancel')),
    'class' => 'btn-primary',
    'goto' => get_config('wwwroot') . 'admin/users/pendingregistrations.php?institution='.$inst->name,
);

$form = pieform(array(
    'name' => $action.'registration',
    'autofocus' => false,
    'method' => 'post',
    'elements' => $elements,
));

$smarty = smarty();
$smarty->assign('message', $message);
$smarty->assign('form', $form);
$smarty->display('admin/users/actionregistration.tpl');

function denyregistration_submit(Pieform $form, $values) {
    global $USER, $SESSION;

    if (isset($values['message']) && !empty($values['message'])) {
        $message = get_string('registrationdeniedmessagereason', 'auth.internal',
            $values['firstname'], get_config('sitename'), $values['message'], display_name($USER));
    }
    else {
        $message = get_string('registrationdeniedmessage', 'auth.internal',
            $values['firstname'], get_config('sitename'), display_name($USER));
    }
    try {
        delete_records('usr_registration', 'email', $values['email']);

        $user = (object) $values;
        $user->admin = 0;
        $user->staff = 0;
        email_user($user, $USER,
            get_string('registrationdeniedemailsubject', 'auth.internal', get_config('sitename')),
            $message
        );
    }
    catch (EmailException $e) {
        log_warn($e);
        die_info(get_string('registrationdeniedunsuccessful', 'admin'));
    }
    catch (SQLException $e) {
        log_warn($e);
        die_info(get_string('registrationdeniedunsuccessful', 'admin'));
    }

    $SESSION->add_ok_msg(get_string('registrationdeniedsuccessful', 'admin'));
    redirect('/admin/users/pendingregistrations.php?institution='.$values['institution']);
}

function approveregistration_submit(Pieform $form, $values) {
    global $SESSION;

    if (!empty($values['extra'])) {
        // The local_register_submit hook may have been used to put other values in
        // this column; if so, leave them in the db.
        $extra = unserialize($values['extra']);
    }
    $extra = (!empty($extra) && $extra instanceof Stdclass) ? $extra : new StdClass;

    // Get additional values to pass through to user creation
    if (!empty($values['institutionstaff'])) {
        $extra->institutionstaff = 1;
    }
    $values['extra'] = serialize($extra);

    // update expiry time and set pending to a value that identify
    // it as approved (2)
    $values['pending'] = 2;
    $values['expiry'] = db_format_timestamp(time() + 86400); // now + 1 day
    update_record('usr_registration', $values, array('email' => $values['email']));

    // send the user the official account completion email
    $user = new stdClass();
    $user->firstname = $values['firstname'];
    $user->lastname = $values['lastname'];
    $user->email = $values['email'];
    email_user(
            $user,
            null,
            get_string('registeredemailsubject', 'auth.internal', get_config('sitename')),
            get_string(
                    'registeredemailmessagetext',
                    'auth.internal',
                    $user->firstname,
                    get_config('sitename'),
                    get_config('wwwroot'),
                    $values['key'],
                    get_config('sitename')
            ),
            get_string(
                    'registeredemailmessagehtml',
                    'auth.internal',
                    $user->firstname,
                    get_config('sitename'),
                    get_config('wwwroot'),
                    $values['key'],
                    get_config('wwwroot'),
                    $values['key'],
                    get_config('sitename')
            )
    );

    $SESSION->add_ok_msg(get_string('registrationapprovedsuccessfully', 'admin'));
    redirect('/admin/users/pendingregistrations.php?institution=' . $values['institution']);
}
