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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * This is the function to call whenever anything happens
 * that is going to end up on a user's activity page.
 * 
 * @param string $activitytype type of activity
 * @param mixed $data data 
 */
function activity_occurred($activitytype, $data, $plugintype=null, $pluginname=null, $delay=null) {
    $at = activity_locate_typerecord($activitytype, $plugintype, $pluginname);
    if (is_null($delay)) {
        $delay = !empty($at->delay);
    }
    if ($delay) {
        $delayed = new StdClass;
        $delayed->type = $at->id;
        $delayed->data = serialize($data);
        $delayed->ctime = db_format_timestamp(time());
        insert_record('activity_queue', $delayed);
    }
    else {
        handle_activity($at, $data);
    }
}

/** 
 * This function dispatches all the activity stuff to whatever notification 
 * plugin it needs to, and figures out all the implications of activity and who 
 * needs to know about it.
 * 
 * @param object $activitytype record from database table activity_type
 * @param mixed $data must contain message to save.
 * each activity type has different requirements of $data - 
 *  - <b>viewaccess</b> must contain $owner userid of view owner AND $view (id of view) and $oldusers array of userids before access change was committed.
 */
function handle_activity($activitytype, $data, $cron=false) {
    $data = (object)$data;
    $activitytype = activity_locate_typerecord($activitytype);

    $classname = 'ActivityType' . ucfirst($activitytype->name);
    if (!empty($activitytype->plugintype)) {
        safe_require($activitytype->plugintype, $activitytype->pluginname);
        $classname = 'ActivityType' . 
            ucfirst($activitytype->plugintype) . 
            ucfirst($activitytype->pluginname) . 
            ucfirst($activitytype->name);
    }

    $activity = new $classname($data, $cron);
    if (!$activity->any_users()) {
        return;
    }

    $activity->notify_users();
}

/**
 * this function returns an array of users who subsribe to a particular activitytype 
 * including the notification method they are using to subscribe to it.
 *
 * @param int $activitytype the id of the activity type
 * @param array $userids an array of userids to filter by
 * @param array $userobjs an array of user objects to filterby
 * @param bool $adminonly whether to filter by admin flag
 * @param array $admininstitutions list of institution names to get admins for
 * @return array of users
 */
