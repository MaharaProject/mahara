<?php
/**
 *
 * @package    mahara
 * @subpackage module-multirecipientnotification
 * @author     David Ballhausen, Tobias Zeuch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('module', 'multirecipientnotification');

global $USER;
global $THEME;

$readone    = param_integer('readone', 0);
$list       = param_alphanumext('list', 'notification_internal_activity');
$markasread = param_integer('markasread', 0);
$delete     = param_integer('delete', 0);

if ($readone) {

    if ('notification_internal_activity' === $list) {
        set_field($list, 'read', 1, 'id', $readone, 'usr', $USER->get('id'));
    }
    else if ('module_multirecipient_notification' === $list) {
        mark_as_read_mr(array($readone), $USER->get('id'));
    }
    $unread = $USER->add_unread(-1);
    $data = array(
        'newunreadcount' => $unread,
        'newunreadcounttext' => get_string('unread', 'mahara', $unread)
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
    $m = array();
    foreach ($_GET as $k => $v) {
        if (preg_match('/^select\-([a-zA-Z_]+)\-(\d+)$/',$k,$m)) {
            $list = $m[1];
            $ids[$list][] = $m[2];
        }
    }
    foreach ($ids as $list => $idsperlist) {
        if ($idsperlist) {
            if ('module_multirecipient_notification' === $list) {
                $list = 'module_multirecipient_userrelation';
                $column = 'notification';
            }
            else {
                $column = 'id';
            }
            set_field_select(
                $list, 'read', '1',
                $column . ' IN (' . join(',', array_map('db_quote', $idsperlist)) . ') AND usr = ?',
                array($USER->get('id'))
            );
            $newunread = $USER->add_unread(-count($idsperlist));
        }
    }
    $message = get_string('markedasread', 'activity');
}
else if ($delete) {
   $ids = array();
   $deleteunread = 0; // Remember the number of unread messages being deleted (this doesn't do that though... it counts the number of message that have mark as read selected)
    foreach ($_GET as $k => $v) {
        if (preg_match('/^select\-([a-zA-Z_]+)\-(\d+)$/',$k,$m)) {
            $list = $m[1];
            $ids[$list][] = $m[2];
            if (param_exists('unread-' . $list . '-' . $m[2])) {
                $deleteunread++;
            }
        }
    }

    db_begin();
    $countdeleted = 0;
    foreach ($ids as $list => $idsperlist) {
        if ('module_multirecipient_notification' === $list) {
            delete_messages_mr($idsperlist, $USER->get('id'));
        }
        else if ('notification_internal_activity' === $list) {
            $strids = join(',', array_map('db_quote', $idsperlist));

            $userid = $USER->get('id');
            // Ignore message ids that do not belong to the current user to stop
            // hacking of the form allowing the deletion of messages owned by other users.
            $rawstrids = join(',', array_map('db_quote', $idsperlist));

            $ids = get_column_sql(
                "SELECT id FROM {notification_internal_activity}
                WHERE id IN ($rawstrids) AND usr = ?",
                array($userid)
            );

            if ($ids) {
                $strids = join(',', $ids);
                // Remove parent pointers to messages we're about to delete
                execute_sql("
                    UPDATE {notification_internal_activity}
                    SET parent = NULL
                    WHERE parent IN ($strids)"
                );
                delete_records_select(
                    'notification_internal_activity',
                    "id IN ($strids)"
                );
                if ($deleteunread) {
                    $newunread = $USER->add_unread(-1 * $deleteunread);
                }
            }
        }
        $countdeleted += ($ids) ? count($ids) : count($idsperlist);
    }
    db_commit();
    $message = get_string('deletednotifications1', 'activity', $countdeleted);
}

// ------------ Change ------------
// use the new function to show from - and to user
$newhtml = activitylistin_html($type, $limit, $offset);
// --------- End Change -----------


if (isset($newunread)) {
    $newhtml['newunreadcount'] = $newunread;
    $newhtml['newunreadcounttext'] = get_string('unread', 'mahara', $newunread);
}

json_reply(false, (object) array('message' => $message, 'data' => $newhtml));
