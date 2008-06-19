<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups/mygroups');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('mygroups'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'group');
define('SECTION_PAGE', 'mygroups');
require('group.php');
$filter = param_alpha('filter', 'all');
$offset = param_integer('offset', 'all');

$groupsperpage = 20;
$offset = (int)($offset / $groupsperpage) * $groupsperpage;

// Strangely, casting is only needed for invite, request and admin and only in 
// postgres
if (is_mysql()) {
    $invitesql  = "'invite'";
    $requestsql = "'request'";
    $adminsql   = "'admin'";
}
else {
    $invitesql  = "CAST('invite' AS TEXT)";
    $requestsql = "CAST('request' AS TEXT)";
    $adminsql   = "CAST('admin' AS TEXT)";
}

// Different filters join on the different kinds of association
if ($filter == 'admin') {
    $sql = "
        INNER JOIN (
            SELECT g.id, $adminsql AS membershiptype
            FROM {group} g
            INNER JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ? AND gm.role = 'admin')
        ) t ON t.id = g.id";
    $values = array($USER->get('id'));
}
else if ($filter == 'member') {
    $sql = "
        INNER JOIN (
            SELECT g.id, 'admin' AS membershiptype
            FROM {group} g
            INNER JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ? AND gm.role = 'admin')
            UNION
            SELECT g.id, 'member' AS type
            FROM {group} g
            INNER JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ? AND gm.role != 'admin')
        ) t ON t.id = g.id";
    $values = array($USER->get('id'), $USER->get('id'));
}
else if ($filter == 'invite') {
    $sql = "
        INNER JOIN (
            SELECT g.id, $invitesql AS membershiptype
            FROM {group} g
            INNER JOIN {group_member_invite} gmi ON (gmi.group = g.id AND gmi.member = ?)
        ) t ON t.id = g.id";
    $values = array($USER->get('id'));
}
else if ($filter == 'request') {
    $sql = "
        INNER JOIN (
            SELECT g.id, $requestsql AS membershiptype
            FROM {group} g
            INNER JOIN {group_member_request} gmr ON (gmr.group = g.id AND gmr.member = ?)
        ) t ON t.id = g.id";
    $values = array($USER->get('id'));
}
else { // all or some other text
    $filter = 'all';
    $sql = "
        INNER JOIN (
            SELECT g.id, 'admin' AS membershiptype
            FROM {group} g
            INNER JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ? AND gm.role = 'admin')
            UNION
            SELECT g.id, 'member' AS membershiptype
            FROM {group} g
            INNER JOIN {group_member} gm ON (g.id = gm.group AND gm.member = ? AND gm.role != 'admin')
            UNION
            SELECT g.id, 'invite' AS membershiptype
            FROM {group} g
            INNER JOIN {group_member_invite} gmi ON (gmi.group = g.id AND gmi.member = ?)
            UNION SELECT g.id, 'request' AS membershiptype
            FROM {group} g
            INNER JOIN {group_member_request} gmr ON (gmr.group = g.id AND gmr.member = ?)
        ) t ON t.id = g.id";
    $values = array($USER->get('id'), $USER->get('id'), $USER->get('id'), $USER->get('id'));
}

$form = pieform(array(
    'name'   => 'filter',
    'method' => 'post',
    'renderer' => 'oneline',
    'elements' => array(
        'options' => array(
            'type' => 'select',
            'options' => array(
                'all'     => get_string('allmygroups', 'group'),
                'admin'   => get_string('groupsiown', 'group'),
                'member'  => get_string('groupsimin', 'group'),
                'invite'  => get_string('groupsiminvitedto', 'group'),
                'request' => get_string('groupsiwanttojoin', 'group')
            ),
            'defaultvalue' => $filter
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('filter')
        )
    ),
));

$values[] = 0;

$count = count_records_sql('SELECT COUNT(*) FROM {group} g ' . $sql . ' WHERE g.deleted = ?', $values);

// almost the same as query used in find - common parts should probably be pulled out
// gets the groups filtered by above
// and the first three members by id

$sql = 'SELECT g.id, g.name, g.description, g.jointype, t.membershiptype, COUNT(gm.member) AS membercount, COUNT(gmr.member) AS requests,
	(SELECT gm.member FROM {group_member} gm JOIN {usr} u ON (u.id = gm.member AND u.deleted = 0) WHERE gm.group = g.id ORDER BY member LIMIT 1) AS member1,
	(SELECT gm.member FROM {group_member} gm JOIN {usr} u ON (u.id = gm.member AND u.deleted = 0) WHERE gm.group = g.id ORDER BY member LIMIT 1 OFFSET 1) AS member2,
	(SELECT gm.member FROM {group_member} gm JOIN {usr} u ON (u.id = gm.member AND u.deleted = 0) WHERE gm.group = g.id ORDER BY member LIMIT 1 OFFSET 2) AS member3
    FROM {group} g
    LEFT JOIN {group_member} gm ON (gm.group = g.id)
    LEFT JOIN {group_member_request} gmr ON (gmr.group = g.id)' .
    $sql . '
    WHERE g.deleted = ?
    GROUP BY 1, 2, 3, 4, 5, 8, 9
    ORDER BY g.name';

$groups = get_records_sql_array($sql, $values, $offset, $groupsperpage);

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'group/mygroups.php?filter=' . $filter,
    'count' => $count,
    'limit' => $groupsperpage,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('group', 'group'),
    'resultcounttextplural' => get_string('groups', 'group'),
));

group_prepare_usergroups_for_display($groups, 'mygroups');

$smarty = smarty();
$smarty->assign('groups', $groups);
$smarty->assign('form', $form);
$smarty->assign('filter', $filter);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('searchingforgroups', array('<a href="' . get_config('wwwroot') . 'group/find.php">', '</a>'));
$smarty->assign('heading', get_string('mygroups'));
$smarty->display('group/mygroups.tpl');

function filter_submit(Pieform $form, $values) {
    redirect('/group/mygroups.php?filter=' . $values['options']);
}

?>
