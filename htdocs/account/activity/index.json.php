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

$markasread = param_integer('markasread', 0);

if ($markasread) {

    db_begin();
    try {
        foreach ($_GET as $k => $v) {
            if (preg_match('/^unread\-(\d+)$/',$k,$m)) {
                set_field('notification_internal_activity', 'read', 1, 'id', $m[1]);
            }
        }
    }
    catch (Exception $e) {
        db_rollback();
        $data = array('error' => $e->getMessage);
        echo json_encode($data);
    }
    db_commit();
    $data = array('success' => 1);
    echo json_encode($data);
    exit;
}

// normal processing

$type = param_alpha('type', 'all');
$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$userid = $SESSION->get('id');

if ($type == 'all') {
    $count = count_records('notification_internal_activity', 'usr', $userid);
    $records = get_rows('notification_internal_activity', 'usr', $userid,
                           'ctime DESC', '*', $offset, $limit);
} else {
    $count = count_records_select('notification_internal_activity', 'usr = ? AND type = ?',
                                  array($userid,$type));
    $records = get_rows_select('notification_internal_activity', 'usr = ? AND type = ?', 
                                  array($userid, $type), 
                                  'ctime DESC', '*', $offset, $limit);
}

if (empty($records)) {
    $records = array();
}
$data = array();
$star = theme_get_image_path('star.png');
$unread = get_string('unread', 'activity');

foreach ($records as $r) {
    $r['date'] = format_date(strtotime($r['ctime']));
}

$activity = array(
    'count' => $count,
    'offset' => $offset,
    'limit' => $limit,
    'data' => $records,
    'star' => $star,
    'unread' => $unread,
);

echo json_encode($activity);
?>
