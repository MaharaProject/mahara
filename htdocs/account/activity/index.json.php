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

json_headers();

$markasread = param_integer('markasread', 0);

if ($markasread) {
    $count = 0;
    db_begin();
    try {
        foreach ($_GET as $k => $v) {
            if (preg_match('/^unread\-(\d+)$/',$k,$m)) {
                set_field('notification_internal_activity', 'read', 1, 'id', $m[1]);
                $count++;
            }
        }
    }
    catch (Exception $e) {
        db_rollback();
        json_reply('local', get_string('failedtomarkasread', 'activity') . ': ' . $e->getMessage());
    }
    db_commit();
    json_reply(false, array('message' => get_string('markedasread', 'activity'), 'count' => $count));
}

// normal processing

$type = param_alphanum('type', 'all');
$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$userid = $USER->get('id');

if ($type == 'all') {
    $count = count_records('notification_internal_activity', 'usr', $userid);
    $sql = 'SELECT a.*, at.name AS type,at.plugintype, at.pluginname FROM {notification_internal_activity} a 
        JOIN {activity_type} at ON a.type = at.id
        WHERE a.usr = ? ORDER BY ctime DESC';
    $records = get_records_sql_array($sql, array($userid), $offset, $limit);
} else if ($type == 'adminmessages' && $USER->get('admin')) {
    $count = count_records_select('notification_internal_activity', 'usr = ? AND type IN (
         SELECT id FROM {activity_type} WHERE admin = ?)', 
                                  array($userid, 1));
    $sql = 'SELECT a.*, at.name AS type,at.plugintype, at.pluginname FROM {notification_internal_activity} a 
        JOIN {activity_type} at ON a.type = at.id
        WHERE a.usr = ? AND at.admin = ? ORDER BY ctime DESC';
    $records = get_records_sql_array($sql, array($userid, 1), $offset, $limit);
}
else {
    $count = count_records_select('notification_internal_activity', 'usr = ? AND type = ?',
                                  array($userid,$type));
    $sql = 'SELECT a.*, at.name AS type,at.plugintype, at.pluginname FROM {notification_internal_activity} a
        JOIN {activity_type} at ON a.type = at.id
        WHERE a.usr = ? AND a.type = ?';
    $records = get_records_sql_array($sql, array($userid, $type), $offset, $limit);
}

if (empty($records)) {
    $records = array();
}
$data = array();
$star = theme_get_url('images/star.png');
$unread = get_string('unread', 'activity');

foreach ($records as &$r) {
    $r->date = format_date(strtotime($r->ctime));
    $section = 'activity';
    if (!empty($r->plugintype)) {
        $section = $r->plugintype . '.' . $r->pluginname;
    }
    $r->type = get_string('type' . $r->type, $section);
    $r->message = format_whitespace($r->message);
}

$activity = array(
    'count'  => $count,
    'offset' => $offset,
    'limit'  => $limit,
    'data'   => $records,
    'star'   => $star,
    'unread' => $unread,
);

echo json_encode($activity);
?>
