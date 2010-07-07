<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2010 Catalyst IT Ltd and others; see:
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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2010 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'groups');
define('TITLE', 'Groups');
define('MENUITEM', 'managegroups/groups');

if (!$USER->get('admin')) {
    //User not an admin, redirect away
    redirect(get_config('wwwroot'));
}

$groups = get_records_sql_array(
    "SELECT g.id,g.name,g.grouptype,g.jointype,g.public AS visible,
    (SELECT COUNT(*) FROM {group_member} gm WHERE gm.group=g.id) AS members,
    (SELECT COUNT(*) FROM {group_member} gm WHERE gm.group=g.id AND gm.role='admin') AS admins
    FROM {group} g WHERE g.deleted = 0 ORDER BY g.id DESC", array()
);
foreach ($groups as &$group) {
    $group->type = get_string('name', 'grouptype.' . $group->grouptype) . ', ' . get_string('membershiptype.'.$group->jointype, 'group');
    $group->visible = $group->visible ? 'Public' : 'Private';
}

$smarty = smarty();
$smarty->assign('groups', $groups);
$smarty->assign('PAGEHEADING', get_string('administergroups', 'admin'));
$smarty->assign('siteadmin', true);
$smarty->display('admin/groups/groups.tpl');

?>
