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
    else if ('artefact_multirecipient_notification' === $list) {
        mark_as_read_mr(array($readone), $USER->get('id'));
    }
    $unread = $USER->add_unread(-1);
    $data = array(
        'newunreadcount' => $unread
    );
    json_reply(false, array('data' => $data));
}

require_once(get_config('libroot') . 'activity.php');

$type = param_variable('type', 'all');
$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$message = false;

if ($delete) {
    $ids = array();
    $deleteunread = 0; // Remember the number of unread messages being deleted
    foreach ($_GET as $k => $v) {
        if (preg_match('/^select\-([a-zA-Z_]+)\-(\d+)$/',$k,$m)) {
            $list = $m[1];
            $ids[$list][] = $m[2];
            if (isset($_GET['unread-' . $list . '-' . $m[2]])) {
                $deleteunread++;
            }
        }
    }
    db_begin();
    $countdeleted = 0;
    foreach ($ids as $list => $idsperlist) {
        if ('module_multirecipient_notification' === $list) {
            delete_messages_mr($idsperlist, $USER->get('id'));
            $countdeleted += count($idsperlist);
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
}

json_reply(false, (object) array('message' => $message, 'data' => $newhtml));
