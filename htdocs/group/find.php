<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups/find');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('findgroups'));
require_once('group.php');
require_once('searchlib.php');
$filter = param_alpha('filter', 'notmember');
$offset = param_integer('offset', 0);
$groupcategory = param_signed_integer('groupcategory', 0);
$groupsperpage = 10;
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
$elements = array();
$elements['query'] = array(
            'type' => 'text',
            'defaultvalue' => $query);
$elements['filter'] = array(
            'type' => 'select',
            'options' => array(
                'notmember' => get_string('groupsnotin', 'group'),
                'member'    => get_string('groupsimin', 'group'),
                'all'       => get_string('allgroups', 'group')
            ),
            'defaultvalue' => $filter);
if (get_config('allowgroupcategories')
    && $groupcategories = get_records_menu('group_category','','','displayorder', 'id,title')
) {
    $options[0] = get_string('allcategories', 'group');
    $options[-1] = get_string('categoryunassigned', 'group');
    $options += $groupcategories;
    $elements['groupcategory'] = array(
                'type'         => 'select',
                'options'      => $options,
                'defaultvalue' => $groupcategory,
                'help'         => true);
}
$elements['search'] = array(
            'type' => 'submit',
            'value' => get_string('search'));
$searchform = pieform(array(
    'name'   => 'search',
    'method' => 'post',
    'renderer' => 'oneline',
    'elements' => $elements
    )
);

$groups = search_group($query, $groupsperpage, $offset, $type, $groupcategory);

// gets more data about the groups found by search_group
// including type if the user is associated with the group in some way
if ($groups['data']) {
    $groupids = array();
    foreach ($groups['data'] as $group) {
        $groupids[] = $group->id;
    }
    $groups['data'] =  get_records_sql_array(
        "SELECT g1.id, g1.name, g1.description, g1.public, g1.jointype, g1.grouptype, g1.role, g1.membershiptype, g1.membercount, COUNT(gmr.member) AS requests
        FROM (
            SELECT g.id, g.name, g.description, g.public, g.jointype, g.grouptype, t.role, t.membershiptype, COUNT(gm.member) AS membercount
            FROM {group} g
            LEFT JOIN {group_member} gm ON (gm.group = g.id)
            LEFT JOIN (
                SELECT g.id, 'admin' AS membershiptype, gm.role AS role
                FROM {group} g
                INNER JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ? AND gm.role = 'admin')
                UNION
                SELECT g.id, 'member' AS membershiptype, gm.role AS role
                FROM {group} g
                INNER JOIN {group_member} gm ON (g.id = gm.group AND gm.member = ? AND gm.role != 'admin')
                UNION
                SELECT g.id, 'invite' AS membershiptype, gmi.role
                FROM {group} g
                INNER JOIN {group_member_invite} gmi ON (gmi.group = g.id AND gmi.member = ?)
                UNION
                SELECT g.id, 'request' AS membershiptype, NULL as role
                FROM {group} g
                INNER JOIN {group_member_request} gmr ON (gmr.group = g.id AND gmr.member = ?)
            ) t ON t.id = g.id
            WHERE g.id IN (" . implode($groupids, ',') . ')
            GROUP BY g.id, g.name, g.description, g.public, g.jointype, g.grouptype, t.role, t.membershiptype
        ) g1
        LEFT JOIN {group_member_request} gmr ON (gmr.group = g1.id)
        GROUP BY g1.id, g1.name, g1.description, g1.public, g1.jointype, g1.grouptype, g1.role, g1.membershiptype, g1.membercount
        ORDER BY g1.name',
        array($USER->get('id'), $USER->get('id'), $USER->get('id'), $USER->get('id'))
    );
}

group_prepare_usergroups_for_display($groups['data'], 'find');

$params = array();
if ($filter != 'notmember') {
    $params['filter'] = $filter;
}
if ($groupcategory != 0) {
    $params['groupcategory'] = $groupcategory;
}
if ($query) {
    $params['query'] = $query;
}

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'group/find.php' . ($params ? ('?' . http_build_query($params)) : ''),
    'count' => $groups['count'],
    'limit' => $groupsperpage,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('group', 'group'),
    'resultcounttextplural' => get_string('groups', 'group'),
));

function search_submit(Pieform $form, $values) {
    redirect('/group/find.php?filter=' . $values['filter'] . (!empty($values['query']) ? '&query=' . urlencode($values['query']) : '') . (!empty($values['groupcategory']) ? '&groupcategory=' . intval($values['groupcategory']) : ''));
}

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('form', $searchform);
$smarty->assign('groups', $groups['data']);
$smarty->assign('pagination', $pagination['html']);
$smarty->display('group/find.tpl');
