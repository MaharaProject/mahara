<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2012 Catalyst IT Ltd and others; see:
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
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALSTAFF', 1);
define('MENUITEM', 'configusers');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

define('TITLE', get_string('userreports', 'admin'));

$tabs = array(
    'users' => array(
        'id'   => 'users',
        'name' => get_string('users'),
    ),
    'accesslist' => array(
        'id'   => 'accesslist',
        'name' => get_string('accesslist', 'view'),
    ),
);

$selected = 'users';
foreach (array_keys($tabs) as $t) {
    if (param_variable('report:' . $t, false)) {
        $selected = $t;
    }
}
$tabs[$selected]['selected'] = true;

$userids = array_map('intval', param_variable('users'));

$ph = $userids;
$institutionsql = '';

if (!$USER->get('admin') && !$USER->get('staff')) {
    // Filter the users by the user's admin/staff institutions
    $institutions = $USER->get('admininstitutions');
    if (get_config('staffreports')) {
        $institutions = array_merge($institutions, $USER->get('staffinstitutions'));
    }
    $institutions = array_values($institutions);
    $ph = array_merge($ph, $institutions);
    $institutionsql = '
            AND id IN (
                SELECT usr FROM {usr_institution} WHERE institution IN (' . join(',', array_fill(0, count($institutions), '?')) . ')
            )';
}

$users = get_records_sql_assoc('
    SELECT
        u.id, u.username, u.email, u.firstname, u.lastname, u.studentid, u.preferredname, u.urlid,
        aru.remoteusername AS remoteuser
    FROM {usr} u
        LEFT JOIN {auth_remote_user} aru ON u.id = aru.localusr AND u.authinstance = aru.authinstance
    WHERE id IN (' . join(',', array_fill(0, count($userids), '?')) . ')
        AND deleted = 0' . $institutionsql . '
    ORDER BY ' . ($selected == 'users' ? 'username' : 'lastname, firstname, id'),
    $ph
);

if (!get_config('staffreports')) {
    // Display the number of users filtered out due to institution permissions.  This is not an exception, because the
    // logged in user might be an admin in one institution, and staff in another.
    if ($uneditableusers = count($userids) - count($users)) {
        $SESSION->add_info_msg(get_string('uneditableusers', 'admin', $uneditableusers));
    }
}

if ($users && !$USER->get('admin')) {
    // Remove email & remoteuser when viewed by staff
    $admininstitutions = $USER->get('admininstitutions');
    if (empty($admininstitutions)) {
        $myusers = array();
    }
    else {
        $ph = array_merge($userids, array_values($admininstitutions));
        $myusers = get_records_sql_assoc('
            SELECT id,id FROM {usr}
            WHERE id IN (' . join(',', array_fill(0, count($userids), '?')) . ')
                AND deleted = 0
                AND id IN (
                    SELECT usr FROM {usr_institution}
                    WHERE institution IN (' . join(',', array_fill(0, count($admininstitutions), '?')) . ')
                )',
            $ph
        );
    }
    foreach ($users as $u) {
        if (!isset($myusers[$u->id])) {
            $u->email = null;
            $u->remoteuser = null;
            $u->hideemail = true;
        }
    }
}

$userids = array_keys($users);

if ($selected == 'users') {
    $smarty = smarty_core();
    $smarty->assign_by_ref('users', $users);
    $smarty->assign_by_ref('USER', $USER);
    $userlisthtml = $smarty->fetch('admin/users/userlist.tpl');

    if ($USER->get('admin') || $USER->is_institutional_admin()) {
        $csvfields = array('username', 'email', 'firstname', 'lastname', 'studentid', 'preferredname', 'remoteuser');
    }
    else {
        $csvfields = array('username', 'firstname', 'lastname', 'studentid', 'preferredname');
    }

    $USER->set_download_file(generate_csv($users, $csvfields), 'users.csv', 'text/csv');
    $csv = true;
}
else if ($selected == 'accesslist') {
    require_once(get_config('libroot') . 'view.php');
    $accesslists = View::get_accesslists(array_keys($users));
    foreach ($accesslists['collections'] as $k => $c) {
        if (!isset($users[$c['owner']]->collections)) {
            $users[$c['owner']]->collections = array();
        }
        $users[$c['owner']]->collections[$k] = $c;
    }
    foreach ($accesslists['views'] as $k => $v) {
        if (!isset($users[$v['owner']]->views)) {
            $users[$v['owner']]->views = array();
        }
        $users[$v['owner']]->views[$k] = $v;
    }
    $smarty = smarty_core();
    $smarty->assign_by_ref('users', $users);
    $smarty->assign_by_ref('USER', $USER);
    $userlisthtml = $smarty->fetch('admin/users/accesslists.tpl');
}

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('tabs', $tabs);
$smarty->assign('users', $users);
$smarty->assign('userlisthtml', $userlisthtml);
$smarty->assign('csv', isset($csv));
$smarty->display('admin/users/report.tpl');

