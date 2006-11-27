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

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

json_headers();

$stopmonitoring = param_integer('stopmonitoring', 0);
$getartefacts   = param_integer('getartefacts', 0); 

$prefix = get_config('dbprefix');

if ($stopmonitoring) {
    $userid = $USER->get('id');
    $count = 0;
    db_begin();
    try {
        foreach ($_GET as $k => $v) {
            if (preg_match('/^stopviews\-(\d+)$/',$k,$m)) {
                $recurse = param_boolean($k . '-recurse', null);
                if (!empty($recurse)) {
                    $sql = 'DELETE FROM ' . $prefix . 'usr_watchlist_artefact 
                            WHERE usr = ? AND (artefact IN (
                                SELECT artefact FROM ' . $prefix . 'artefact_parent_cache WHERE parent IN (
                                    SELECT artefact FROM ' . $prefix . 'view_artefact WHERE view = ? ) )
                            OR artefact IN (
                                SELECT artefact FROM ' . $prefix . 'view_artefact WHERE view = ?))';
                    delete_records_sql($sql, array($userid, $m[1], $m[1]));
                }
                delete_records('usr_watchlist_view', 'usr', $userid, 'view', $m[1]);
                $count++;
            }
            else if (preg_match('/^stopartefacts\-(\d+)$/',$k,$m)) {
                $recurse = param_boolean($k . '-recurse', null);
                if (!empty($recurse)) {
                    $sql = 'DELETE FROM ' . $prefix . 'usr_watchlist_artefact 
                            WHERE usr = ? AND artefact IN (
                                SELECT artefact FROM ' . $prefix . 'artefact_parent_cache WHERE parent = ?)';
                    delete_records_sql($sql, array($userid, $m[1]));
                }
                delete_records('usr_watchlist_artefact', 'usr', $userid, 'artefact', $m[1]);
                $count++;
            }
            else if (preg_match('/^stopcommunities\-(\d+)$/',$k,$m)) {
                delete_records('usr_watchlist_community', 'usr', $userid, 'community', $m[1]);
                $count++;
            }
        }
    }
    catch (Exception $e) {
        db_rollback();
        $data = array('error' => $e->getMessage);
        echo json_encode($data);
    }
    db_commit();
    $data = array('success' => 1, 'count' => $count);
    echo json_encode($data);
    exit;
}


// normal processing

$type = param_alpha('type', 'views');
$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$userid = $USER->get('id');
$prefix = get_config('dbprefix');

$count = 0;
$records = array();

if ($type == 'views') {
    $count = count_records('usr_watchlist_view', 'usr', $userid);
    $sql = 'SELECT v.*, v.title AS name 
            FROM ' . $prefix . 'view v
            JOIN ' . $prefix . 'usr_watchlist_view w ON w.view = v.id
            WHERE w.usr = ?
            ORDER BY v.mtime DESC';
    $records = get_records_sql_array($sql, array($userid), $offset, $limit);
}
else if ($type == 'communities') {
    $count = count_records('usr_watchlist_community', 'usr', $userid);
    $sql = 'SELECT c.* 
            FROM ' . $prefix . 'community c
            JOIN ' . $prefix . 'usr_watchlist_community w ON w.community = c.id 
            WHERE w.usr = ?
            ORDER BY c.mtime DESC';
    $records = get_records_sql_array($sql, array($userid), $offset, $limit);
}
else if ($type == 'artefacts') {
    $count = count_records('usr_watchlist_artefact', 'usr', $userid);
    $sql = 'SELECT a.* , a.title AS name
            FROM ' . $prefix . 'artefact a
            JOIN ' . $prefix . 'usr_watchlist_artefact w ON w.artefact = a.id 
            WHERE w.usr = ?
            ORDER BY a.mtime DESC';
    $records = get_records_sql_array($sql, array($userid), $offset, $limit);
}

if (empty($records)) {
    $records = array();
}

$activity = array(
    'count'     => $count,
    'offset'    => $offset,
    'limit'     => $limit,
    'data'      => $records,
    'type'      => $type,
);

echo json_encode($activity);

?>
