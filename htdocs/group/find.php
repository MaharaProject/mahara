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
define('MENUITEM', 'groups/find');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('findgroups'));
require('group.php');
require('searchlib.php');
$filter = param_alpha('filter', 'notmember');
$offset = param_integer('offset', 0);

$groupsperpage = 20;

$query = param_variable('query', '');

$searchform = pieform(array(
    'name' => 'search',
    'method' => 'post',
    'renderer' => 'oneline',
    'elements' => array(
        'query' => array(
            'type' => 'text',
            'defaultvalue' => $query
        ),
        'filter' => array(
            'type' => 'select',
            'options' => array(
                'notmember' => get_string('groupsnotin'),
                'member' => get_string('groupsimin'),
                'all' => get_string('allgroups')
            ),
            'defaultvalue' => $filter
        ),
        'search' => array(
            'type' => 'submit',
            'value' => get_string('search')
        )
    )
));

if ($filter == 'member') {
    $type = 'member';
}
else if ($filter == 'notmember') {
    $type = 'notmember';
}
else if ($filter == 'all'){
    $type = 'all';
}

$groups = search_group($query, $groupsperpage, $offset, $type);

if ($groups['data']) {
    $groupids = array();
    foreach ($groups['data'] as $group) {
        $groupids[] = $group->id;
    }
    $groups['data'] =  get_records_sql_array(
        'SELECT g.id, g.name, g.description, g.owner, g.jointype, m.member1 AS member1, m.member2 AS member2, t.type, MIN(gm.member) AS member3, COUNT(gm2.*) AS membercount, COUNT(gmr.*) AS requests
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
        LEFT JOIN {group_member_request} gmr ON gmr.group = g.id
        LEFT JOIN (
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
        ) t ON t.id = g.id
        WHERE g.id IN (' . implode($groupids, ',') . ')
        GROUP BY 1, 2, 3, 4, 5, 6, 7, 8
        ORDER BY g.name',
        array($USER->get('id'), $USER->get('id'), $USER->get('id'), $USER->get('id'))
    );
}
setup_groups($groups['data'], 'find');

$pagination = build_pagination(array(
    'url' => 'find.php?filter=' . $filter . '&amp;query=' . $query,
    'count' => $groups['count'],
    'limit' => $groupsperpage,
    'offset' => $offset
));

function search_submit(Pieform $form, $values) {
    redirect('/group/find.php?filter=' . $values['filter'] . (isset($values['query']) ? '&query=' . urlencode($values['query']) : ''));
}

$smarty = smarty();
$smarty->assign('heading', TITLE);
$smarty->assign('form', $searchform);
$smarty->assign('groups', $groups['data']);
$smarty->assign('pagination', $pagination['html']);
$smarty->display('group/find.tpl');

?>
