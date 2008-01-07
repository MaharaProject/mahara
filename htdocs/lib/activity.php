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
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
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
function activity_occurred($activitytype, $data, $plugintype=null, $pluginname=null) {
    $at = activity_locate_typerecord($activitytype, $plugintype, $pluginname);
    if (!empty($at->delay)) {
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
 * This function dispatches all the activity stuff
 * to whatever notification plugin it needs to
 * and figures out all the implications of 
 * activity and who needs to know about it.
 * 
 * @param object $activitytype record from activity_type
 * @param mixed $data must contain message to save.
 * it can also contain url.
 * each activity type has different requirements of $data - 
 *  - <b>contactus</b> must contain $message, $subject (optional), $fromname, $fromaddress, $userfrom (if a logged in user)
 *  - <b>objectionable</b> must contain $message, $view and $artefact if applicable
 *  - <b>maharamessage</b> must contain $users, an array of userids. $subject and $message (contents of message)
 *  - <b>usermessage</b> must contain $userto, id of recipient user, $userfrom, id of user from 
    -       and $subject and $message (contents of message)
 *  - <b>feedback (artefact)</b> must contain both $artefact (id) and $view (id) and $message 
 *  - <b>feedback (view)</b> must contain $view (id) and $message
 *  - <b>watchlist (view) </b> must contain $view (id of view) as $message
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

    $activity = new $classname($data);
    if (!$activity->any_users()) {
        return;
    }

    $data = $activity->to_stdclass();
    safe_require('notification', 'internal', 'lib.php', 'require_once');
    $data->type = $activity->get_id();
    foreach ($activity->get_users() as $user) {
        $userdata = $data;
        // some stuff gets overridden by user specific stuff
        if (!empty($user->url)) {
            $userdata->url = $user->url;
        }
        if (!empty($user->message)) {
            $userdata->message = $user->message;
        }
        if (!empty($user->subject)) {
            $userdata->subject = $user->subject;
        }
        if (empty($user->method)) {
            $user->method = 'internal';
        }
        if ($user->method != 'internal') {
            $method = $user->method;
            safe_require('notification', $method, 'lib.php', 'require_once');
            try {
                call_static_method(generate_class_name('notification', $method), 'notify_user', $user, $userdata);
                $user->markasread = true; // if we're doing something else, don't generate unread internal ones.
            }
            catch (Exception $e) {
                $user->markasread = false; // if we fail (eg email falls over), don't mark it as read...
                // @todo penny notify them that their notification type failed....
            }
        }
        // always do internal
        call_static_method('PluginNotificationInternal', 'notify_user', $user, $userdata);
    }
}

/**
 * this function returns an array of users
 * for a particular activitytype
 * including the notification method.
 *
 * @param int $activitytype the id of the activity type
 * @param array $userids an array of userids to filter by
 * @param array $userobjs an array of user objects to filterby
 * @param bool $adminonly whether to filter by admin flag
 * @return array of users
 */
function activity_get_users($activitytype, $userids=null, $userobjs=null, $adminonly=false) {
    $values = array($activitytype);
    $sql = 'SELECT u.id,u.username,u.firstname,u.lastname,u.preferredname,u.email, p.method
                FROM {usr} u
                LEFT JOIN {usr_activity_preference} p
                    ON p.usr = u.id
                WHERE (p.activity = ? ' . (empty($adminonly) ? ' OR p.activity IS NULL' : '') . ')';
    if (!empty($adminonly)) {
        $sql .= ' AND u.admin = ? ';
        $values[] = 1;
    }
    if (!empty($userobjs) && is_array($userobjs)) {
        $sql .= ' AND u.id IN (' . implode(',',db_array_to_ph($userobjs)) . ')';
        $values = array_merge($values, array_to_fields($userobjs));
    } 
    else if (!empty($userids) && is_array($userids)) {
        $sql .= ' AND u.id IN (' . implode(',',db_array_to_ph($userids)) . ')';
        $values = array_merge($values, $userids);
    }
    return get_records_sql_array($sql, $values);
}

/**
 * this function inserts a default set of activity preferences for a given user
 * id
 */
function activity_set_defaults($user_id) {
    $activitytypes = get_records_array('activity_type', 'admin', 0);
    foreach ($activitytypes as $type) {
        insert_record('usr_activity_preference', (object)array(
            'usr' => $user_id,
            'activity' => $type->name,
            'method' => 'internal',
        ));
    }
    
}

function activity_process_queue() {

    db_begin();
    if ($toprocess = get_records_array('activity_queue')) {
        foreach ($toprocess as $activity) {
            handle_activity($activity->type, unserialize($activity->data), true);
        }
        delete_records('activity_queue');
    }
    db_commit();
}

function activity_get_viewaccess_users($view, $owner, $type) {
    $type = activity_locate_typerecord($type);
    $sql = 'SELECT userid, u.*, p.method
                FROM (
                SELECT (CASE WHEN usr1 = ? THEN usr2 ELSE usr1 END) AS userid 
                    FROM {usr_friend} f
                    JOIN {view} v ON (v.owner = f.usr1 OR v.owner = f.usr2)
                    JOIN {view_access} vu ON vu.view = v.id
                        WHERE (usr1 = ? OR usr2 = ?) AND vu.accesstype = ? AND v.id = ? 
                UNION SELECT usr AS userid 
                    FROM {view_access_usr} u 
                        WHERE u.view = ?
                UNION SELECT m.member 
                    FROM {group_member} m
                    JOIN {view_access_group} g ON g.group = m.group
                        WHERE g.view = ? AND (g.tutoronly = ? OR m.tutor = ?)
                UNION SELECT g.owner
                    FROM {group} g
                    JOIN {view_access_group} ag ON ag.group = g.id
                        WHERE ag.view = ?
                ) AS userlist
                JOIN {usr} u ON u.id = userlist.userid
                LEFT JOIN {usr_activity_preference} p ON p.usr = u.id
            WHERE p.activity = ?';
    $values = array($owner, $owner, $owner, 'friends', $view, $view, $view, 0, 1, $view, $type->id);
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
        throw new Exception("Invalid activity type $activitytype");
    }
    return $at;
}

