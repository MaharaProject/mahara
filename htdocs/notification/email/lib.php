<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage notification-email
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

require_once(get_config('docroot') . 'notification/lib.php');

class PluginNotificationEmail extends PluginNotification {

    public static function notify_user($user, $data) {

        $lang = (empty($user->lang) || $user->lang == 'default') ? get_config('lang') : $user->lang;

        $sitename = get_config('sitename');
        $fulltype = get_string_from_language($lang, 'type' . $data->activityname, 'activity');
        $subject = get_string_from_language($lang, 'emailsubject', 'notification.email', $sitename, $fulltype);
        if (!empty($data->subject)) {
            $subject .= ': ' . $data->subject;
        }

        if (!empty($data->userfrom)) {
            $userfrom = get_record('usr', 'id', $data->userfrom);
            $messagebody = get_string_from_language($lang, 'emailbody', 'notification.email', $sitename)
                . get_string_from_language($lang, 'subject') . ': ' . $data->subject . "\n\n"
                . $data->message;
        } 
        else {
            $userfrom = null;
            $messagebody = get_string_from_language($lang, 'emailbodynoreply', 'notification.email', $sitename)
                . get_string_from_language($lang, 'subject') . ': ' . $data->subject . "\n\n"
                . $data->message;
        }
        if (!empty($data->url)) {
            $messagebody .= "\n\n" . get_string_from_language($lang, 'referurl', 'notification.email', $data->url);
        }
        $prefurl = get_config('wwwroot') . 'account/activity/preferences/';
        $messagebody .=  "\n\n" . get_string_from_language($lang, 'emailbodyending', 'notification.email', $prefurl);
        email_user($user, $userfrom, $subject, $messagebody);
    }
}

?>
