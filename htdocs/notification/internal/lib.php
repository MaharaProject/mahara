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
 * @subpackage notification-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginNotificationInternal extends PluginNotification {

    public static function notify_user($user, $data) {
        $toinsert = new StdClass;
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
        
        return insert_record('notification_internal_activity', $toinsert, 'id', true);
    }
    
    /** 
     * this method is only implemented in internal & is used for the header
     */

    public static function unread_count($userid) {
        static $unreadcount = array();
        if (!isset($unreadcount[$userid])) {
            $unreadcount[$userid] = count_records('notification_internal_activity', 'usr', $userid, 'read', 0);
        }
        return $unreadcount[$userid];
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
        delete_records('notification_internal_activity', 'usr', $user['id']);
    }
}

?>
