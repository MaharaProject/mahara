<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'settings/institutions');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'migrateinstitution');
require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('institutionmembership'));
define('SUBSECTIONHEADING', get_string('selfmigration', 'mahara'));

$key = param_alphanum('key');
$token = param_alphanum('token');

$migrate_record = get_record('usr_institution_migrate', 'key', $key, 'token', $token);
if ($USER->get('id') != $migrate_record->usr) {
    // Only migrating user can complete migration so need to be logged in as that user
    $USER->logout();
    $SESSION->add_error_msg(get_string('cannotcompletemigrationwithuser'));
    redirect(get_config('wwwroot'));
}

$error = $errorlink = null;
$message = null;
$form = null;
$formmessage = null;
if (!$migrate_record || (strtotime($migrate_record->ctime) < strtotime('-30 mins'))) {
    $error = get_string('invalidkeytoken');
    $errorlink = get_string('restartmigration', 'mahara', get_config('wwwroot') . 'account/migrateinstitution.php');
    delete_records('usr_institution_migrate', 'id', $migrate_record->id);
}
else {
    // We are wanting to migrate the user so show the confirm form
    $form = array(
        'name' => 'migrateconfirm',
        'elements' => array(
            'key' => array(
                'type' => 'hidden',
                'value' => $key,
            ),
            'token' => array(
                'type' => 'hidden',
                'value' => $token,
            ),
            'submit' => array(
                'type' => 'submitcancel',
                'subclass' => array('btn-primary'),
                'goto' => get_config('wwwroot') . 'account/migrateinstitutionconfirm.php?key=' . $key . '&token=' . $token,
                'value' => array(get_string('confirmmigration'), get_string('cancel')),
            ),
        ),
    );
    $form = pieform($form);
    $toauth = get_record('auth_instance', 'id', $migrate_record->new_authinstance);
    $toinstitutionname = get_field('institution', 'displayname', 'name', $toauth->institution);
    $fromauth = get_record('auth_instance', 'id', $migrate_record->old_authinstance);
    $frominstitutionname = get_field('institution', 'displayname', 'name', $fromauth->institution);
    $formmessage = get_string('migrateaccountconfirminfo', 'mahara', $frominstitutionname, $toinstitutionname);
}

function migrateconfirm_cancel_submit(Pieform $form) {
    global $migrate_record, $SESSION;
    $SESSION->set('postmigrateresponse', false);
    delete_records('usr_institution_migrate', 'id', $migrate_record->id);
    $SESSION->add_ok_msg(get_string('migrationcancelled'));
    redirect(get_config('wwwroot') . 'account/migrateinstitution.php');
}

function migrateconfirm_submit(Pieform $form, $values) {
    global $USER, $SESSION, $migrate_record;

    $user = new User();
    $user->find_by_id($migrate_record->usr);

    $toauth = get_record('auth_instance', 'id', $migrate_record->new_authinstance);
    $fromauth = get_record('auth_instance', 'id', $migrate_record->old_authinstance);
    $toinstitutionname = get_field('institution', 'displayname', 'name', $toauth->institution);
    if ($fromauth->institution != 'mahara') {
        $user->leave_institution($fromauth->institution);
    }
    if ($toauth->institution != 'mahara') {
        $user->join_institution($toauth->institution);
    }
    $user->authinstance = $migrate_record->new_authinstance;
    $user->commit();
    set_user_primary_email($user->get('id'), $migrate_record->email);
    // Add username to auth_remote_user table but make sure this user doesn't have an existing remoteuser link
    // and make sure the IdP username doesn't already have a link to another authinstance
    delete_records('auth_remote_user', 'authinstance', $migrate_record->old_authinstance, 'localusr', $migrate_record->usr);
    delete_records('auth_remote_user', 'remoteusername', $migrate_record->new_username);
    ensure_record_exists('auth_remote_user',
                         (object) array('authinstance' => $migrate_record->new_authinstance,
                                        'localusr' => $migrate_record->usr),
                         (object) array('authinstance' => $migrate_record->new_authinstance,
                                        'remoteusername' => $migrate_record->new_username,
                                        'localusr' => $migrate_record->usr)
                         );
    delete_records('usr_institution_migrate', 'id', $migrate_record->id);
    $USER->logout();
    $SESSION->add_ok_msg(get_string('migratesuccess', 'mahara', $toinstitutionname));
    redirect(get_config('wwwroot'));
}

$smarty = smarty();
setpageicon($smarty, 'icon-university');
$smarty->assign('message', $message);
$smarty->assign('error', $error);
$smarty->assign('errorlink', $errorlink);
$smarty->assign('confirmform', $form);
$smarty->assign('sitename', get_config('sitename'));
$smarty->assign('confirmforminfo', $formmessage);
$smarty->assign('SUBPAGENAV', account_institution_get_menu_tabs());
$smarty->display('account/migrateinstitutionconfirm.tpl');