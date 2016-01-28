<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('adminnotifications', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'notifications');
define('MENUITEM', 'manageinstitutions/adminnotifications');

$sql = '
    SELECT
        u.id, u.username, u.firstname, u.lastname, u.preferredname, u.admin, u.staff, u.profileicon, u.email,
        a.activity, a.method
    FROM {usr} u
    LEFT JOIN {usr_activity_preference} a ON a.usr = u.id
    LEFT OUTER JOIN {usr_institution} ui
        ON (ui.usr = u.id' . ($USER->get('admin') ? '' : ' AND ui.institution IN ('
                              . join(',',array_map('db_quote', array_keys($USER->get('institutions')))) . ')') . ')
    WHERE u.deleted = 0
    GROUP BY
        u.id, u.username, u.firstname, u.lastname, u.preferredname, u.admin, u.staff, u.profileicon, u.email,
        a.activity, a.method
    HAVING (' . ($USER->get('admin') ? 'u.admin = 1 OR ' : '') . 'SUM(ui.admin) > 0)';

$admins  = get_records_sql_array($sql, array());
$temptypes   = get_records_array('activity_type', 'admin', 1);
$types   = array();
foreach ($temptypes as $t) {
    if (empty($t->plugintype)) {
        $section = 'activity';
    }
    else {
        $section = $t->plugintype . '.' . $t->pluginname;
    }
    $types[$t->id] = get_string('type' . $t->name, $section);
}
$users = array();
foreach ($admins as $u) {
    if (!array_key_exists($u->id, $users)) {
        $users[$u->id] = array('user' => $u,
                               'methods' => array());
        foreach (array_keys($types) as $key) {
            $users[$u->id]['methods'][$key] = '';
        }
    }
    if (!empty($u->method)) {
        $users[$u->id]['methods'][$u->activity] = get_string('name', 'notification.' . $u->method);
    }
}

$userinstitutions = get_records_sql_array('
    SELECT u.usr, i.name, i.displayname
    FROM {institution} i INNER JOIN {usr_institution} u ON i.name = u.institution
    WHERE u.usr IN (' . join(',', array_keys($users)) . ')', array());
if ($userinstitutions) {
    foreach ($userinstitutions as $ui) {
        $users[$ui->usr]['user']->institutions[] = $ui->displayname;
    }
}

$smarty = smarty();
setpageicon($smarty, 'icon-university');

$smarty->assign('users', $users);
$smarty->assign('types', $types);
$smarty->display('admin/users/notifications.tpl');
