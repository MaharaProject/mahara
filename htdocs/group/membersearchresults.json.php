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

define('PUBLIC', 1);
define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
require_once('searchlib.php');

$id     = param_integer('id');
$query  = trim(param_variable('query', ''));
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

define('GROUP', $id);
$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$membershiptype = param_variable('membershiptype', '');

if (!empty($membershiptype)) {
    if (group_user_access($id) != 'admin') {
        json_reply('local', get_string('accessdenied', 'error'));
    }
}

$results = get_group_user_search_results($group->id, $query, $offset, $limit, $membershiptype);
if (!param_integer('html', 1)) {
    foreach ($results['data'] as &$result) {
        $result = array('id' => $result['id'], 'name' => $result['name']);
    }
    json_reply(false, $results);
}

list($html, $pagination, $count, $offset, $membershiptype) = group_get_membersearch_data($results, $id, $query, $membershiptype);

json_reply(false, array(
    'message' => null,
    'data' => array(
        'tablerows' => $html,
        'pagination' => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
        'count' => $count,
        'results' => $count . ' ' . ($count == 1 ? get_string('result') : get_string('results')),
        'offset' => $offset,
        'membershiptype' => $membershiptype,
    )
));
