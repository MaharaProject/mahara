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
define('MENUITEM', 'managegroups/groups');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

define('TITLE', get_string('administergroups', 'admin'));

require_once('group.php');
require_once('searchlib.php');

$query = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit = param_integer('limit', 10);

$searchform = pieform(array(
    'name'   => 'search',
    'method' => 'post',
    'renderer' => 'oneline',
    'elements' => array(
        'query' => array(
            'type' => 'text',
            'defaultvalue' => $query,
        ),
        'search' => array(
            'type' => 'submit',
            'value' => get_string('search'),
        ),
    ),
));

function search_submit(Pieform $form, $values) {
    redirect(get_config('wwwroot') . 'admin/groups/groups.php?query=' . urlencode($values['query']));
}

$groups = search_group($query, $limit, $offset, 'all');

if ($ids = array_map(create_function('$a', 'return intval($a->id);'), $groups['data'])) {
    // Member & admin counts
    $ids = join(',', $ids);
    $counts = get_records_sql_assoc("
        SELECT m.group, COUNT(m.member) AS members, SUM((m.role = 'admin')::int) AS admins
        FROM {group_member} m
        WHERE m.group IN ($ids)
        GROUP BY m.group",
        array()
    );
}

foreach ($groups['data'] as &$group) {
    $group->visibility = $group->public ? get_string('Public', 'group') : get_string('Members', 'group');
    $group->admins = empty($counts[$group->id]->admins) ? 0 : $counts[$group->id]->admins;
    $group->members = empty($counts[$group->id]->members) ? 0 : $counts[$group->id]->members;
}

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'admin/groups/groups.php?query=' . $query,
    'count' => $groups['count'],
    'limit' => $limit,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('group', 'group'),
    'resultcounttextplural' => get_string('groups', 'group'),
));

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('searchform', $searchform);
$smarty->assign('groups', $groups);
$smarty->assign('pagination', $pagination);
$smarty->display('admin/groups/groups.tpl');

?>
