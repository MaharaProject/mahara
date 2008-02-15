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


if ($filter == 'member') {
    $type = 'member';
}
else if ($filter == 'notmember') {
    $type = 'notmember';
}
else { // all or some other text
    $filter = 'all';
    $type = 'all';
}

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
                'notmember' => get_string('groupsnotin', 'group'),
                'member' => get_string('groupsimin', 'group'),
                'all' => get_string('allgroups', 'group')
            ),
            'defaultvalue' => $filter
        ),
        'search' => array(
            'type' => 'submit',
            'value' => get_string('search')
        )
    )
));

$groups = search_group($query, $groupsperpage, $offset, $type);

// gets more data about the groups found by search_group
// including type if the user is associated with the group in some way
// and the first three members by id
// does this by finding the lowest id, then the next lowest, then the third lowest in subselects
// which is just horrible :(
if ($groups['data']) {
    $groupids = array();
    foreach ($groups['data'] as $group) {
        $groupids[] = $group->id;
    }
    $groups['data'] =  get_records_sql_array(
        'SELECT g.id, g.name, g.description, g.owner, g.jointype, t.type, COUNT(gm.member) AS membercount, COUNT(gmr.member) AS requests,
        (SELECT gm.member FROM {group_member} gm WHERE gm.group = g.id ORDER BY member LIMIT 1) AS member1,
        (SELECT gm.member FROM {group_member} gm WHERE gm.group = g.id ORDER BY member LIMIT 1 OFFSET 1) AS member2,
        (SELECT gm.member FROM {group_member} gm WHERE gm.group = g.id ORDER BY member LIMIT 1 OFFSET 2) AS member3
        FROM {group} g
        LEFT JOIN {group_member} gm ON (gm.group = g.id)
        LEFT JOIN {group_member_request} gmr ON (gmr.group = g.id)
        LEFT JOIN (
            SELECT g.id, CAST(\'owner\' AS VARCHAR(7)) AS type
            FROM {group} g
            WHERE g.owner = ?
            UNION SELECT g.id, CAST(\'member\' AS VARCHAR(7)) AS type
            FROM {group} g
            INNER JOIN {group_member} gm ON (g.id = gm.group AND gm.member = ?)
            WHERE g.owner != gm.member
            UNION SELECT g.id, CAST(\'invite\' AS VARCHAR(7)) AS type
            FROM {group} g
            INNER JOIN {group_member_invite} gmi ON (gmi.group = g.id AND gmi.member = ?)
            UNION SELECT g.id, CAST(\'request\' AS VARCHAR(7)) AS type
            FROM {group} g
            INNER JOIN {group_member_request} gmr ON (gmr.group = g.id AND gmr.member = ?)
        ) t ON t.id = g.id
        WHERE g.id IN (' . implode($groupids, ',') . ')
        GROUP BY 1, 2, 3, 4, 5, 6, 9, 10
        ORDER BY g.name',
        array($USER->get('id'), $USER->get('id'), $USER->get('id'), $USER->get('id'))
    );
}
setup_groups($groups['data'], 'find');

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'group/find.php?filter=' . $filter . '&amp;query=' . $query,
    'count' => $groups['count'],
    'limit' => $groupsperpage,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('group', 'group'),
    'resultcounttextplural' => get_string('groups', 'group'),
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
