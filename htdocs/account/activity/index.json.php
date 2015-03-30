<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
    $unread = $USER->add_unread(-1);
    $data = array(
        'newunreadcount' => $unread,
        'newimage' => $THEME->get_image_url($unread ? 'newmail' : 'message'),
    );
    json_reply(false, array('data' => $data));
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
        $newunread = $USER->add_unread(-count($ids));
    }
    $message = get_string('markedasread', 'activity');
}
else if ($delete) {
    $ids = array();
    $deleteunread = 0; // Remember the number of unread messages being deleted
    foreach ($_GET as $k => $v) {
        if (preg_match('/^delete\-(\d+)$/',$k,$m)) {
            $ids[] = $m[1];
            if (isset($_GET['unread-' . $m[1]])) {
                $deleteunread++;
            }
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
        if ($deleteunread) {
            $newunread = $USER->add_unread(-$deleteunread);
        }
        db_commit();
    }
    $message = get_string('deletednotifications1', 'activity', count($ids));
}

$newhtml = activitylist_html($type, $limit, $offset);

if (isset($newunread)) {
    $newhtml['newunreadcount'] = $newunread;
    $newhtml['newimage'] = $THEME->get_image_url($newunread ? 'newmail' : 'message');
}

json_reply(false, (object) array('message' => $message, 'data' => $newhtml));
