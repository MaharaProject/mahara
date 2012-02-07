<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2011 Catalyst IT Ltd and others; see:
 *                    http://wiki.mahara.org/Contributors
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
 * @subpackage admin
 * @author     Richard Mansfield
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'configusers');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

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
        u.preferredname, CHAR_LENGTH(u.password) AS haspassword, aru.remoteusername AS remoteuser
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

$changeauthform = pieform(array(
    'name'           => 'changeauth',
    'class'          => 'bulkactionform',
    'renderer'       => 'oneline',
    'dieaftersubmit' => false,
    'elements'       => array(
        'users' => $userelement,
        'title'        => array(
            'type'         => 'html',
            'class'        => 'bulkaction-title',
            'value'        => get_string('changeauthmethod', 'admin') . ': ',
        ),
        'authinstance' => array(
            'type'         => 'select',
            'options'      => $options,
            'defaultvalue' => $default,
        ),
        'changeauth' => array(
            'type'         => 'submit',
            'value'        => get_string('submit'),
        ),
    ),
));

// Suspend users
$suspendform = pieform(array(
    'name'     => 'suspend',
    'class'    => 'bulkactionform',
    'renderer' => 'oneline',
    'elements' => array(
        'users' => $userelement,
        'title'        => array(
            'type'         => 'html',
            'class'        => 'bulkaction-title',
            'value'        => get_string('suspendusers', 'admin') . ': ',
        ),
        'suspend' => array(
            'type'        => 'submit',
            'value'       => get_string('Suspend', 'admin'),
        ),
        'reason' => array(
            'type'        => 'text',
            'title'       => ' ' . get_string('reason') . ': ',
        ),
    ),
));

// Delete users
$deleteform = pieform(array(
    'name'     => 'delete',
    'class'    => 'bulkactionform delete',
    'renderer' => 'oneline',
    'elements' => array(
        'users' => $userelement,
        'title'        => array(
            'type'         => 'html',
            'class'        => 'bulkaction-title',
            'value'        => get_string('deleteusers', 'admin') . ': ',
        ),
        'delete' => array(
            'type'        => 'submit',
            'confirm'     => get_string('confirmdeleteusers', 'admin'),
            'value'       => get_string('delete'),
        ),
    ),
));

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('users', $users);
$smarty->assign('changeauthform', $changeauthform);
$smarty->assign('suspendform', $suspendform);
$smarty->assign('deleteform', $deleteform);
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
    global $users, $SESSION, $authinstances;

    $newauth = AuthFactory::create($values['authinstance']);
    $needspassword = method_exists($newauth, 'change_password');

    $updated = 0;
    $needpassword = 0;

    db_begin();

    foreach ($users as $user) {
        if ($user->authinstance != $values['authinstance']) {

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
