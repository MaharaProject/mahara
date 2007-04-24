<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
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

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('community.php');

json_headers();

$owned  = param_boolean('owned', 0);
$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$prefix = get_config('dbprefix');
$userid = $USER->get('id');

if (empty($owned)) { // just get communities this user is a member of.
    $data = get_member_communities($userid, $offset, $limit);
    $count = get_record_sql('SELECT COUNT(c.*)
              FROM ' . $prefix . 'community c 
              JOIN ' . $prefix . 'community_member cm ON cm.community = c.id
              WHERE c.owner != ? AND cm.member = ?', array($userid, $userid));
    $count = $count->count;
}
else {

    $count = count_records_sql('SELECT COUNT(*) FROM ' . $prefix . 'community c WHERE c.owner = ?',
                               array($userid));

    $datasql = 'SELECT c.id,c.jointype,c.name,c.owner,count(cmr.community) as requestcount, COUNT(v.view) AS hasviews
                FROM ' . $prefix . 'community c 
                LEFT JOIN ' . $prefix . 'community_member_request cmr ON cmr.community = c.id
                LEFT JOIN ' . $prefix . 'view_access_community v ON v.community = c.id
                WHERE c.owner = ?
                GROUP BY c.id,c.jointype,c.name,c.owner';
                
    $data  = get_records_sql_array($datasql,array($userid), $offset, $limit);
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
