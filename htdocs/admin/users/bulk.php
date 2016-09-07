<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Richard Mansfield
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'configusers');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . 'lib/antispam.php');

define('TITLE', get_string('bulkactions', 'admin'));

$userids = array_map('intval', param_variable('users'));

$ph = $userids;
$institutionsql = '';

if (!$USER->get('admin')) {
    // Filter the users by the admin's institutions
    $institutions = array_values($USER->get('admininstitutions'));
    $ph = array_merge($ph, $institutions);
    $institutionsql = '
            AND id IN (
                SELECT usr FROM {usr_institution} WHERE institution IN (' . join(',', array_fill(0, count($institutions), '?')) . ')
            )';
}

$users = get_records_sql_assoc('
    SELECT
        u.id, u.username, u.email, u.firstname, u.lastname, u.suspendedcusr, u.authinstance, u.studentid,
        u.preferredname, CHAR_LENGTH(u.password) AS haspassword, aru.remoteusername AS remoteuser, u.lastlogin,
        u.probation
    FROM {usr} u
        LEFT JOIN {auth_remote_user} aru ON u.id = aru.localusr AND u.authinstance = aru.authinstance
    WHERE id IN (' . join(',', array_fill(0, count($userids), '?')) . ')
        AND deleted = 0' . $institutionsql . '
    ORDER BY username',
    $ph
);

// Display the number of users filtered out due to institution permissions.  This is not an
// exception, because the logged in user might be an admin in one institution, and staff in
// another.
if ($uneditableusers = count($userids) - count($users)) {
    $SESSION->add_info_msg(get_string('uneditableusers', 'admin', $uneditableusers));
}

$userids = array_keys($users);

// Hidden drop-down to submit the list of users back to this page.
// Used in all three forms
$userelement = array(
    'type'     => 'select',
    'class'    => 'hidden',
    'multiple' => 'true',
    'options'  => array_combine($userids, $userids),
    'value'    => $userids,
);

// Change authinstance
if ($USER->get('admin')) {
    $authinstances = auth_get_auth_instances();
}
else {
    $admininstitutions = $USER->get('admininstitutions');
    $authinstances = auth_get_auth_instances_for_institutions($admininstitutions);
}

$options = array();
$default = null;
foreach ($authinstances as $authinstance) {
    $options[$authinstance->id] = $authinstance->displayname. ': '.$authinstance->instancename;
    if (!$default && $authinstance->name == 'mahara') {
        $default = $authinstance->id;
    }
}

// Suspend users
$suspendform = pieform(array(
    'name'     => 'suspend',
    'class'    => 'bulkactionform form-inline form-as-button',
    'renderer' => 'div',
    'elements' => array(
        'users' => $userelement,
        'suspendgroup' => array(
            'type' => 'fieldset',
            'class' => 'input-group',
            'elements' => array (
                'reason' => array(
                    'type'        => 'text',
                    'class'       => 'input-small',
                    'title'       => get_string('suspendedreason', 'admin') . ': ',
                ),
                'suspend' => array(
                    'type'        => 'button',
                    'usebuttontag' => true,
                    'class'       => 'btn-default input-group-btn no-label',
                    'value'       => get_string('Suspend', 'admin'),
                )
            )
        )
    ),
));

// Change authentication method
$changeauthform = null;
if (count($options) > 1) {
    $changeauthform = pieform(array(
        'name'           => 'changeauth',
        'class'          => 'bulkactionform form-inline form-as-button',
        'renderer'       => 'div',
        'dieaftersubmit' => false,
        'elements'       => array(
            'users' => $userelement,
            'authgroup' => array(
                'type' => 'fieldset',
                'class' => 'input-group',
                'elements' => array (
                    'authinstance' => array(
                        'type'         => 'select',
                        'options'      => $options,
                        'defaultvalue' => $default,
                    ),
                    'changeauth' => array(
                        'type'        => 'button',
                        'usebuttontag' => true,
                        'class'       => 'btn-default input-group-btn',
                        'value'        => get_string('changeauthmethod', 'admin')
                    )
                )
            )
        ),
    ));
}

// Set probation points
$probationform = null;
if (is_using_probation()) {
    $probationform = pieform(array(
        'name' => 'probation',
        'class' => 'bulkactionform form-inline form-as-button',
        'renderer' => 'div',
        'elements' => array(
            'users' => $userelement,
            'spamgroup' => array(
                'type' => 'fieldset',
                'class' => 'input-group',
                'elements' => array (
                    'probationpoints' => array(
                        'type' => 'select',
                        'title' => get_string('probationbulksetspamprobation', 'admin') . ': ',
                        'options' => probation_form_options(),
                        'defaultvalue' => '0',
                    ),
                    'setprobation' => array(
                        'type' => 'button',
                        'usebuttontag' => true,
                        'class'       => 'btn-default input-group-btn no-label',
                        'confirm' => get_string('probationbulkconfirm', 'admin'),
                        'value' => get_string('probationbulkset', 'admin'),
                    )
                )
            )
        ),
    ));
}

// Delete users
$deleteform = pieform(array(
    'name'     => 'delete',
    'class'    => 'bulkactionform delete form-inline form-as-button',
    'renderer' => 'div',
    'elements' => array(
        'users' => $userelement,
        'delete' => array(
            'type'        => 'button',
            'usebuttontag' => true,
            'class'       => 'btn-default',
            'confirm'     => get_string('confirmdeleteusers', 'admin'),
            'value'       => '<span class="icon icon-lg icon-user-times left text-danger" role="presentation" aria-hidden="true"></span>' . get_string('deleteusers', 'admin'),
        ),
    ),
));

