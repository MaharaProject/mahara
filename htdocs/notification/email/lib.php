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
 * @subpackage notification-email
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

require_once(get_config('docroot') . 'notification/lib.php');

class PluginNotificationEmail extends PluginNotification {

    static $userdata = array('htmlmessage', 'emailmessage');

    public static function notify_user($user, $data) {

        $messagehtml = null;

        if (!empty($data->overridemessagecontents)) {
            $subject = $data->subject;
            if (!empty($data->emailmessage)) {
                $messagebody = $data->emailmessage;
            }
            else if (!empty($user->emailmessage)) {
                $messagebody = $user->emailmessage;
            }
            else {
                $messagebody = $data->message;
            }
            if (!empty($data->htmlmessage)) {
                $messagehtml = $data->htmlmessage;
            }
            else if (!empty($user->htmlmessage)) {
                $messagehtml = $user->htmlmessage;
            }
        }
        else {
            $lang = (empty($user->lang) || $user->lang == 'default') ? get_config('lang') : $user->lang;
            $separator = str_repeat('-', 72);

            $sitename = get_config('sitename');
            $subject = get_string_from_language($lang, 'emailsubject', 'notification.email', $sitename);
            if (!empty($data->subject)) {
                $subject .= ': ' . $data->subject;
            }

            $messagebody = get_string_from_language($lang, 'emailheader', 'notification.email', $sitename) . "\n";
            $messagebody .= $separator . "\n\n";

            $messagebody .= get_string_from_language($lang, 'subject') . ': ' . $data->subject . "\n\n";
            if ($data->activityname == 'usermessage') {
                // Do not include the message body in user messages when they are sent by email
                // because it encourages people to reply to the email.
                $messagebody .= get_string_from_language($lang, 'newusermessageemailbody', 'group', display_name($data->userfrom), $data->url);
            }
            else {
                $messagebody .= $data->message;
                if (!empty($data->url)) {
                    $messagebody .= "\n\n" . get_string_from_language($lang, 'referurl', 'notification.email', $data->url);
                }
            }

            $messagebody .= "\n\n$separator";

            $prefurl = get_config('wwwroot') . 'account/activity/preferences/';
            $messagebody .=  "\n\n" . get_string_from_language($lang, 'emailfooter', 'notification.email', $sitename, $prefurl);
        }

        $userfrom = null;
        if (!empty($data->fromuser)) {
            $userfrom = get_record('usr', 'id', $data->fromuser);
            if ($data->hideemail) {
                $userfrom->email = get_config('noreplyaddress');
            }
        }
        email_user($user, $userfrom, $subject, $messagebody, $messagehtml, !empty($data->customheaders) ? $data->customheaders : null);
    }
}
