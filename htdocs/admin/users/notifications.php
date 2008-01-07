<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configusers/adminnotifications');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('adminnotifications', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'notifications');

require_once('pieforms/pieform.php');

$sql = 'SELECT u.*, a.activity, a.method
    FROM {usr} u 
    LEFT JOIN {usr_activity_preference} a ON a.usr = u.id
    WHERE u.admin = ?';

$admins  = get_records_sql_array($sql, array(1));
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


$smarty = smarty();
$smarty->assign('users', $users);
$smarty->assign('types', $types);
$smarty->display('admin/users/notifications.tpl');


?>
