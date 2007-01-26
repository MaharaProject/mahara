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
 * @subpackage notification-emaildigest
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

require_once(get_config('docroot') . 'notification/lib.php');

class PluginNotificationEmaildigest extends PluginNotification {

    public static function notify_user($user, $data) {
        $toinsert = new StdClass;
        $toinsert->type = $data->type;
        $toinsert->usr = $user->id;
        $toinsert->message = $data->message;
        $toinsert->ctime = db_format_timestamp(time());
        if (!empty($data->url)) {
            $toinsert->url = $data->url;
        }
        
        insert_record('notification_emaildigest_queue', $toinsert);
    }

    public static function get_cron() {
        $emaildigest = new StdClass;
        $emaildigest->callfunction = 'send_digest';
        $emaildigest->hour = '6';
        return array($emaildigest);
    }

    public static function send_digest() {
        $users = array();
        $sitename = get_config('sitename');

        $sql = 'SELECT q.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, q.*,' . db_format_tsfield('ctime').'
                FROM ' . get_config('dbprefix') . 'usr u 
                    JOIN ' . get_config('dbprefix') . 'notification_emaildigest_queue q
                        ON q.usr = u.id
                ORDER BY usr,type,q.ctime';

        if ($tosend = get_records_sql_array($sql, array())) {
            foreach ($tosend as $queue) {
                if (!isset($users[$queue->usr])) {
                    $users[$queue->usr] = new StdClass;
                    
                    $users[$queue->usr]->user = new StdClass;
                    $users[$queue->usr]->user->firstname     = $queue->firstname;
                    $users[$queue->usr]->user->lastname      = $queue->lastname;
                    $users[$queue->usr]->user->preferredname = $queue->preferredname;
                    $users[$queue->usr]->user->email         = $queue->email;
                    $users[$queue->usr]->user->id            = $queue->usr;
                    
                    $users[$queue->usr]->entries = array();
                }
                $queue->nicetype = get_string('type' . $queue->type, 'activity');
                $users[$queue->usr]->entries[$queue->id] = $queue;
            }
        }
        foreach ($users as $user) {
            $subject = get_string('emailsubject', 'notification.emaildigest', $sitename);
            $body = get_string('emailbodynoreply', 'notification.emaildigest', $sitename);
            foreach ($user->entries as $entry) {
                $body .= get_string('type', 'activity') . $entry->nicetype 
                    . ' ' . get_string('attime', 'activity')  . ' ' . format_date($entry->ctime) . "\n";
                if (!empty($queue->subject)) {
                    $body .= get_string('subject') . $queue->subject ."\n";
                }
                if (!empty($queue->message)) {
                    $body .= "\n" . $queue->message;
                }
                if (!empty($queue->url)) {
                    $body .= "\n" . $queue->url;
                }
                $body .= "\n\n";
            }
            $prefurl = get_config('wwwroot') . 'account/activity/preferences/';
            $body .= "\n\n" . get_string('emailbodyending', 'notification.emaildigest', $prefurl);
            try {
                email_user($user->user, null, $subject, $body);
                //only delete them if the email succeeded! 
                $in = db_array_to_ph($user->entries);
                delete_records_select('notification_emaildigest_queue', 
                                      'id IN (' . implode(', ', $in) . ')', 
                                      array_keys($user->entries));
            } 
            catch (Exception $e) {
                // @todo
            }
        }
    }
}

?>
