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
define('SECTION_PAGE', 'actiondeletion');
require_once('institution.php');

$id = param_integer('d');
$action = param_alpha('action');

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

if (!$deletion = get_record_select('usr_pendingdeletion', '"id" = ?', array($id))) {
    die_info(get_string('userdeletionnosuchid', 'auth.internal'));
}

$usertodelete = new User();
$usertodelete->find_by_id($deletion->usr);

if ($action == 'approve') {
    $message = get_string('approveuserdeletionmessage', 'admin', $usertodelete->username);
    $submitbtn = get_string('approve', 'admin');
    define('TITLE', get_string('approveuserdeletionfor', 'admin',
        $usertodelete->firstname, $usertodelete->lastname, $usertodelete->email));
}
else {
    $message = get_string('denyuserdeletionmessage', 'admin');
    $submitbtn = get_string('deny', 'admin');
    define('TITLE', get_string('denyuserdeletionfor', 'admin',
        $usertodelete->firstname, $usertodelete->lastname));
    $elements['message'] = array(
        'type'  => 'textarea',
        'title' => get_string('deletiondeniedreason', 'admin'),
        'description' => get_string('deletiondeniedreasondesc', 'admin'),
        'cols' => 50,
        'rows' => 10,
    );
}
foreach ((array)$deletion as $key => $value) {
    $elements[$key] = array(
        'type'  => 'hidden',
        'value' => $value,
    );
}
$elements['submit'] = array(
    'type' => 'submitcancel',
    'value' => array($submitbtn, get_string('cancel')),
    'class' => 'btn-primary',
    'goto' => get_config('wwwroot') . 'admin/users/pendingdeletions.php'
);

$form = pieform(array(
    'name' => $action.'deletion',
    'autofocus' => false,
    'method' => 'post',
    'elements' => $elements,
));

$smarty = smarty();
$smarty->assign('message', $message);
$smarty->assign('form', $form);
$smarty->display('admin/users/actiondeletion.tpl');

function denydeletion_submit(Pieform $form, $values) {
    global $USER, $SESSION, $deletion, $usertodelete;

    if (isset($values['message']) && !empty($values['message'])) {
        $message = get_string('userdeletiondeniedmessagereason', 'auth.internal',
            $usertodelete->firstname, get_config('sitename'), $values['message'], display_name($USER));
    }
    else {
        $message = get_string('userdeletiondeniedmessage', 'auth.internal',
            $usertodelete->firstname, get_config('sitename'), display_name($USER));
    }
    try {
        delete_records('usr_pendingdeletion', 'id', $values['id']);

        email_user($usertodelete, $USER,
            get_string('userdeletiondeniedemailsubject', 'auth.internal', get_config('sitename')),
            $message
        );
    }
    catch (EmailException $e) {
        log_warn($e);
        die_info(get_string('userdeletiondeniedunsuccessful', 'admin'));
    }
    catch (SQLException $e) {
        log_warn($e);
        die_info(get_string('userdeletiondeniedunsuccessful', 'admin'));
    }

    $SESSION->add_ok_msg(get_string('userdeletiondeniedsuccessful', 'admin'));
    redirect('/admin/users/pendingdeletions.php');
}

function approvedeletion_submit(Pieform $form, $values) {
    global $SESSION, $usertodelete, $USER;

    // cant delete the last site admin
    $admins = get_site_admins();
    $lastadminid = 0;
    if (count($admins)== 1) {
        $lastadminid = $admins[0]->id;
    }

    $usercanbedeleted = $candeleteuser = false;
    // Check if user can be deleted
    if (isset($values['id']) && isset($values['usr'])
        && ($values['usr'] != 0)
        && ($values['usr'] != $USER->get('id'))
        && ($values['usr'] != $lastadminid)
        && ($usrdeletion = get_record('usr_pendingdeletion', 'id', $values['id']))
        && ($usrdeletion->usr == $values['usr'])) {
        $usercanbedeleted = true;
    }

    if ($usercanbedeleted) {
        // Now check if we are allowed to delete them
        $userinstitutions = $usertodelete->get('institutions');
        if (empty($userinstitutions) && $USER->get('admin')) {
            // we are only in 'mahara' institution so can only be deleted by site admins
            $candeleteuser = true;
        }
        else {
            foreach ($userinstitutions as $i) {
                if ($USER->can_edit_institution($i->institution)) {
                    // If $USER can edit any of the institutions that the $user belongs then they are allowed to delete the user
                    $candeleteuser = true;
                    break;
                }
            }
        }
    }

    if ($usercanbedeleted && $candeleteuser) {
        delete_records('usr_pendingdeletion', 'id', $values['id']);

        //delete user account
        delete_user($values['usr']);

        // send the user the official account deletion email
        email_user(
                $usertodelete,
                null,
                get_string('userdeletionemailsubject', 'auth.internal', get_config('sitename')),
                get_string(
                        'userdeletionemailmessagetext',
                        'auth.internal',
                        $usertodelete->firstname,
                        get_config('sitename'),
                        get_config('sitename')
                ),
                get_string(
                        'userdeletionemailmessagehtml',
                        'auth.internal',
                        $usertodelete->firstname,
                        get_config('sitename'),
                        get_config('sitename')
                )
        );

        $SESSION->add_ok_msg(get_string('deletionapprovedsuccessfully', 'admin'));
    }
    else {
        $SESSION->add_error_msg(get_string('deletionapprovedfailed', 'admin'));
    }
    redirect('/admin/users/pendingdeletions.php');
}
