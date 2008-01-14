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
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');

json_headers();

$owned  = param_boolean('owned', 0);
$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$userid = $USER->get('id');

if (empty($owned)) { // just get groups this user is a member of.
    $data = get_member_groups($userid, $offset, $limit);
    $count = get_record_sql('SELECT COUNT(distinct g.id) AS count
              FROM {group} g 
              JOIN {group_member} gm ON gm.group = g.id
              WHERE g.owner != ? AND gm.member = ? AND deleted = ?', array($userid, $userid, 0));
    $count = $count->count;
}
else {

    $count = count_records_sql('SELECT COUNT(*) FROM {group} g WHERE g.owner = ? AND deleted = ?',
                               array($userid, 0));

    $datasql = 'SELECT g.id,g.jointype,g.name,g.owner,count(distinct gmr.group) as requestcount, COUNT(distinct v.view) AS hasviews
                FROM {group} g 
                LEFT JOIN {group_member_request} gmr ON gmr.group = g.id
                LEFT JOIN {view_access_group} v ON v.group = g.id
                WHERE g.owner = ? AND g.deleted = ?
                GROUP BY g.id,g.jointype,g.name,g.owner';
                
    $data  = get_records_sql_array($datasql,array($userid, 0), $offset, $limit);
}

if (!$data) {
    $data = array();
}

print json_encode(array(
    'count'  => $count,
    'limit'  => $limit,
    'offset' => $offset,
    'data'   => $data,
));



?>
