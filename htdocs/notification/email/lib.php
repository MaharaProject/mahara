<?php
/**
 *
 * @package    mahara
 * @subpackage notification-email
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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

            if ($data->url && stripos($data->url, 'http://') !== 0 && stripos($data->url, 'https://') !== 0) {
                $data->url = get_config('wwwroot') . $data->url;
            }

            if ($data->activityname == 'usermessage') {
                // Do not include the message body in user messages when they are sent by email
                // because it encourages people to reply to the email.
                $messagebody .= get_string_from_language($lang, 'newusermessageemailbody', 'group', display_name($data->userfrom, $user), $data->url);
            }
            else {
                $messagebody .= $data->message;
                if (!empty($data->url)) {
                    $messagebody .= "\n\n" . get_string_from_language($lang, 'referurl', 'notification.email', $data->url);
                }
            }

            $messagebody .= "\n\n$separator";
            // Add unsubscribe link
            if (isset($user->unsubscribetoken) && !empty($user->unsubscribetoken)) {
                $unsubscribemessage = 'unsubscribe';
                if (isset($data->unsubscribetype) && !empty($data->unsubscribetype)) {
                    $unsubscribemessage .= '_' . $data->unsubscribetype;
                }
                $messagebody .= "\n\n" . get_string_from_language($lang, $unsubscribemessage, 'notification.email', $data->unsubscribelink . $user->unsubscribetoken);
            }
            $prefurl = get_config('wwwroot') . 'account/activity/preferences/index.php';
            $messagebody .=  "\n\n" . get_string_from_language($lang, 'emailfooter', 'notification.email', $sitename, $prefurl);
        }

        $userfrom = null;
        if (!empty($data->fromuser)) {
            $userfrom = get_record('usr', 'id', $data->fromuser);
            if ($data->hideemail) {
                $userfrom->email = get_config('noreplyaddress');
            }
            else {
                if (empty($data->customheaders)) {
                    $data->customheaders = array();
                }
                $data->customheaders[] = "reply-to: {$userfrom->email}";
            }
        }
        email_user($user, $userfrom, $subject, $messagebody, $messagehtml, !empty($data->customheaders) ? $data->customheaders : null);
    }
}
