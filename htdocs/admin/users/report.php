<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Richard Mansfield
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALSTAFF', 1);
define('MENUITEM', 'configusers');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require(get_config('docroot') . 'lib/antispam.php');

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
    'loginaslog' => array(
        'id'   => 'loginaslog',
        'name' => get_string('loginaslog', 'admin'),
    ),
);

$selected = 'users';
foreach (array_keys($tabs) as $t) {
    if (param_variable('report:' . $t, false)) {
        $selected = $t;
    }
}
$tabs[$selected]['selected'] = true;

if ($selected == 'users') {
    define('SUBSECTIONHEADING', get_string('users'));
}
else if ($selected == 'accesslist') {
    define('SUBSECTIONHEADING', get_string('accesslist', 'view'));
}
else if ($selected == 'loginaslog') {
    define('SUBSECTIONHEADING', get_string('loginaslog', 'admin'));
}

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
        aru.remoteusername AS remoteuser, u.lastlogin, u.probation
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
    $smarty->assign('users', $users);
    $smarty->assign('USER', $USER);
    $userlisthtml = $smarty->fetch('admin/users/userlist.tpl');

    if ($USER->get('admin') || $USER->is_institutional_admin()) {
        $csvfields = array('username', 'email', 'firstname', 'lastname', 'studentid', 'preferredname', 'remoteuser', 'lastlogin');
    }
    else {
        $csvfields = array('username', 'firstname', 'lastname', 'studentid', 'preferredname', 'lastlogin');
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
    $smarty->assign('users', $users);
    $smarty->assign('USER', $USER);
    $userlisthtml = $smarty->fetch('admin/users/accesslists.tpl');
}
else if ($selected == 'loginaslog') {
    $ph = array_merge($userids, $userids);
    $log = get_records_sql_array('
        SELECT *
        FROM {event_log}
        WHERE (usr IN (' . join(',', array_fill(0, count($userids), '?')) . ')
           OR realusr IN (' . join(',', array_fill(0, count($userids), '?')) . '))
          AND event = \'loginas\'

        ORDER BY time DESC',
        $ph
    );
    if (empty($log)) {
        $log = array();
    }
    foreach($log as $l) {
        $l->data = json_decode($l->data);
        foreach(array('usr', 'realusr') as $f) {
            $l->{$f . 'name'} = display_name($l->{$f});
        }
    }
    if (!in_array(get_config('eventloglevel'), array('masq', 'all'))) {
      $note = get_string('masqueradingnotloggedwarning', 'admin', get_config('wwwroot'));
    }
    else {
      $note = false;
    }
    $smarty = smarty_core();
    $smarty->assign('log', $log);
    $smarty->assign('USER', $USER);
    $smarty->assign('note', $note);
    $userlisthtml = $smarty->fetch('admin/users/loginaslog.tpl');
}

$smarty = smarty();
$smarty->assign('sectiontabs', $tabs);
$smarty->assign('users', $users);
$smarty->assign('userlisthtml', $userlisthtml);
$smarty->assign('csv', isset($csv));
$smarty->display('admin/users/report.tpl');
