<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-multirecipientnotification
 * @author     David Ballhausen, Tobias Zeuch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'multirecipientnotification');

global $USER;
global $THEME;

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
    $m = array();
    foreach ($_GET as $k => $v) {
        if (preg_match('/^unread\-(\d+)$/',$k,$m)) {
            $ids[] = $m[1];
        }
    }
    if ($ids) {
        set_field_select(
            'notification_internal_activity', 'read', 1,
            'id IN (' . join(',', array_map('db_quote', $ids)) . ') AND usr = ?',
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
        if (preg_match('/^delete\-([a-zA-Z_]+)\-(\d+)$/',$k,$m)) {
            $table = $m[1];
            $ids[$table][] = $m[2];
            if (isset($_GET['unread-' . $table . '-' . $m[2]])) {
                $deleteunread++;
            }
        }
    }
    $countdeleted = 0;
    db_begin();
    foreach ($ids as $table => $idspertable) {
        if ('artefact_multirecipient_notification' === $table) {
            delete_messages_mr($idspertable, $USER->get('id'));
            $countdeleted += count($idspertable);
        }
    }
    db_commit();
    $message = get_string('deletednotifications1', 'activity', $countdeleted);
}

// ------------ Change ------------
// use the new function to show from - and to user
$newhtml = activitylistout_html($type, $limit, $offset);
// --------- End Change -----------


if (isset($newunread)) {
    $newhtml['newunreadcount'] = $newunread;
    $newhtml['newimage'] = $THEME->get_image_url($newunread ? 'newmail' : 'message');
}

json_reply(false, (object) array('message' => $message, 'data' => $newhtml));