/** activity type classes **/
abstract class ActivityType {
    
    protected $subject;
    protected $message;
    protected $users = array();
    protected $url;
    protected $id;
   
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

    public function __construct($data) {
        $this->set_parameters($data);
        $this->ensure_parameters();
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
        

    abstract function get_required_parameters();
}


abstract class ActivityTypeAdmin extends ActivityType { 

    public function __construct($data) {
        parent::__construct($data);
        $this->users = activity_get_users($this->get_id(), null, null, true);
    }
}

class ActivityTypeContactus extends ActivityTypeAdmin {
    
    protected $fromname;
    protected $fromemail;
    protected $userfrom;

    function __construct($data) { 
        parent::__construct($data);
        $this->subject = get_string('newcontactusfrom', 'activity') . ' ' .$this->fromname 
            . '<' . $this->fromemail .'>' . (isset($this->subject) ? ': ' . $this->subject : '');
        $this->message = $this->subject . "\n\n" . $this->message;
        $this->subject = get_string('newcontactus', 'activity');
        if (!empty($this->userfrom)) {
            $this->url = get_config('wwwroot') . 'user/view.php?id=' . $this->userfrom;
        }
    }
    
    public function get_required_parameters() {
        return array('message', 'fromname', 'fromemail');
    }
}

class ActivityTypeObjectionable extends ActivityTypeAdmin {

    protected $view;
    protected $artefact;

