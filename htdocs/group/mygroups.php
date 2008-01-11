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
 * @subpackage core
 * @author     Clare Lenihan <clare@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups/mygroups');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('mygroups'));
require('group.php');
$filter = param_alpha('filter', 'all');
$offset = param_integer('offset', 'all');

$groupsperpage = 20;
$offset = (int)($offset / $groupsperpage) * $groupsperpage;

$form = pieform(array(
    'name' => 'filter',
    'method' => 'post',
    'renderer' => 'oneline',
    'elements' => array(
        'options' => array(
            'type' => 'select',
            'options' => array(
                'all' => get_string('allmygroups', 'group'),
                'owner' => get_string('groupsiown', 'group'),
                'member' => get_string('groupsimin', 'group'),
                'invite' => get_string('groupsiminvitedto', 'group'),
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

// different filters join on the different kinds of association
if ($filter == 'all') {
    $sql = '
        INNER JOIN (
            SELECT g.id, CAST(\'owner\' AS TEXT) AS type
            FROM {group} g
            WHERE g.owner = ?
            UNION SELECT g.id, CAST(\'member\' AS TEXT) AS type
            FROM {group} g
            INNER JOIN {group_member} gm ON (g.id = gm.group AND gm.member = ?)
            WHERE g.owner != gm.member
            UNION SELECT g.id, CAST(\'invite\' AS TEXT) AS type
            FROM {group} g
            INNER JOIN {group_member_invite} gmi ON (gmi.group = g.id AND gmi.member = ?)
            UNION SELECT g.id, CAST(\'request\' AS TEXT) AS type
            FROM {group} g
            INNER JOIN {group_member_request} gmr ON (gmr.group = g.id AND gmr.member = ?)
        ) t ON t.id = g.id';
    $values = array($USER->get('id'), $USER->get('id'), $USER->get('id'), $USER->get('id'));
}
else if ($filter == 'owner') {
    $sql = '
        INNER JOIN (
            SELECT g.id, CAST(\'owner\' AS TEXT) AS type
            FROM {group} g
            WHERE g.owner = ?
        ) t ON t.id = g.id';
    $values = array($USER->get('id'));
}
else if ($filter == 'member') {
    $sql = '
        INNER JOIN (
            SELECT g.id, CAST(\'owner\' AS TEXT) AS type
            FROM {group} g
            WHERE g.owner = ?
            UNION SELECT g.id, CAST(\'member\' AS TEXT) AS type
            FROM {group} g
            INNER JOIN {group_member} gm ON (g.id = gm.group AND gm.member = ?)
            WHERE g.owner != gm.member
        ) t ON t.id = g.id';
    $values = array($USER->get('id'), $USER->get('id'));
}
else if ($filter == 'invite') {
    $sql = '
        INNER JOIN (
            SELECT g.id, CAST(\'invite\' AS TEXT) AS type
            FROM {group} g
            INNER JOIN {group_member_invite} gmi ON (gmi.group = g.id AND gmi.member = ?)
        ) t ON t.id = g.id';
    $values = array($USER->get('id'));
}
else if ($filter == 'request') {
    $sql = '
        INNER JOIN (
            SELECT g.id, CAST(\'request\' AS TEXT) AS type
            FROM {group} g
            INNER JOIN {group_member_request} gmr ON (gmr.group = g.id AND gmr.member = ?)
        ) t ON t.id = g.id';
    $values = array($USER->get('id'));
}

$count = count_records_sql('SELECT COUNT(g.*) FROM {group} g ' . $sql, $values);

// almost the same as query used in find - common parts should probably be pulled out
// gets the groups filtered by
// including type if the user is associated with the group in some way
// and the first three members by id
// does this by finding the lowest id, then the next lowest, then the third lowest in subselects
// which is just horrible :(

$sql = 'SELECT g.id, g.name, g.description, g.owner, g.jointype, m.member1 AS member1, m.member2 AS member2, t.type, MIN(gm.member) AS member3, COUNT(gm2.*) AS membercount, COUNT(gmr.*) AS requests
    FROM {group} g
    LEFT JOIN (
        SELECT m.member AS member1, g.id AS group, MIN(gm.member) AS member2
        FROM {group} g
        LEFT JOIN (
            SELECT g.id AS group, MIN(gm.member) AS member
            FROM {group} g
            LEFT JOIN {group_member} gm ON (gm.group = g.id)
            GROUP BY 1
        ) m on m.group = g.id
        LEFT JOIN {group_member} gm ON (gm.group = g.id AND m.member != gm.member)
           GROUP BY 1, 2
    ) m ON m.group = g.id
    LEFT JOIN {group_member} gm ON (gm.group = g.id AND gm.member != m.member1 AND gm.member != m.member2)
    LEFT JOIN {group_member} gm2 ON (gm2.group = g.id)
    LEFT JOIN {group_member_request} gmr ON (gmr.group = g.id)' .
    $sql . '
    GROUP BY 1, 2, 3, 4, 5, 6, 7, 8
    ORDER BY g.name';

$groups = get_records_sql_array($sql, $values, $offset, $groupsperpage);

$pagination = build_pagination(array(
    'url' => 'mygroups.php?filter=' . $filter,
    'count' => $count,
    'limit' => $groupsperpage,
    'offset' => $offset
));

setup_groups($groups, 'mygroups');

$smarty = smarty();
$smarty->assign('groups', $groups);
$smarty->assign('form', $form);
$smarty->assign('filter', $filter);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('searchingforgroups', array('<a href="' . get_config('wwwroot') . 'group/find.php">', '</a>'));
$smarty->display('group/mygroups.tpl');

function filter_submit(Pieform $form, $values) {
    redirect('/group/mygroups.php?filter=' . $values['options']);
}

?>