$smarty = smarty();
$smarty->assign('users', $users);
$smarty->assign('changeauthform', $changeauthform);
$smarty->assign('suspendform', $suspendform);
$smarty->assign('deleteform', $deleteform);
$smarty->assign('probationform', $probationform);
$smarty->display('admin/users/bulk.tpl');

function changeauth_validate(Pieform $form, $values) {
    global $userids, $SESSION;

    // Make sure all users are members of the institution that
    // this authinstance belongs to.
    $authobj = AuthFactory::create($values['authinstance']);

    if ($authobj->institution != 'mahara') {
        $ph = $userids;
        $ph[] = $authobj->institution;
        $institutionusers = count_records_sql('
            SELECT COUNT(usr)
            FROM {usr_institution}
            WHERE usr IN (' . join(',', array_fill(0, count($userids), '?')) . ') AND institution = ?',
            $ph
        );
        if ($institutionusers != count($userids)) {
            $SESSION->add_error_msg(get_string('someusersnotinauthinstanceinstitution', 'admin'));
            $form->set_error('authinstance', get_string('someusersnotinauthinstanceinstitution', 'admin'));
        }
    }
}

function changeauth_submit(Pieform $form, $values) {
    global $users, $SESSION, $authinstances, $USER;

    $newauth = AuthFactory::create($values['authinstance']);
    $needspassword = method_exists($newauth, 'change_password');

    $updated = 0;
    $needpassword = 0;

    db_begin();

    $newauthinst = get_records_select_assoc('auth_instance', 'id = ?', array($values['authinstance']));
    if ($USER->get('admin') || $USER->is_institutional_admin($newauthinst[$values['authinstance']]->institution)) {
        foreach ($users as $user) {
            if ($user->authinstance != $values['authinstance']) {
                // Authinstance can be changed by institutional admins if both the
                // old and new authinstances belong to the admin's institutions
                $authinst = get_field('auth_instance', 'institution', 'id', $user->authinstance);
                if ($USER->get('admin') || $USER->is_institutional_admin($authinst)) {
                    // determine the current remoteusername
                    $current_remotename = get_field('auth_remote_user', 'remoteusername',
                                                    'authinstance', $user->authinstance, 'localusr', $user->id);
                    if (!$current_remotename) {
                        $current_remotename = $user->username;
                    }
                    // remove row if new authinstance row already exists to avoid doubleups
                    delete_records('auth_remote_user', 'authinstance', $values['authinstance'], 'localusr', $user->id);
                    insert_record('auth_remote_user', (object) array(
                        'authinstance'   => $values['authinstance'],
                        'remoteusername' => $current_remotename,
                        'localusr'       => $user->id,
                    ));
                }

                if ($user->haspassword && !$needspassword) {
                    $user->password = '';
                }
                else if ($needspassword && !$user->haspassword) {
                    $needpassword++;
                }

                $user->authinstance = $values['authinstance'];
                update_record('usr', $user, 'id');
                $updated++;
            }
        }
    }

    db_commit();

    if ($needpassword) {
        // Inform the user that they may need to reset passwords
        $SESSION->add_info_msg(get_string('bulkchangeauthmethodresetpassword', 'admin', $needpassword));
    }
    $message = get_string('bulkchangeauthmethodsuccess', 'admin', $updated);
    $form->reply(PIEFORM_OK, array('message' => $message));
}

function suspend_submit(Pieform $form, $values) {
    global $users, $SESSION;

    $suspended = 0;

    db_begin();

    foreach ($users as $user) {
        if (!$user->suspendedcusr) {
            suspend_user($user->id, $values['reason']);
            $suspended++;
        }
    }

    db_commit();

    $SESSION->add_ok_msg(get_string('bulksuspenduserssuccess', 'admin', $suspended));
    redirect('/admin/users/suspended.php');
}

function delete_validate(Pieform $form, $values) {
    global $SESSION, $USER;
    $users = $values['users'];
    // Not allowed to bulk delete yourself
    if (is_array($users) && in_array($USER->get('id'), $users)) {
        $form->set_error(null, get_string('unabletodeleteself', 'admin'));
    }
    // Not allowed to remove all site admins
    $siteadmins = count_records_sql("SELECT COUNT(admin) FROM {usr}
                           WHERE id NOT IN (" . join(',', array_map('db_quote', $users)) . ") AND admin = 1", array());
    if (!$siteadmins) {
        $form->set_error(null, get_string('unabletodeletealladmins', 'admin'));
    }
}

function delete_submit(Pieform $form, $values) {
    global $users, $editable, $SESSION;

    db_begin();

    foreach ($users as $user) {
        delete_user($user->id);
    }

    db_commit();

    $SESSION->add_ok_msg(get_string('bulkdeleteuserssuccess', 'admin', count($users)));
    redirect('/admin/users/search.php');
}

function probation_submit(Pieform $form, $values) {
    global $SESSION, $users;

    $newpoints = ensure_valid_probation_points($values['probationpoints']);
    $paramlist = array($newpoints);

    $sql = '';
    foreach ($users as $user) {
        $paramlist[] = $user->id;
        $sql .= '?,';
    }
    // Drop the last comma
    $sql = substr($sql, 0, -1);

    execute_sql('update {usr} set probation = ? where id in (' . $sql . ')', $paramlist);

    $SESSION->add_ok_msg(get_string('bulkprobationpointssuccess', 'admin', count($users), $newpoints));
    redirect('/admin/users/search.php');
}
