<?php
/**
 *
 * @package    mahara
 * @subpackage notification-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginNotificationInternal extends PluginNotification {

    static $userdata = array('urltext', 'subject', 'message');

    public static function notify_user($user, $data) {
        static $pluginlist = null;

        $toinsert = new stdClass();
        $toinsert->type = $data->type;
        $toinsert->usr = $user->id;
        if (!empty($user->markasread)) {
            $toinsert->read = 1;
        }
        else {
            $toinsert->read = 0;
        }
        $toinsert->message = $data->message;
        $toinsert->subject = $data->subject;
        $toinsert->parent = $data->parent;
        $toinsert->ctime = db_format_timestamp(time());

        if (!empty($data->url)) {
            $toinsert->url = $data->url;
        }
        if (!empty($data->urltext)) {
            $toinsert->urltext = $data->urltext;
        }
        if (!empty($data->fromuser)) {
            $toinsert->from = $data->fromuser;
        }

        $messageid = insert_record('notification_internal_activity', $toinsert, 'id', true);

        // If unread, check if any plugins want to do anything with this. (Handled this way as cheaper than using events.)
        if (!$toinsert->read) {
            // Only do the include process once - don't even try to do inclusion if we know we already have.
            if ($pluginlist === null) {
                $pluginlist = plugin_all_installed();
                foreach ($pluginlist as $plugin) {
                    safe_require($plugin->plugintype, $plugin->name);
                }
            }
            foreach ($pluginlist as $key => $plugin) {
                $classname = generate_class_name($plugin->plugintype, $plugin->name);
                if (!is_callable(array($classname, 'notification_created'))) {
                    unset ($pluginlist[$key]);
                    continue;
                }
                call_static_method($classname, 'notification_created', $messageid, $toinsert, 'notification_internal_activity');
            }
        }

        return $messageid;
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            // Add triggers to update user unread message count when updating
            // notification_internal_activity
            db_create_trigger(
                'update_unread_insert',
                'AFTER', 'INSERT', 'notification_internal_activity', '
                IF NEW.read = 0 THEN
                    UPDATE {usr} SET unread = unread + 1 WHERE id = NEW.usr;
                END IF;'
            );
            db_create_trigger(
                'update_unread_update',
                'AFTER', 'UPDATE', 'notification_internal_activity', '
                IF OLD.read = 0 AND NEW.read = 1 THEN
                    UPDATE {usr} SET unread = unread - 1 WHERE id = NEW.usr;
                ELSEIF OLD.read = 1 AND NEW.read = 0 THEN
                    UPDATE {usr} SET unread = unread + 1 WHERE id = NEW.usr;
                END IF;'
            );
            db_create_trigger(
                'update_unread_delete',
                'AFTER', 'DELETE', 'notification_internal_activity', '
                IF OLD.read = 0 THEN
                    UPDATE {usr} SET unread = unread - 1 WHERE id = OLD.usr;
                END IF;'
            );
        }
    }

    public static function get_event_subscriptions() {
        $subscriptions = array(
            (object)array(
                'plugin'       => 'internal',
                'event'        => 'deleteuser',
                'callfunction' => 'deleteuser',
            ),
        );
        return $subscriptions;
    }

    public static function deleteuser($event, $user) {
        db_begin();

        // Before deleting the user's notifications, remove parent pointers to the
        // messages we're about to delete. The temporary table in this query is
        // required by MySQL
        execute_sql("
            UPDATE {notification_internal_activity}
            SET parent = NULL
            WHERE parent IN (
                SELECT id FROM (
                   SELECT id FROM {notification_internal_activity} WHERE usr = ?
                ) AS temp
            )",
            array($user['id'])
        );
        delete_records('notification_internal_activity', 'usr', $user['id']);

        // Delete system messages from this user where the url points to their
        // missing profile.  They're mostly friend requests, which are now useless.
        delete_records_select(
            'notification_internal_activity',
            '"from" = ? AND type = (SELECT id FROM {activity_type} WHERE name = ?) AND url = ?',
            array($user['id'], 'maharamessage', get_config('wwwroot') . 'user/view.php?id=' . $user['id'])
        );
        db_commit();
    }

    /**
     * A method that does housekeeping on the notification_internal_activity table
     * @param $types string|array the activity types to be cleaned
     * @param $olderthandays integer the age an entry should at least be, before cleaning
     */
    public static function clean_notifications($types, $olderthandays=182) {
        $olderthandays = get_config('internalnotificationexpire') ? get_config('internalnotificationexpire') : $olderthandays;
        $staletime = db_format_timestamp(time() - ($olderthandays * 24 * 60 * 60));

        if (!is_array($types)) {
            // We're potentially dealing with just one type
            $types = array($types);
        }

        $select = '
            ctime < ?
            AND "read" = 1
            AND type IN(
                SELECT id FROM {activity_type}
                WHERE name IN (' . join(",", array_map(function($a) { return db_quote($a); }, $types)) . '))';

        delete_records_select('notification_internal_activity', $select, array(db_format_timestamp($staletime)));
    }

}