    function __construct($data) { 
        parent::__construct($data);
        if (!$viewtitle = get_field('view', 'title', 'id', $this->view)) {
            throw new ViewNotFoundException(get_string('viewnotfound', 'error', $this->view));
        }
        if (empty($this->artefact)) {
            $this->url = get_config('wwwroot') . 'view/view.php?id=' . $this->view;
            $this->subject = get_string('objectionablecontentview', 'activity') 
                . ' ' . get_string('onview', 'activity') . ' ' . $viewtitle;
        }
        else {
            if (!$artefacttitle = get_field('artefact', 'title', 'id', $this->artefact)) {
                throw new ArtefactNotFoundException(get_string('artefactnotfound', 'error', $this->artefact));
            }
            $this->url = get_config('wwwroot') . 'view/view.php?artefact=' . $this->artefact . '&id=' . $this->view;
            $this->subject = get_string('objectionablecontentartefact', 'activity') 
                . ' '  . get_string('onartefact', 'activity') . ' ' . $artefacttitle;
        }
    }

    public function get_required_parameters() {
        return array('message', 'view');
    }

}

class ActivityTypeVirusRepeat extends ActivityTypeAdmin {

    protected $username;
    protected $fullname;
    protected $userid;

    public function __construct($data) { 
        parent::__construct($data);
        $userstring = $this->username . ' (' . $this->fullname . ') (userid:' . $this->userid . ')' ;
        $this->subject = get_string('virusrepeatsubject', 'mahara', $userstring);
        $this->message = get_string('virusrepeatmessage');
    }

    public function get_required_parameters() {
        return array('username', 'fullname', 'userid');
    }
}

class ActivityTypeVirusRelease extends ActivityTypeAdmin {

    public function __construct($data) { 
        parent::__construct($data);
    }

    public function get_required_parameters() {
        return array();
    }
}

class ActivityTypeMaharamessage extends ActivityType {

    public function __construct($data) { 
        parent::__construct($data);
        $this->users = activity_get_users($this->get_id(), $this->users);
    }

    public function get_required_parameters() {
        return array('message', 'subject', 'users');
    }
}

class ActivityTypeUsermessage extends ActivityType { 

    protected $userto;
    protected $userfrom;

    public function __construct($data) { 
        parent::__construct($data);
        if (empty($this->subject)) {
            $this->subject = get_string('newusermessage', 'mahara', display_name($this->userfrom));
        }
        $this->users = activity_get_users($this->get_id(), array($this->userto));
        if (empty($this->url)) {
            $this->url = get_config('wwwroot') . 'user/view.php?id=' . $this->userfrom;
        }
    } 

    public function get_required_parameters() {
        return array('message', 'userto', 'userfrom');
    }
    
}

class ActivityTypeFeedback extends ActivityType { 

    protected $view;
    protected $artefact;

    public function __construct($data) { 
        parent::__construct($data);
        if (!empty($this->artefact)) { // feedback on artefact
            $this->subject = get_string('newfeedbackonartefact', 'activity');
            require_once(get_config('docroot') . 'artefact/lib.php');
            $artefact = artefact_instance_from_id($this->artefact);
            $this->subject .= ' ' .$artefact->get('title');

            $userid = null;
            if ($artefact->feedback_notify_owner()) {
                $userid = $artefact->get('owner');
            }
            if (empty($this->url)) {
                $this->url = get_config('wwwroot') . 'view/view.php?artefact=' 
                    . $this->artefact . '&id=' . $this->view;
            }
        } 
        else { // feedback on view.
            $this->subject = get_string('newfeedbackonview', 'activity');
            if (!$view = get_record('view', 'id', $this->view)) {
                throw new ViewNotFoundException(get_string('viewnotfound', 'error', $this->view));
            }
            $userid = $view->owner;
            $this->subject .= ' ' .$view->title;
            if (empty($this->url)) {
                $this->url = get_config('wwwroot') . 'view/view.php?id=' . $this->view;
            }
        }
        if ($userid) {
            $this->users = activity_get_users($this->get_id(), array($userid));
        } 
    }

    public function get_required_parameters() {
        return array('message', 'view');
    }
}

class ActivityTypeWatchlist extends ActivityType { 

    protected $view;

