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

defined('INTERNAL') || die();
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/lib/activity.php');

class ActivityTypeMultirecipientmessage extends ActivityTypeUsermessage {

    protected $usrids = array();
    protected $notification = null;

    /**
     * @param array $data Parameters:
     *                    - userto (int)
     *                    - userfrom (int)
     *                    - subject (string)
     *                    - message (string)
     *                    - parent (int)
     */
    public function __construct($data, $cron=false) {
        parent::__construct($data, $cron);
        $this->activityname = 'usermessage';
        if (!in_array($this->userfrom, $this->usrids)) {
            array_push($this->usrids,$this->userfrom);
        }
        if (count(array_diff($this->usrids, array($this->userfrom))) <= 0) {
            throw new Exception('empty recipients, talking to yourself again?');
        }

        $this->type = $this->get_id();
    }

    /**
     *
     * insert the notification into table module_multirecipient_notification
     * afterwards call notifiy_user to connect the users with the message
     *
     * @staticvar boolean $badnotification
     * @staticvar array $adminnotified
     * @param type $userids
     */
    public function notify_users() {
        safe_require('notification', 'internal');
        $messagedata = $this->to_stdclass();
        $messagedata->ctime = date('Y-m-d H:i:s');

        $this->notification = insert_record('module_multirecipient_notification',
                $messagedata, 'id', true);

        if (empty($messagedata->lang) || $messagedata->lang == 'default') {
            $messagedata->lang = get_config('lang');
        }

        $typenr = get_field('activity_type', 'id', 'name', $this->get_type());
        $users = activity_get_users($typenr, $this->usrids);

        foreach ($users as $user) {
            $this->notify_user($user);
        }

        return $this->notification;
    }

    /**
     * notify the single user (pesonalized notification type: Email or nothing
     * because internal notification is already done here)
     *
     * @param type $user
     */
    public function notify_user($user) {
        $userdata = $this->to_stdclass();
        $changes = new stdClass;
        $userdata->usr = $user->id;
        $user->method = $this->user_notification_method($user->id);

        // some stuff gets overridden by user specific stuff
        if (empty($user->method)) {
            $user->method = $this->default_notification_method();
        }
        if (empty($user->lang) || $user->lang == 'default') {
            $user->lang = get_config('lang');
        }
        $userdata->role = $this->get_role($userdata->usr);

        // always do internal
        foreach (PluginNotificationInternal::$userdata as &$p) {
            $function = 'get_' . $p;
            $userdata->$p = $this->$function($user);
        }

        if ('sender' === $userdata->role) {
            $userdata->read = '0';
        }

        $userdata->internalid = insert_record('module_multirecipient_userrelation',
                $userdata, 'id', true);
        if ($this->update_url($userdata->notification)) {
            $changes->url = $userdata->url = $this->url;
        }
        if ($user->method != 'internal') {
            $changes->read = (string)(int) ($user->method != 'internal');
            $changes->id = $userdata->internalid;
            update_record('module_multirecipient_userrelation', $changes);
        }

        if (($user->method != 'internal') && ('sender' !== $userdata->role)) {
            $method = $user->method;
            safe_require('notification', $method);
            $notificationclass = generate_class_name('notification', $method);
            $classvars = get_class_vars($notificationclass);

            if (!empty($classvars['userdata'])) {
                foreach ($classvars['userdata'] as &$p) {
                    $function = 'get_' . $p;
                    if (!isset($userdata->$p) && method_exists($this, $function)) {
                        $userdata->$p = $this->$function($user);
                    }
                }
            }
            try {
                call_static_method($notificationclass, 'notify_user', $user, $userdata);
            }
            catch (MaharaException $e) {
                static $badnotification = false;
                static $adminnotified = array();
                // We don't mind other notification methods failing, as it'll
                // go into the activity log as 'unread'
                $changes->read = 0;
                update_record('module_multirecipient_userrelation', $changes);
                if (!$badnotification && !($e instanceof EmailDisabledException || $e instanceof InvalidEmailException)) {
                    // Admins should probably know about the error, but to avoid sending too many similar notifications,
                    // save an initial prefix of the message being sent and throw away subsequent exceptions with the
                    // same prefix.  To cut down on spam, it's worth missing out on a few similar messages.
                    $k = substr($e, 0, 60);
                    if (!isset($adminnotified[$k])) {
                        $message = (object) array(
                            'users' => get_column('usr', 'id', 'admin', 1),
                            'subject' => get_string('adminnotificationerror', 'activity'),
                            'message' => $e,
                        );
                        $adminnotified[$k] = 1;
                        $badnotification = true;
                        activity_occurred('maharamessage', $message);
                        $badnotification = false;
                    }
                }
            }
        }

        // The user's unread message count does not need to be updated from $changes->read
        // because of the db trigger on notification_internal_activity.
        return;
    }

    /**
     *
     * the role of the connected user is sender or recipient. Compare with
     * fromuser to identify
     *
     * @param int $user
     * @return string
     */
    public function get_role($user) {
        if ($user == $this->fromuser) {
            return 'sender';
        }

        return 'recipient';
    }

    /**
     *
     * denotes the fields required to send this message
     *
     * @return type
     */
    public function get_required_parameters() {
        return array('message', 'subject', 'userfrom', 'usrids');
    }

    /**
     * fake the activitytype
     *
     * @return string
     */
    public function get_type() {
        return 'usermessage';
    }

    /**
     * get the default notification_method
     *
     * @param int $usrid
     * @return string
     */
    function default_notification_method() {
        $method = get_field('activity_type', 'defaultmethod', 'name', $this->get_type());
        return $method;
    }

    /**
     * get the user-specific notification_method for user $userid
     *
     * @param int $usrid
     * @return string
     */
    function user_notification_method($usrid) {
        $activity = get_field('activity_type', 'id', 'name', $this->get_type());
        $method = get_field('usr_activity_preference', 'method', 'usr', $usrid, 'activity', $activity);
        return $method;
    }

    protected function update_url($internalid) {
        $this->url = 'module/multirecipientnotification/sendmessage.php?replyto=' . $internalid . '&returnto=inbox';
        return true;
    }
}
