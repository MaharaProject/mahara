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
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$readone    = param_integer('readone', 0);
$markasread = param_integer('markasread', 0);
$delete     = param_integer('delete', 0);

if ($readone) {
    set_field('notification_internal_activity', 'read', 1, 'id', $readone, 'usr', $USER->get('id'));
    json_reply(false, null);
}

require_once(get_config('libroot') . 'activity.php');

$type = param_variable('type', 'all');
$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$message = false;

if ($markasread) {
    $ids = array();
    foreach ($_GET as $k => $v) {
        if (preg_match('/^unread\-(\d+)$/',$k,$m)) {
            $ids[] = $m[1];
        }
    }
    if ($ids) {
        set_field_select(
            'notification_internal_activity', 'read', 1,
            'id IN (' . join(',', $ids) . ') AND usr = ?',
            array($USER->get('id'))
        );
    }
    $message = get_string('markedasread', 'activity');
}
else if ($delete) {
    $ids = array();
    foreach ($_GET as $k => $v) {
        if (preg_match('/^delete\-(\d+)$/',$k,$m)) {
            $ids[] = $m[1];
        }
    }
    if ($ids) {
        $strids = join(',', $ids);
        $userid = $USER->get('id');
        db_begin();
        // Remove parent pointers to messages we're about to delete
        // Use temp table in subselect for Mysql compat.
        execute_sql("
            UPDATE {notification_internal_activity}
            SET parent = NULL
            WHERE parent IN (
                SELECT id
                FROM (
                   SELECT id FROM {notification_internal_activity} WHERE id IN ($strids) AND usr = ?
                ) AS temp
            )",
            array($userid)
        );
        delete_records_select(
            'notification_internal_activity',
            "id IN ($strids) AND usr = ?",
            array($userid)
        );
        db_commit();
    }
    $message = get_string('deletednotifications', 'activity', count($ids));
}

$newhtml = activitylist_html($type, $limit, $offset);

if ($message) {
    safe_require('notification', 'internal');
    $newhtml['newunreadcount'] = call_static_method(
        generate_class_name('notification', 'internal'),
        'unread_count',
        $USER->get('id')
    );
}

json_reply(false, (object) array('message' => $message, 'data' => $newhtml));