function activity_get_users($activitytype, $userids=null, $userobjs=null, $adminonly=false,
                            $admininstitutions = array()) {
    $values = array($activitytype);
    $sql = '
        SELECT
            u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.admin, u.staff, 
            p.method, ap.value AS lang, apm.value AS maildisabled, aic.value AS mnethostwwwroot,
            h.appname AS mnethostapp
        FROM {usr} u
        LEFT JOIN {usr_activity_preference} p
            ON (p.usr = u.id AND p.activity = ?)' . (empty($admininstitutions) ? '' : '
        LEFT OUTER JOIN {usr_institution} ui
            ON (u.id = ui.usr
                AND ui.institution IN ('.join(',',array_map('db_quote',$admininstitutions)).'))') . '
        LEFT OUTER JOIN {usr_account_preference} ap
            ON (ap.usr = u.id AND ap.field = \'lang\')
        LEFT OUTER JOIN {usr_account_preference} apm
            ON (apm.usr = u.id AND ap.field = \'maildisabled\')
        LEFT OUTER JOIN {auth_instance} ai
            ON (ai.id = u.authinstance AND ai.authname = \'xmlrpc\')
        LEFT OUTER JOIN {auth_instance_config} aic
            ON (aic.instance = ai.id AND aic.field = \'wwwroot\')
        LEFT OUTER JOIN {host} h
            ON aic.value = h.wwwroot
        WHERE TRUE';
    if (!empty($userobjs) && is_array($userobjs)) {
        $sql .= ' AND u.id IN (' . implode(',',db_array_to_ph($userobjs)) . ')';
        $values = array_merge($values, array_to_fields($userobjs));
    } 
    else if (!empty($userids) && is_array($userids)) {
        $sql .= ' AND u.id IN (' . implode(',',db_array_to_ph($userids)) . ')';
        $values = array_merge($values, $userids);
    }
    if (!empty($admininstitutions)) {
        $sql .= '
        GROUP BY
            u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.admin, u.staff,
            p.method, ap.value, apm.value, aic.value, h.appname
        HAVING (u.admin = 1 OR SUM(ui.admin) > 0)';
    } else if ($adminonly) {
        $sql .= ' AND u.admin = 1';
    }
    return get_records_sql_array($sql, $values);
}


function activity_default_notification_method() {
    static $method = null;
    if (is_null($method)) {
        if (in_array('email', array_map(create_function('$a', 'return $a->name;'), plugins_installed('notification')))) {
            $method = 'email';
        }
        else {
            $method = 'internal';
        }
    }
    return $method;
}

/**
 * this function inserts a default set of activity preferences for a given user
 * id
 */
function activity_set_defaults($eventdata) {
    $user_id = $eventdata['id'];
    $activitytypes = get_records_array('activity_type', 'admin', 0);
    $method = activity_default_notification_method();

    foreach ($activitytypes as $type) {
        insert_record('usr_activity_preference', (object)array(
            'usr' => $user_id,
            'activity' => $type->id,
            'method' => $method,
        ));
    }
    
}

function activity_add_admin_defaults($userids) {
    $activitytypes = get_records_array('activity_type', 'admin', 1);
    $method = activity_default_notification_method();

    foreach ($activitytypes as $type) {
        foreach ($userids as $id) {
            if (!record_exists('usr_activity_preference', 'usr', $id, 'activity', $type->id)) {
                insert_record('usr_activity_preference', (object)array(
                    'usr' => $id,
                    'activity' => $type->id,
                    'method' => $method,
                ));
            }
        }
    }
}


function activity_process_queue() {

    if ($toprocess = get_records_array('activity_queue')) {
        // Hack to avoid duplicate watchlist notifications on the same view
        $watchlist = activity_locate_typerecord('watchlist');
        $viewsnotified = array();
        foreach ($toprocess as $activity) {
            // Remove this activity from the queue to make sure we
            // never send duplicate emails even if part of the
            // activity handler fails for whatever reason
            if (!delete_records('activity_queue', 'id', $activity->id)) {
                log_warn("Unable to remove activity $activity->id from the queue. Skipping it.");
                continue;
            }

            $data = unserialize($activity->data);
            if ($activity->type == $watchlist->id && !empty($data->view)) {
                if (isset($viewsnotified[$data->view])) {
                    continue;
                }
                $viewsnotified[$data->view] = true;
            }

            db_begin();
            try {
                handle_activity($activity->type, $data, true);
            }
            catch (MaharaException $e) {
                // Exceptions can happen while processing the queue, we just 
                // log them and continue
                log_debug($e->getMessage());
            }
            db_commit();
        }
    }
}

function activity_get_viewaccess_users($view, $owner, $type) {
    $type = activity_locate_typerecord($type);
    $sql = "SELECT userid, u.*, p.method, ap.value AS lang
                FROM (
                SELECT (CASE WHEN usr1 = ? THEN usr2 ELSE usr1 END) AS userid 
                    FROM {usr_friend} f
                    JOIN {view} v ON (v.owner = f.usr1 OR v.owner = f.usr2)
                    JOIN {view_access} vu ON vu.view = v.id
                        WHERE (usr1 = ? OR usr2 = ?) AND vu.accesstype = 'friends' AND v.id = ? 
                UNION SELECT usr AS userid 
                    FROM {view_access} u 
                        WHERE u.view = ?
                UNION SELECT m.member 
                    FROM {group_member} m
                    JOIN {view_access} vg ON vg.group = m.group
                    JOIN {group} g ON (g.id = vg.group AND g.deleted = 0 AND g.viewnotify = 1)
                    JOIN {group_member} og ON (g.id = og.group AND og.member = ?)
                        WHERE vg.view = ? AND (vg.role IS NULL OR vg.role = m.role)
                ) AS userlist
                JOIN {usr} u ON u.id = userlist.userid
                LEFT JOIN {usr_activity_preference} p ON p.usr = u.id AND p.activity = ?
                LEFT JOIN {usr_account_preference} ap ON ap.usr = u.id AND ap.field = 'lang'";
    $values = array($owner, $owner, $owner, $view, $view, $owner, $view, $type->id);
    if (!$u = get_records_sql_assoc($sql, $values)) {
        $u = array();
    }
    return $u;
}

function activity_locate_typerecord($activitytype, $plugintype=null, $pluginname=null) {
    if (is_object($activitytype)) {
        return $activitytype;
    }
    if (is_numeric($activitytype)) {
        $at = get_record('activity_type', 'id', $activitytype);
    }
    else {
        if (empty($plugintype) && empty($pluginname)) {
            $at = get_record_select('activity_type', 
                'name = ? AND plugintype IS NULL AND pluginname IS NULL', 
                array($activitytype));
        } 
        else {
            $at = get_record('activity_type', 'name', $activitytype, 'plugintype', $plugintype, 'pluginname', $pluginname);
        }
    }
    if (empty($at)) {
        throw new SystemException("Invalid activity type $activitytype");
    }
    return $at;
}

function generate_activity_class_name($name, $plugintype, $pluginname) {
    if (!empty($plugintype)) {
        safe_require($plugintype, $pluginname);
        return 'ActivityType' .
            ucfirst($plugintype) .
            ucfirst($pluginname) .
            ucfirst($name);
    }
    return 'ActivityType' . $name;
}

/** activity type classes **/
abstract class ActivityType {
    
    /**
     * Who any notifications about this activity should appear to come from
     */
    protected $fromuser;

    /**
     * When sending notifications, should the email of the person sending it be 
     * hidden? (Almost always yes, will cause the email to appear to come from 
     * the 'noreply' address)
     */
    protected $hideemail = true;

    protected $subject;
    protected $message;
    protected $strings;
    protected $users = array();
    protected $url;
    protected $urltext;
    protected $id;
    protected $type;
    protected $activityname;
    protected $cron;
    protected $overridemessagecontents;
    protected $parent;
   
    public function get_id() {
        if (!isset($this->id)) {
            $tmp = activity_locate_typerecord($this->get_type());
            $this->id = $tmp->id;
        }
        return $this->id;
    }
    
    public function get_type() {
        $prefix = 'ActivityType';
        return strtolower(substr(get_class($this), strlen($prefix)));
    }

    public function any_users() {
        return (is_array($this->users) && count($this->users) > 0);
    }

    public function get_users() {
        return $this->users;
    }

    public function __construct($data, $cron=false) {
        $this->cron = $cron;
        $this->set_parameters($data);
        $this->ensure_parameters();
        $this->activityname = strtolower(substr(get_class($this), strlen('ActivityType')));
    }

    private function set_parameters($data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }   
    }

    private function ensure_parameters() {
        foreach ($this->get_required_parameters() as $param) {
            if (!isset($this->{$param})) {
                throw new ParamOutOfRangeException(get_string('missingparam', 'activity', $param, $this->get_type()));
            }
        }
    }

    public function to_stdclass() {
       return (object)get_object_vars($this); 
    }

    public function get_string_for_user($user, $string) {
        if (empty($string) || empty($this->strings->{$string}->key)) {
            return;
        }
        $args = array_merge(
            array(
                $user->lang,
                $this->strings->{$string}->key,
                empty($this->strings->{$string}->section) ? 'mahara' : $this->strings->{$string}->section,
            ),
            empty($this->strings->{$string}->args) ? array() : $this->strings->{$string}->args
        );
        return call_user_func_array('get_string_from_language', $args);
    }

    // Optional string to use for the link text.
    public function add_urltext(array $stringdef) {
        $this->strings->urltext = (object) $stringdef;
    }

    public function get_urltext($user) {
        if (empty($this->urltext)) {
            return $this->get_string_for_user($user, 'urltext');
        }
        return $this->urltext;
    }

    public function get_message($user) {
        if (empty($this->message)) {
            return $this->get_string_for_user($user, 'message');
        }
        return $this->message;
    }
        
    public function get_subject($user) {
        if (empty($this->subject)) {
            return $this->get_string_for_user($user, 'subject');
        }
        return $this->subject;
    }

    // rewrite the url with the internal notification id?
    protected function update_url() {
        return false;
    }

    abstract function get_required_parameters();

    public function notify_user($user) {
        $changes = new stdClass;

        $userdata = $this->to_stdclass();
        // some stuff gets overridden by user specific stuff
        if (!empty($user->url)) {
            $userdata->url = $user->url;
        }
        if (empty($user->lang) || $user->lang == 'default') {
            $user->lang = get_config('lang');
        }
        if (empty($user->method)) {
            $user->method = call_static_method(get_class($this), 'default_notification_method');
        }

        // always do internal
        foreach (PluginNotificationInternal::$userdata as &$p) {
            $function = 'get_' . $p;
            $userdata->$p = $this->$function($user);
        }

        $userdata->internalid = PluginNotificationInternal::notify_user($user, $userdata);
        if ($this->update_url($userdata->internalid)) {
            $changes->url = $userdata->url = $this->url;
        }

        if ($user->method != 'internal' || isset($changes->url)) {
            $changes->read = (int) ($user->method != 'internal');
            $changes->id = $userdata->internalid;
            update_record('notification_internal_activity', $changes);
        }

        if ($user->method != 'internal') {
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
                // We don't mind other notification methods failing, as it'll
                // go into the activity log as 'unread'
                $changes->read = 0;
                update_record('notification_internal_activity', $changes);
                if (!($e instanceof EmailDisabledException || $e instanceof InvalidEmailException)) {
                    // Though, admins should probably know about the error
                    $message = (object) array(
                        'users' => get_column('usr', 'id', 'admin', 1),
                        'subject' => get_string('adminnotificationerror', 'activity'),
                        'message' => $e,
                    );
                    activity_occurred('maharamessage', $message);
                }
            }
        }

    }

    public function notify_users() {
        safe_require('notification', 'internal');
        $this->type = $this->get_id();

        while (!empty($this->users)) {
            $user = array_shift($this->users);
            $this->notify_user($user);
        }
    }

    public static function default_notification_method() {
        return activity_default_notification_method();
    }
}


abstract class ActivityTypeAdmin extends ActivityType { 

    public function __construct($data, $cron=false) {
        parent::__construct($data, $cron);
        $this->users = activity_get_users($this->get_id(), null, null, true);
    }
}

class ActivityTypeContactus extends ActivityTypeAdmin {
    
    protected $fromname;
    protected $fromemail;
    protected $hideemail = false;

    /**
     * @param array $data Parameters:
     *                    - message (string)
     *                    - subject (string) (optional)
     *                    - fromname (string)
     *                    - fromaddress (email address)
     *                    - fromuser (int) (if a logged in user)
     */
    function __construct($data, $cron=false) { 
        parent::__construct($data, $cron);
        if (!empty($this->fromuser)) {
            $this->url = get_config('wwwroot') . 'user/view.php?id=' . $this->fromuser;
        }
        else {
            $this->customheaders = array(
                'Reply-to: ' . $this->fromname . ' <' . $this->fromemail . '>',
            );
        }
    }

    function get_subject($user) {
        return get_string_from_language($user->lang, 'newcontactus', 'activity');
    }

    function get_message($user) {
        return get_string_from_language($user->lang, 'newcontactusfrom', 'activity') . ' ' . $this->fromname 
            . ' <' . $this->fromemail .'>' . (isset($this->subject) ? ': ' . $this->subject : '')
            . "\n\n" . $this->message;
    }

    public function get_required_parameters() {
        return array('message', 'fromname', 'fromemail');
    }
}

class ActivityTypeObjectionable extends ActivityTypeAdmin {

    protected $view;
    protected $artefact;
    protected $reporter;
    protected $ctime;

    /**
     * @param array $data Parameters:
     *                    - message (string)
     *                    - view (int)
     *                    - artefact (int) (optional)
     *                    - reporter (int)
     *                    - ctime (int) (optional)
     */
    function __construct($data, $cron=false) { 
        parent::__construct($data, $cron);

        require_once('view.php');
        $this->view = new View($this->view);

        if (!empty($this->artefact)) {
            require_once(get_config('docroot') . 'artefact/lib.php');
            $this->artefact = artefact_instance_from_id($this->artefact);
        }

        if ($owner = $this->view->get('owner')) {
            // Notify institutional admins of the view owner
            if ($institutions = get_column('usr_institution', 'institution', 'usr', $owner)) {
                $this->users = activity_get_users($this->get_id(), null, null, null, $institutions);
            }
        }

        if (empty($this->artefact)) {
            $this->url = $this->view->get_url();
        }
        else {
            $this->url = get_config('wwwroot') . 'view/artefact.php?artefact=' . $this->artefact->get('id') . '&view=' . $this->view->get('id');
        }

        if (empty($this->strings->subject)) {
            $this->overridemessagecontents = true;
            $viewtitle = $this->view->get('title');
            if (empty($this->artefact)) {
                $this->strings->subject = (object) array(
                    'key'     => 'objectionablecontentview',
                    'section' => 'activity',
                    'args'    => array($viewtitle, display_default_name($this->reporter)),
                );
            }
            else {
                $title = $this->artefact->get('title');
                $this->strings->subject = (object) array(
                    'key'     => 'objectionablecontentviewartefact',
                    'section' => 'activity',
                    'args'    => array($viewtitle, $title, display_default_name($this->reporter)),
                );
            }
        }
    }

    public function get_emailmessage($user) {
        $reporterurl = get_config('wwwroot') . 'user/view.php?id=' . $this->reporter;
        $ctime = strftime(get_string_from_language($user->lang, 'strftimedaydatetime'), $this->ctime);
        if (empty($this->artefact)) {
            return get_string_from_language(
                $user->lang, 'objectionablecontentviewtext', 'activity',
                $this->view->get('title'), display_default_name($this->reporter), $ctime,
                $this->message, $this->view->get_url(), $reporterurl
            );
        }
        else {
            return get_string_from_language(
                $user->lang, 'objectionablecontentviewartefacttext', 'activity',
                $this->view->get('title'), $this->artefact->get('title'), display_default_name($this->reporter), $ctime,
                $this->message, $this->view->get_url(), $reporterurl
            );
        }
    }

    public function get_htmlmessage($user) {
        $viewtitle = hsc($this->view->get('title'));
        $reportername = hsc(display_default_name($this->reporter));
        $reporterurl = get_config('wwwroot') . 'user/view.php?id=' . $this->reporter;
        $ctime = strftime(get_string_from_language($user->lang, 'strftimedaydatetime'), $this->ctime);
        $message = hsc($this->message);
        if (empty($this->artefact)) {
            return get_string_from_language(
                $user->lang, 'objectionablecontentviewhtml', 'activity',
                $viewtitle, $reportername, $ctime,
                $message, $this->view->get_url(), $viewtitle,
                $reporterurl, $reportername
            );
        }
        else {
            return get_string_from_language(
                $user->lang, 'objectionablecontentviewartefacthtml', 'activity',
                $viewtitle, hsc($this->artefact->get('title')), $reportername, $ctime,
                $message, $this->view->get_url(), $viewtitle,
                $reporterurl, $reportername
            );
        }
    }

    public function get_required_parameters() {
        return array('message', 'view', 'reporter');
    }

}

class ActivityTypeVirusRepeat extends ActivityTypeAdmin {

    protected $username;
    protected $fullname;
    protected $userid;

    public function __construct($data, $cron=false) { 
        parent::__construct($data, $cron);
    }

    public function get_subject($user) {
        $userstring = $this->username . ' (' . $this->fullname . ') (userid:' . $this->userid . ')' ;
        return get_string_from_language($user->lang, 'virusrepeatsubject', 'mahara', $userstring);
    }

    public function get_message($user) {
        return get_string_from_language($user->lang, 'virusrepeatmessage');
    }

    public function get_required_parameters() {
        return array('username', 'fullname', 'userid');
    }
}

class ActivityTypeVirusRelease extends ActivityTypeAdmin {

    public function __construct($data, $cron=false) { 
        parent::__construct($data, $cron);
    }

    public function get_required_parameters() {
        return array();
    }
}

class ActivityTypeMaharamessage extends ActivityType {

    /**
     * @param array $data Parameters:
     *                    - subject (string)
     *                    - message (string)
     *                    - users (list of user ids)
     */
    public function __construct($data, $cron=false) { 
        parent::__construct($data, $cron);
        $this->users = activity_get_users($this->get_id(), $this->users);
    }

    public function get_required_parameters() {
        return array('message', 'subject', 'users');
    }
}

class ActivityTypeInstitutionmessage extends ActivityType {

    protected $messagetype;
    protected $institution;
    protected $username;
    protected $fullname;

    public function __construct($data, $cron=false) {
        parent::__construct($data, $cron);
        if ($this->messagetype == 'request') {
            $this->url = get_config('wwwroot') . 'admin/users/institutionusers.php';
            $this->users = activity_get_users($this->get_id(), null, null, null,
                                              array($this->institution->name));
            $this->add_urltext(array('key' => 'institutionmembers', 'section' => 'admin'));
        } else if ($this->messagetype == 'invite') {
            $this->url = get_config('wwwroot') . 'account/institutions.php';
            $this->users = activity_get_users($this->get_id(), $this->users);
            $this->add_urltext(array('key' => 'institutionmembership', 'section' => 'mahara'));
        }
    }

    public function get_subject($user) {
        if ($this->messagetype == 'request') {
            $userstring = $this->fullname . ' (' . $this->username . ')';
            return get_string_from_language($user->lang, 'institutionrequestsubject', 'activity', $userstring, 
                                            $this->institution->displayname);
        } else if ($this->messagetype == 'invite') {
            return get_string_from_language($user->lang, 'institutioninvitesubject', 'activity', 
                                            $this->institution->displayname);
        }
    }

    public function get_message($user) {
        if ($this->messagetype == 'request') {
            return get_string_from_language($user->lang, 'institutionrequestmessage', 'activity');
        } else if ($this->messagetype == 'invite') {
            return get_string_from_language($user->lang, 'institutioninvitemessage', 'activity');
        }
    }

    public function get_required_parameters() {
        return array('messagetype', 'institution');
    }
}

class ActivityTypeUsermessage extends ActivityType { 

    protected $userto;
    protected $userfrom;

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
        if ($this->userfrom) {
            $this->fromuser = $this->userfrom;
        }
        $this->users = activity_get_users($this->get_id(), array($this->userto));
        $this->add_urltext(array(
            'key'     => 'Reply',
            'section' => 'group',
        ));
    } 

    public function get_subject($user) {
        if (empty($this->subject)) {
            return get_string_from_language($user->lang, 'newusermessage', 'group',
                                            display_name($this->userfrom));
        }
        return $this->subject;
    }

    protected function update_url($internalid) {
        $this->url = get_config('wwwroot') . 'user/sendmessage.php?id=' . $this->userfrom . '&replyto=' . $internalid . '&returnto=inbox';
        return true;
    }

    public function get_required_parameters() {
        return array('message', 'userto', 'userfrom');
    }
    
}

class ActivityTypeWatchlist extends ActivityType { 

    protected $view;

    private $viewinfo;

    /**
     * @param array $data Parameters:
     *                    - view (int)
     */
    public function __construct($data, $cron) { 
        parent::__construct($data, $cron); 
        //$oldsubject = $this->subject;
        if (!$this->viewinfo = get_record_sql('SELECT u.*, v.title FROM {usr} u
                                         JOIN {view} v ON v.owner = u.id
                                         WHERE v.id = ?', array($this->view))) {
            if (!empty($this->cron)) { // probably deleted already
                return;
            }
            throw new ViewNotFoundException(get_string('viewnotfound', 'error', $this->view));
        }
        // mysql compatibility (sigh...)
        $casturl = 'CAST(? AS TEXT)';
        if (get_config('dbtype') == 'mysql') {
            $casturl = 'CAST(? AS CHAR)'; // note, NOT varchar
        }
        $sql = 'SELECT u.*, p.method, ap.value AS lang, ' . $casturl . ' AS url
                    FROM {usr_watchlist_view} wv
                    JOIN {usr} u
                        ON wv.usr = u.id
                    LEFT JOIN {usr_activity_preference} p
                        ON p.usr = u.id
                    LEFT OUTER JOIN {usr_account_preference} ap
                        ON (ap.usr = u.id AND ap.field = \'lang\')
                    WHERE (p.activity = ? OR p.activity IS NULL)
                    AND wv.view = ?
               ';
        $this->users = get_records_sql_array($sql, 
                                       array(get_config('wwwroot') . 'view/view.php?id=' 
                                             . $this->view, $this->get_id(), $this->view));

        // Remove the view from the watchlist of users who can no longer see it
        if ($this->users) {
            $userstodelete = array();
            foreach($this->users as $k => &$u) {
                if (!can_view_view($this->view, $u->id)) {
                    $userstodelete[] = $u->id;
                    unset($this->users[$k]);
                }
            }
            if ($userstodelete) {
                delete_records_select(
                    'usr_watchlist_view',
                    'view = ? AND usr IN (' . join(',', $userstodelete) . ')',
                    array($this->view)
                );
            }
        }

        $this->add_urltext(array('key' => 'View', 'section' => 'view'));
    }

    public function get_subject($user) {
        return get_string_from_language($user->lang, 'newwatchlistmessage', 'activity');
    }

    public function get_message($user) {
        return get_string_from_language($user->lang, 'newwatchlistmessageview', 'activity', 
                                        display_name($this->viewinfo, $user), $this->viewinfo->title);
    }

    public function get_required_parameters() {
        return array('view');
    }
}

class ActivityTypeNewview extends ActivityType { 

    protected $owner;
    protected $view;

    private $viewinfo;

    public function __construct($data, $cron=false) { 
        parent::__construct($data, $cron);
        if (!$this->viewinfo = get_record_sql('SELECT u.*, v.title FROM {usr} u
                                         JOIN {view} v ON v.owner = u.id
                                         WHERE v.id = ?', array($this->view))) {
            if (!empty($this->cron)) { //probably deleted already
                return;
            }
            throw new ViewNotFoundException(get_string('viewnotfound', 'error', $this->view));
        }

        $this->url = get_config('wwwroot') . 'view/view.php?id=' . $this->view;

        // add users on friendslist or userlist...
        $this->users = activity_get_viewaccess_users($this->view, $this->owner, $this->get_id()); 
    }

    public function get_subject($user) {
        return get_string_from_language($user->lang, 'newviewsubject', 'activity');
    }
    
    public function get_message($user) {
        return get_string_from_language($user->lang, 'newviewmessage', 'activity', 
                                        display_name($this->viewinfo, $user), $this->viewinfo->title);
    }
    
    public function get_required_parameters() {
        return array('owner', 'view');
    }
}

class ActivityTypeViewaccess extends ActivityType { 

    protected $view;
    protected $owner;
    protected $oldusers; // this can be empty though

    private $title, $ownername;

    /**
     * @param array $data Parameters:
     *                    - owner (int)
     *                    - view (int)
     *                    - oldusers (array of user IDs)
     */
    public function __construct($data, $cron=false) { 
        parent::__construct($data, $cron);
        if (!$viewinfo = get_record_sql('
            SELECT v.title, v.owner, v.group, v.institution,
                u.id, u.username, u.preferredname, u.firstname, u.lastname, u.staff, u.admin,
                g.name AS groupname, i.displayname AS institutionname
            FROM {view} v
                LEFT JOIN {usr} u ON v.owner = u.id
                LEFT JOIN {group} g ON v.group = g.id
                LEFT JOIN {institution} i ON v.institution = i.name
            WHERE v.id = ?', array($this->view))) {
            if (!empty($this->cron)) { // probably deleted already
                return;
            }
            throw new ViewNotFoundException(get_string('viewnotfound', 'error', $this->view));
        }
        $this->url = get_config('wwwroot') . 'view/view.php?id=' . $this->view;
        $this->users = array_diff_key(
            activity_get_viewaccess_users($this->view, $this->owner, $this->get_id()),
            $this->oldusers
        );
        $this->title = $viewinfo->title;
        if ($this->users) {
            if ($viewinfo->group) {
                $this->ownername = $viewinfo->groupname;
            }
            else if ($viewinfo->institution) {
                $this->ownername = $viewinfo->institutionname;
            }
            else if ($viewinfo->owner) {
                $this->ownername = display_name($viewinfo, null, true);
            }
        }
        $this->add_urltext(array('key' => 'View', 'section' => 'view'));
    }

    public function get_subject($user) {
        return get_string('newviewaccesssubject', 'activity');
    }
    
    public function get_message($user) {
        if ($this->ownername) {
            return get_string_from_language($user->lang, 'newviewaccessmessage', 'activity',
                                            $this->title, $this->ownername);
        }
        return get_string_from_language($user->lang, 'newviewaccessmessagenoowner', 'activity', $this->title);
    }
    
    public function get_required_parameters() {
        return array('view', 'owner', 'oldusers');
    }
}

class ActivityTypeGroupMessage extends ActivityType {

    protected $group;
    protected $roles;
    protected $submittedview;
    private $viewinfo;
    private $groupinfo;

    /**
     * @param array $data Parameters:
     *                    - subject (string)
     *                    - message (string)
     *                    - group (integer)
     *                    - roles (list of roles)
     */
    public function __construct($data, $cron=false) {
        require_once('group.php');

        parent::__construct($data, $cron);

        $this->groupinfo = get_record('group', 'id', $this->group);

        $members = group_get_member_ids($this->group, isset($this->roles) ? $this->roles : null);
        $this->users = activity_get_users($this->get_id(), $members);

        if ($this->submittedview) {
            $this->viewinfo = get_record('view', 'id', $this->submittedview);
            $this->viewinfo->ownername = display_name($this->viewinfo->owner);
            $this->url = get_config('wwwroot') . 'view/view.php?id=' . $this->submittedview;
        }
    }

    public function get_subject($user) {
        if ($this->submittedview) {
            return get_string_from_language($user->lang, 'viewsubmittedsubject', 'activity', $this->groupinfo->name);
        }
        return $this->subject;
    }

    public function get_message($user) {
        if ($this->submittedview) {
            return get_string_from_language($user->lang, 'viewsubmittedmessage', 'activity', $this->viewinfo->ownername, $this->viewinfo->title, $this->groupinfo->name);
        }
        return $this->subject;
    }

    public function get_required_parameters() {
        return array('message', 'subject', 'group');
    }
}

abstract class ActivityTypePlugin extends ActivityType {

    abstract public function get_plugintype();

    abstract public function get_pluginname();

    public function get_type() {
        $prefix = 'ActivityType' . $this->get_plugintype() . $this->get_pluginname();
        return strtolower(substr(get_class($this), strlen($prefix)));
    }

    public function get_id() {
        if (!isset($this->id)) {
            $tmp = activity_locate_typerecord($this->get_type(), $this->get_plugintype(), $this->get_pluginname());
            $this->id = $tmp->id;
        }
        return $this->id;
    }
}


function format_notification_whitespace($message, $type=null) {
    $message = preg_replace('/^(\s|<br( ?\/)?>|&nbsp;|\xc2\xa0)*/', '', $message);
    $message = format_whitespace($message);
    // @todo: Sensibly distinguish html notifications, notifications where the full text
    // appears on another page and this is just an abbreviated preview, and text-only
    // notifications where the entire text must appear here because there's nowhere else
    // to see it.
    $replace = ($type == 'newpost' || $type == 'feedback') ? '<br>' : '<br><br>';
    return preg_replace('/(<br( ?\/)?>\s*){2,}/', $replace, $message);
}

/**
 * Get one page of notifications and return html
 */
function activitylist_html($type='all', $limit=10, $offset=0) {
    global $USER;

    $userid = $USER->get('id');

    $typesql = '';
    if ($type != 'all') {
        // Treat as comma-separated list of activity type names
        $types = split(',', preg_replace('/[^a-z,]+/', '', $type));
        if ($types) {
            $typesql = ' at.name IN (' . join(',', array_map('db_quote', $types)) . ')';
            if (in_array('adminmessages', $types)) {
                $typesql = '(' . $typesql . ' OR at.admin = 1)';
            }
            $typesql = ' AND ' . $typesql;
        }
    }

    $from = "
        FROM {notification_internal_activity} a
        JOIN {activity_type} at ON a.type = at.id
        WHERE a.usr = ? $typesql";
    $values = array($userid);

    $count = count_records_sql('SELECT COUNT(*)' . $from, $values);

    $pagination = build_pagination(array(
        'id'         => 'activitylist_pagination',
        'url'        => get_config('wwwroot') . 'account/activity/index.php?type=' . hsc($type),
        'jsonscript' => 'account/activity/index.json.php',
        'datatable'  => 'activitylist',
        'count'      => $count,
        'limit'      => $limit,
        'offset'     => $offset,
    ));

    $result = array(
        'count'         => $count,
        'limit'         => $limit,
        'offset'        => $offset,
        'type'          => $type,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($count < 1) {
        return $result;
    }

    $records = get_records_sql_array('
        SELECT
            a.*, at.name AS type, at.plugintype, at.pluginname' . $from . '
        ORDER BY a.ctime DESC',
        $values,
        $offset,
        $limit
    );
    if ($records) {
        foreach ($records as &$r) {
            $r->date = format_date(strtotime($r->ctime), 'strfdaymonthyearshort');
            $section = empty($r->plugintype) ? 'activity' : "{$r->plugintype}.{$r->pluginname}";
            $r->strtype = get_string('type' . $r->type, $section);
            $r->message = format_notification_whitespace($r->message);
        }
    }

    $smarty = smarty_core();
    $smarty->assign('data', $records);
    $result['tablerows'] = $smarty->fetch('account/activity/activitylist.tpl');

    return $result;
}