    public function __construct($data) { 
        parent::__construct($data); 
        $oldsubject = $this->subject;
        $this->subject = get_string('newwatchlistmessage', 'activity');
        if (!$viewinfo = get_record_sql('SELECT u.*, v.title FROM {usr} u
                                         JOIN {view} v ON v.owner = u.id
                                         WHERE v.id = ?', array($this->view))) {
            if (!empty($cron)) { // probably deleted already
                return;
            }
            throw new ViewNotFoundException(get_string('viewnotfound', 'error', $this->view));
        }
        $this->message = $oldsubject . ' ' . $viewinfo->title;
        // mysql compatibility (sigh...)
        $casturl = 'CAST(? AS TEXT)';
        if (get_config('dbtype') == 'mysql') {
            $casturl = 'CAST(? AS CHAR)'; // note, NOT varchar
        }
        $sql = 'SELECT u.*, p.method, ' . $casturl . ' AS url
                    FROM {usr_watchlist_view} wv
                    JOIN {usr} u
                        ON wv.usr = u.id
                    LEFT JOIN {usr_activity_preference} p
                        ON p.usr = u.id
                    WHERE (p.activity = ? OR p.activity IS NULL)
                    AND wv.view = ?
               ';
        $this->users = get_records_sql_array($sql, 
                                       array(get_config('wwwroot') . 'view/view.php?id=' 
                                             . $this->view, 'watchlist', $this->view));
        foreach ($this->users as &$user) {
            $user->message = display_name($viewinfo, $user) . ' ' . $this->message;
        }
    }

    public function get_required_parameters() {
        return array('subject', 'view');
    }
}

class ActivityTypeNewview extends ActivityType { 

    protected $owner;
    protected $view;

    public function __construct($data) { 
        parent::__construct($data);
        if (!$viewinfo = get_record_sql('SELECT u.*, v.title FROM {usr} u
                                         JOIN {view} v ON v.owner = u.id
                                         WHERE v.id = ?', array($this->view))) {
            if (!empty($cron)) { //probably deleted already
                return;
            }
            throw new ViewNotFoundException(get_string('viewnotfound', 'error', $this->view));
        }

        $this->message = get_string('newviewmessage', 'activity', $viewinfo->title);
        $this->subject = get_string('newviewsubject', 'activity');
        $this->url = get_config('wwwroot') . 'view/view.php?id=' . $this->view;

        // add users on friendslist or userlist...
        $this->users = activity_get_viewaccess_users($this->view, $this->owner, $this->get_id()); 
        // ick
        foreach ($this->users as &$user) {
            $user->message = display_name($viewinfo, $user) . ' ' . $this->message;
        }
    }
    
    public function get_required_parameters() {
        return array('owner', 'view');
    }
}

class ActivityTypeViewaccess extends ActivityType { 

    protected $view;
    protected $owner;
    protected $oldusers; // this can be empty though

    public function __construct($data) { 
        parent::__construct($data);
        if (!$viewinfo = get_record_sql('SELECT u.*, v.title FROM {usr} u
                                         JOIN {view} v ON v.owner = u.id
                                         WHERE v.id = ?', array($this->view))) {
            if (!empty($cron)) { // probably deleted already
                return;
            }
            throw new ViewNotFoundException(get_string('viewnotfound', 'error', $this->view));
        }
        $this->message = get_string('newviewaccessmessage', 'activity')
            . ' "' . $viewinfo->title . '" ' . get_string('ownedby', 'activity');
        $this->subject = get_string('newviewaccesssubject', 'activity');
        $this->url = get_config('wwwroot') . 'view/view.php?id=' . $this->view;
        $this->users = array_diff_key(
            activity_get_viewaccess_users($this->view, $this->owner, $this->get_id()),
            $this->oldusers
        );

        // ick
        foreach ($this->users as &$user) {
            $user->message = $this->message . ' ' . display_name($viewinfo, $user);
        }
   }

    public function get_required_parameters() {
        return array('view', 'owner', 'oldusers');
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

?>
