<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * This is the function to call whenever anything happens
 * that is going to end up on a user's activity page.
 *
 * @param string $activitytype type of activity
 * @param array $data must contain the fields specified by get_required_parameters of the activity type subclass.
 * @param string $plugintype
 * @param string $pluginname
 * @param bool $delay
 */
function activity_occurred($activitytype, $data, $plugintype=null, $pluginname=null, $delay=null) {
    try {
        $at = activity_locate_typerecord($activitytype, $plugintype, $pluginname);
    }
    catch (Exception $e) {
        return;
    }
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
 * @param $cron = true if called by a cron job
 * @param object $queuedactivity  record of the activity in the queue (from the table activity_queue)
 * @return int The ID of the last processed user
 *      = 0 if all users get processed
 */
function handle_activity($activitytype, $data, $cron=false, $queuedactivity=null) {
    $data = (object)$data;

    if ($cron && isset($queuedactivity)) {
        $data->last_processed_userid = $queuedactivity->last_processed_userid;
        $data->activity_queue_id = $queuedactivity->id;
    }

    $classname = get_activity_type_classname($activitytype);
    $activity = new $classname($data, $cron);
    if (!$activity->any_users()) {
        return 0;
    }

    return $activity->notify_users();
}

/**
 * Given an activity type id or record, calculate the class name.
 *
 * @param mixed $activitytype either numeric activity type id or an activity type record (containing name, plugintype, pluginname)
 * @return string
 */
function get_activity_type_classname($activitytype) {
    $activitytype = activity_locate_typerecord($activitytype);

    $classname = 'ActivityType' . ucfirst($activitytype->name);
    if (!empty($activitytype->plugintype)) {
        safe_require($activitytype->plugintype, $activitytype->pluginname);
        $classname = 'ActivityType' .
            ucfirst($activitytype->plugintype) .
            ucfirst($activitytype->pluginname) .
            ucfirst($activitytype->name);
    }
    return $classname;
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
            ON (apm.usr = u.id AND apm.field = \'maildisabled\')
        LEFT OUTER JOIN {auth_instance} ai
            ON (ai.id = u.authinstance AND ai.authname = \'xmlrpc\')
        LEFT OUTER JOIN {auth_instance_config} aic
            ON (aic.instance = ai.id AND aic.field = \'wwwroot\')
        LEFT OUTER JOIN {host} h
            ON aic.value = h.wwwroot
        WHERE u.deleted = 0';
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
    return get_records_sql_assoc($sql, $values);
}


/**
 * this function inserts a default set of activity preferences for a given user
 * id
 */
function activity_set_defaults($eventdata) {
    $user_id = $eventdata['id'];
    $activitytypes = get_records_array('activity_type', 'admin', 0);

    foreach ($activitytypes as $type) {
        insert_record('usr_activity_preference', (object)array(
            'usr' => $user_id,
            'activity' => $type->id,
            'method' => $type->defaultmethod,
        ));
    }
}

function activity_add_admin_defaults($userids) {
    $activitytypes = get_records_array('activity_type', 'admin', 1);

    foreach ($activitytypes as $type) {
        foreach ($userids as $id) {
            if (!record_exists('usr_activity_preference', 'usr', $id, 'activity', $type->id)) {
                insert_record('usr_activity_preference', (object)array(
                    'usr' => $id,
                    'activity' => $type->id,
                    'method' => $type->defaultmethod,
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
            $data = unserialize($activity->data);
            if ($activity->type == $watchlist->id && !empty($data->view)) {
                if (isset($viewsnotified[$data->view])) {
                    continue;
                }
                $viewsnotified[$data->view] = true;
            }

            try {
                $last_processed_userid = handle_activity($activity->type, $data, true, $activity);
            }
            catch (MaharaException $e) {
                // Exceptions can happen while processing the queue, we just
                // log them and continue
                log_debug($e->getMessage());
            }
            // Update the activity queue
            // or Remove this activity from the queue if all the users get processed
            // to make sure we
            // never send duplicate emails even if part of the
            // activity handler fails for whatever reason
            if (!empty($last_processed_userid)) {
                update_record('activity_queue', array('last_processed_userid' => $last_processed_userid), array('id' => $activity->id));
            }
            else {
                if (!delete_records('activity_queue', 'id', $activity->id)) {
                    log_warn("Unable to remove activity $activity->id from the queue. Skipping it.");
                }
            }
        }
    }
}

/**
 * event-listener is called when an artefact is changed or a block instance
 * is commited. Saves the view, the block instance, user and time into the
 * database
 *
 * @global User $USER
 * @param string $event
 * @param object $eventdata
 */
function watchlist_record_changes($event){
    global $USER;

    // don't catch root's changes, especially not when installing...
    if ($USER->get('id') <= 0) {
        return;
    }
    if ($event instanceof BlockInstance) {
        $viewid = $event->get('view');
        if ($viewid) {
            set_field('view', 'mtime', db_format_timestamp(time()), 'id', $viewid);
        }
        if (record_exists('usr_watchlist_view', 'view', $viewid)) {
            $whereobj = new stdClass();
            $whereobj->block = $event->get('id');
            $whereobj->view = $viewid;
            $whereobj->usr = $USER->get('id');
            $dataobj = clone $whereobj;
            $dataobj->changed_on = date('Y-m-d H:i:s');
            ensure_record_exists('watchlist_queue', $whereobj, $dataobj);
        }
    }
    else if ($event instanceof ArtefactType) {
        $blockid = $event->get('id');
        $getcolumnquery = '
            SELECT DISTINCT
             "view", "block"
            FROM
             {view_artefact}
            WHERE
             artefact =' . $blockid;
        $relations = get_records_sql_array($getcolumnquery, array());

        // fix unnecessary type-inconsistency of get_records_sql_array
        if (false === $relations) {
            $relations = array();
        }

        foreach ($relations as $rel) {
            $viewid = $rel->view;
            if ($viewid) {
                set_field('view', 'mtime', db_format_timestamp(time()), 'id', $viewid);
            }
            if (!record_exists('usr_watchlist_view', 'view', $viewid)) {
                continue;
            }
            $whereobj = new stdClass();
            $whereobj->block = $rel->block;
            $whereobj->view = $viewid;
            $whereobj->usr = $USER->get('id');
            $dataobj = clone $whereobj;
            $dataobj->changed_on = date('Y-m-d H:i:s');
            ensure_record_exists('watchlist_queue', $whereobj, $dataobj);
        }
    }
    else if (!is_object($event) && !empty($event['id'])) {
        $viewid = $event['id'];
        if ($viewid) {
            set_field('view', 'mtime', db_format_timestamp(time()), 'id', $viewid);
        }
        if (record_exists('usr_watchlist_view', 'view', $viewid)) {
            $whereobj = new stdClass();
            $whereobj->view = $viewid;
            $whereobj->usr = $USER->get('id');
            $whereobj->block = null;
            $dataobj = clone $whereobj;
            $dataobj->changed_on = date('Y-m-d H:i:s');
            ensure_record_exists('watchlist_queue', $whereobj, $dataobj);
        }
    }
    else {
        return;
    }
}

/**
 * is triggered when a blockinstance is deleted. Deletes all watchlist_queue
 * entries that refer to this blockinstance
 *
 * @param BlockInstance $blockinstance
 */
function watchlist_block_deleted(BlockInstance $block) {
    global $USER;

    // don't catch root's changes, especially not when installing...
    if ($USER->get('id') <= 0) {
        return;
    }

    delete_records('watchlist_queue', 'block', $block->get('id'));

    if (record_exists('usr_watchlist_view', 'view', $block->get('view'))) {
        $whereobj = new stdClass();
        $whereobj->view = $block->get('view');
        $whereobj->block = null;
        $whereobj->usr = $USER->get('id');
        $dataobj = clone $whereobj;
        $dataobj->changed_on = date('Y-m-d H:i:s');
        ensure_record_exists('watchlist_queue', $whereobj, $dataobj);
    }
}

/**
 * is called by the cron-job to process the notifications stored into
 * watchlist_queue.
 */
function watchlist_process_notifications() {
    $delayMin = get_config('watchlistnotification_delay');
    $comparetime = time() - $delayMin * 60;

    $sql = "SELECT usr, view, MAX(changed_on) AS time
            FROM {watchlist_queue}
            GROUP BY usr, view";
    $results = get_records_sql_array($sql, array());

    if (false === $results) {
        return;
    }

    foreach ($results as $viewuserdaterow) {
        if ($viewuserdaterow->time > date('Y-m-d H:i:s', $comparetime)) {
            continue;
        }

        // don't send a notification if only blockinstances are referenced
        // that were deleted (block exists but corresponding
        // block_instance doesn't)
        $sendnotification = false;

        $blockinstance_ids = get_column('watchlist_queue', 'block', 'usr', $viewuserdaterow->usr, 'view', $viewuserdaterow->view);
        if (is_array($blockinstance_ids)) {
            $blockinstance_ids = array_unique($blockinstance_ids);
        }

        $viewuserdaterow->blocktitles = array();

        // need to check if view has an owner, group or institution
        $view = get_record('view', 'id', $viewuserdaterow->view);
        if (empty($view->owner) && empty($view->group) && empty($view->institution)) {
            continue;
        }
        // ignore root pages, owner = 0, this account is not meant to produce content
        if (isset($view->owner) && empty($view->owner)) {
            continue;
        }
        // Ignore system templates, institution = 'mahara' and template = 2
        require_once(get_config('libroot') . 'view.php');
        if (isset($view->institution)
            && $view->institution == 'mahara'
            && $view->template == View::SITE_TEMPLATE) {
            continue;
        }

        foreach ($blockinstance_ids as $blockinstance_id) {
            if (empty($blockinstance_id)) {
                // if no blockinstance is given, assume that the form itself
                // was changed, e.g. the theme, or a block was removed
                $sendnotification = true;
                continue;
            }
            require_once(get_config('docroot') . 'blocktype/lib.php');

            try {
                $block = new BlockInstance($blockinstance_id);
            }
            catch (BlockInstanceNotFoundException $exc) {
                // maybe the block was deleted
                continue;
            }

            $blocktype = $block->get('blocktype');
            $title = '';

            // try to get title rendered by plugin-class
            safe_require('blocktype', $blocktype);
            if (class_exists(generate_class_name('blocktype', $blocktype))) {
                $title = $block->get_title();
            }
            else {
                log_warn('class for blocktype could not be loaded: ' . $blocktype);
                $title = $block->get('title');
            }

            // if no title was given to the blockinstance, try to get one
            // from the artefact
            if (empty($title)) {
                $configdata = $block->get('configdata');

                if (array_key_exists('artefactid', $configdata)) {
                    try {
                        $artefact = $block->get_artefact_instance($configdata['artefactid']);
                        $title = $artefact->get('title');
                    }
                    catch(Exception $exc) {
                        log_warn('couldn\'t identify title of blockinstance ' .
                                 $block->get('id') . $exc->getMessage());
                    }
                }
            }

            // still no title, maybe the default-name for the blocktype
            if (empty($title)) {
                $title = get_string('title', 'blocktype.' . $blocktype);
            }

            // no title could be retrieved, so let's tell the user at least
            // what type of block was changed
            if (empty($title)) {
                $title = '[' . $blocktype . '] (' .
                    get_string('nonamegiven', 'activity') . ')';
            }

            $viewuserdaterow->blocktitles[] = $title;
            $sendnotification = true;
        }

        // only send notification if there is something to talk about (don't
        // send notification for example when new blockelement was aborted)
        if ($sendnotification) {
            try{
                $watchlistnotification = new ActivityTypeWatchlistnotification($viewuserdaterow, false);
                $watchlistnotification->notify_users();
            }
            catch (ViewNotFoundException $exc) {
                // Seems like the view has been deleted, don't do anything
            }
            catch (SystemException $exc) {
                // if the view that was changed doesn't have an owner
            }
        }

        delete_records('watchlist_queue', 'usr', $viewuserdaterow->usr, 'view', $viewuserdaterow->view);
    }
}

function activity_get_viewaccess_users($view) {
    require_once(get_config('docroot') . 'lib/group.php');
    $sql = "SELECT userlist.userid, usr.*, actpref.method, accpref.value AS lang
                FROM (
                    SELECT friend.usr1 AS userid
                      FROM {view} view
                      JOIN {view_access} access ON (access.view = view.id AND access.accesstype = 'friends')
                      JOIN {usr_friend} friend ON (view.owner = friend.usr2 AND view.id = ?)
                    UNION
                    SELECT friend.usr2 AS userid
                      FROM {view} view
                      JOIN {view_access} access ON (access.view = view.id AND access.accesstype = 'friends')
                      JOIN {usr_friend} friend ON (view.owner = friend.usr1 AND view.id = ?)
                    UNION
                    SELECT access.usr AS userid
                      FROM {view_access} access
                     WHERE access.view = ?
                    UNION
                    SELECT members.member AS userid
                      FROM {view_access} access
                      JOIN {group} grp ON (access.group = grp.id AND grp.deleted = 0 AND access.view = ?)
                      JOIN {group_member} members ON (grp.id = members.group AND members.member <> CASE WHEN access.usr IS NULL THEN -1 ELSE access.usr END)
                     WHERE (access.role IS NULL OR access.role = members.role) AND
                      (grp.viewnotify = " . GROUP_ROLES_ALL . "
                       OR (grp.viewnotify = " . GROUP_ROLES_NONMEMBER . " AND (members.role = 'admin' OR members.role = 'tutor'))
                       OR (grp.viewnotify = " . GROUP_ROLES_ADMIN . " AND members.role = 'admin')
                      )
                ) AS userlist
                JOIN {usr} usr ON usr.id = userlist.userid
                LEFT JOIN {usr_activity_preference} actpref ON actpref.usr = usr.id
                LEFT JOIN {activity_type} acttype ON actpref.activity = acttype.id AND acttype.name = 'viewaccess'
                LEFT JOIN {usr_account_preference} accpref ON accpref.usr = usr.id AND accpref.field = 'lang'";
    $values = array($view, $view, $view, $view);
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

/**
 * To implement a new activity type, you must subclass this class. Your subclass
 * MUST at minimum include the following:
 *
 * 1. Override the __construct method with one which first calls parent::__construct
 *    and then populates $this->users with the list of recipients for this activity.
 *
 * 2. Implement the get_required_parameters method.
 */
abstract class ActivityType {

    /**
     * NOTE: Child classes MUST call the parent constructor, AND populate
     * $this->users with a list of user records which should receive the message!
     *
     * @param array $data The data needed to send the notification
     * @param boolean $cron Indicates whether this is being called by the cron job
     */
    public function __construct($data, $cron=false) {
        $this->cron = $cron;
        $this->set_parameters($data);
        $this->ensure_parameters();
        $this->activityname = strtolower(substr(get_class($this), strlen('ActivityType')));
    }

    /**
     * This method should return an array which names the fields that must be present in the
     * $data that was passed to the class's constructor. It should include all necessary data
     * to determine the recipient(s) of the notification and to determine its content.
     *
     * @return array
     */
    abstract function get_required_parameters();

    /**
     * The number of users in a split chunk to notify
     */
    const USERCHUNK_SIZE = 1000;

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
    protected $last_processed_userid;
    protected $activity_queue_id;
    protected $overridemessagecontents;
    protected $parent;
    protected $defaultmethod;

    public function get_id() {
        if (!isset($this->id)) {
            $tmp = activity_locate_typerecord($this->get_type());
            $this->id = $tmp->id;
        }
        return $this->id;
    }

    public function get_default_method() {
        if (!isset($this->defaultmethod)) {
            $tmp = activity_locate_typerecord($this->get_id());
            $this->defaultmethod = $tmp->defaultmethod;
        }
        return $this->defaultmethod;
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
                // Allow some string parameters to be specified in $this->strings
                if (!in_array($param, array('subject', 'message', 'urltext')) || empty($this->strings->{$param}->key)) {
                    throw new ParamOutOfRangeException(get_string('missingparam', 'activity', $param, $this->get_type()));
                }
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
        $def = $stringdef;
        if (!is_object($this->strings)) {
            $this->strings = new stdClass();
        }
        $this->strings->urltext = (object) $def;
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

    /**
     * Rewrite $this->url with the ID of the internal notification record for this activity.
     * (Generally so that you can make a URL that sends the user to the Mahara inbox page
     * for this message.)
     *
     * @param int $internalid
     * @return boolean True if $this->url was updated, False if not.
     */
    protected function update_url($internalid) {
        return false;
    }

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
            // If method is not set then either the user has selected 'none' or their setting has not been set (so use default).
            if ($record = get_record('usr_activity_preference', 'usr', $user->id, 'activity', $this->get_id())) {
                $user->method = $record->method;
                if (empty($user->method)) {
                    // The user specified 'none' as their notification type.
                    return;
                }
            }
            else {
                $user->method = $this->get_default_method();
                if (empty($user->method)) {
                    // The default notification type is 'none' for this activity type.
                    return;
                }
            }
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
                static $badnotification = false;
                static $adminnotified = array();
                // We don't mind other notification methods failing, as it'll
                // go into the activity log as 'unread'
                $changes->read = 0;
                update_record('notification_internal_activity', $changes);
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
    }

    /**
     * Sound out notifications to $this->users.
     * Note that, although this has batching properties built into it with USERCHUNK_SIZE,
     * it's also recommended to update a bulk ActivityType's constructor to limit the total
     * number of records pulled from the database.
     */
    public function notify_users() {
        safe_require('notification', 'internal');
        $this->type = $this->get_id();

        if ($this->cron) {
            // Sort the list of users to notify by userid
            uasort($this->users, function($a, $b) {return $a->id > $b->id;});
            // Notify a chunk of users
            $num_processed_users = 0;
            $last_processed_userid = 0;
            foreach ($this->users as $user) {
                if ($this->last_processed_userid && ($user->id <= $this->last_processed_userid)) {
                    continue;
                }
                if ($num_processed_users < ActivityType::USERCHUNK_SIZE) {
                    // Immediately update the last_processed_userid in the activity_queue
                    // to prevent duplicated notifications
                    $last_processed_userid = $user->id;
                    update_record('activity_queue', array('last_processed_userid' => $last_processed_userid), array('id' => $this->activity_queue_id));
                    $this->notify_user($user);
                    $num_processed_users++;
                }
                else {
                    break;
                }
            }
            return $last_processed_userid;
        }
        else {
            while (!empty($this->users)) {
                $user = array_shift($this->users);
                $this->notify_user($user);
            }
        }
        return 0;
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
            $this->url = profile_url($this->fromuser, false);
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
            $this->url = $this->view->get_url(false, true) . '&objection=1';
        }
        else {
            $this->url = 'artefact/artefact.php?artefact=' . $this->artefact->get('id') . '&view=' . $this->view->get('id') . '&objection=1';
        }

        if (empty($this->strings->subject)) {
            $this->overridemessagecontents = true;
            $viewtitle = $this->view->get('title');
            $this->strings = new stdClass();
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
        $reporterurl = profile_url($this->reporter);
        $ctime = strftime(get_string_from_language($user->lang, 'strftimedaydatetime'), $this->ctime);
        if (empty($this->artefact)) {
            return get_string_from_language(
                $user->lang, 'objectionablecontentviewtext', 'activity',
                $this->view->get('title'), display_default_name($this->reporter), $ctime,
                $this->message, $this->view->get_url(true, true) . "&objection=1", $reporterurl
            );
        }
        else {
            return get_string_from_language(
                $user->lang, 'objectionablecontentviewartefacttext', 'activity',
                $this->view->get('title'), $this->artefact->get('title'), display_default_name($this->reporter), $ctime,
                $this->message, get_config('wwwroot') . "artefact/artefact.php?artefact=" . $this->artefact->get('id') . "&view=" . $this->view->get('id') . "&objection=1", $reporterurl
            );
        }
    }

    public function get_htmlmessage($user) {
        $viewtitle = hsc($this->view->get('title'));
        $reportername = hsc(display_default_name($this->reporter));
        $reporterurl = profile_url($this->reporter);
        $ctime = strftime(get_string_from_language($user->lang, 'strftimedaydatetime'), $this->ctime);
        $message = hsc($this->message);
        if (empty($this->artefact)) {
            return get_string_from_language(
                $user->lang, 'objectionablecontentviewhtml', 'activity',
                $viewtitle, $reportername, $ctime,
                $message, $this->view->get_url(true, true) . "&objection=1", $viewtitle,
                $reporterurl, $reportername
            );
        }
        else {
            return get_string_from_language(
                $user->lang, 'objectionablecontentviewartefacthtml', 'activity',
                $viewtitle, hsc($this->artefact->get('title')), $reportername, $ctime,
                $message, get_config('wwwroot') . "artefact/artefact.php?artefact=" . $this->artefact->get('id') . "&view=" . $this->view->get('id') . "&objection=1", hsc($this->artefact->get('title')),
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
            $this->url = 'admin/users/institutionusers.php';
            $this->users = activity_get_users($this->get_id(), null, null, null,
                                              array($this->institution->name));
            $this->add_urltext(array('key' => 'institutionmembers', 'section' => 'admin'));
        } else if ($this->messagetype == 'invite') {
            $this->url = 'account/institutions.php';
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
            return $this->get_subject($user) .' '. get_string_from_language($user->lang, 'institutionrequestmessage', 'activity', $this->url);
        } else if ($this->messagetype == 'invite') {
            return $this->get_subject($user) .' '. get_string_from_language($user->lang, 'institutioninvitemessage', 'activity', $this->url);
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
        $this->url = 'user/sendmessage.php?id=' . $this->userfrom . '&replyto=' . $internalid . '&returnto=inbox';
        return true;
    }

    public function get_required_parameters() {
        return array('message', 'userto', 'userfrom');
    }

}

class ActivityTypeWatchlist extends ActivityType {

    protected $view;

    private $ownerinfo;
    private $viewinfo;

    /**
     * @param array $data Parameters:
     *                    - view (int)
     */
    public function __construct($data, $cron) {
        parent::__construct($data, $cron);

        require_once('view.php');
        if ($this->viewinfo = new View($this->view)) {
            $this->ownerinfo = hsc($this->viewinfo->formatted_owner());
        }
        if (empty($this->ownerinfo)) {
            if (!empty($this->cron)) { // probably deleted already
                return;
            }
            throw new ViewNotFoundException(get_string('viewnotfound', 'error', $this->view));
        }
        $viewurl = $this->viewinfo->get_url(false);

        // mysql compatibility (sigh...)
        $casturl = 'CAST(? AS TEXT)';
        if (is_mysql()) {
            $casturl = '?';
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
        $this->users = get_records_sql_array(
            $sql,
            array($viewurl, $this->get_id(), $this->view)
        );

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
        return get_string_from_language($user->lang, 'newwatchlistmessageview1', 'activity',
                                        $this->viewinfo->get('title'), $this->ownerinfo);
    }

    public function get_required_parameters() {
        return array('view');
    }
}

/**
 * extending ActivityTypeWatchlist to reuse the funcinality and structure
 */
class ActivityTypeWatchlistnotification extends ActivityTypeWatchlist{
    protected $view;
    protected $viewinfo;
    protected $blocktitles = array();
    protected $usr;

    /**
     * @param array $data Parameters:
     *                    - view (int)
     *                    - blocktitles (array: int)
     *                    - usr (int)
     */
    public function __construct($data, $cron) {
        parent::__construct($data, $cron);

        $this->blocktitles = $data->blocktitles;
        $this->usr = $data->usr;


        $this->viewinfo = new View($this->view);
    }

    /**
     * override function get_message to add information about the changed
     * blockinstances
     *
     * @param type $user
     * @return type
     */
    public function get_message($user) {
        $message = get_string_from_language($user->lang, 'newwatchlistmessageview1', 'activity',
                                        $this->viewinfo->get('title'), display_name($this->usr, $user));

        try {
            foreach ($this->blocktitles as $blocktitle) {
                $message .= "\n" . get_string_from_language($user->lang, 'blockinstancenotification', 'activity', $blocktitle);
            }
        }
        catch(Exception $exc) {
            var_log(var_export($exc, true));
        }

        return $message;
    }

    /**
     * overwrite get_type to obfuscate that we are not really an Activity_type
     */
    public function get_type() {
        return('watchlist');
    }
}

class ActivityTypeViewAccess extends ActivityType {

    protected $view;
    protected $oldusers; // this can be empty though
    protected $views; // optional array of views by id being changed

    private $title, $ownername;

    private $incollection = false;
    /**
     * @param array $data Parameters:
     *                    - view (int)
     *                    - oldusers (array of user IDs)
     */
    public function __construct($data, $cron=false) {
        parent::__construct($data, $cron);
        if (!$viewinfo = new View($this->view)) {
            if (!empty($this->cron)) { // probably deleted already
                return;
            }
            throw new ViewNotFoundException(get_string('viewnotfound', 'error', $this->view));
        }
        $this->url = $viewinfo->get_url(false);
        $this->users = array_diff_key(
            activity_get_viewaccess_users($this->view),
            $this->oldusers
        );
        if ($viewinfo->get_collection()) {
            $this->incollection = true;
            $this->title = $viewinfo->get_collection()->get('name');
            $this->add_urltext(array('key' => 'Collection', 'section' => 'collection'));
        }
        else {
            $this->title = $viewinfo->get('title');
            $this->add_urltext(array('key' => 'View', 'section' => 'view'));
        }
        $this->ownername = $viewinfo->formatted_owner();
    }

    public function get_view_titles() {
        $views = (!empty($this->views) && is_array($this->views) && sizeof($this->views) > 1) ? $this->views : false;
        if (!$views) {
            return false;
        }
        $titles = array();
        foreach ($views as $view) {
            $titles[$view['id']] = $view['title'];
        }
        return $titles;
    }
    public function get_subject($user) {
        if ($titles = $this->get_view_titles()) {
            // because we may be updating access rules on pages both in and not in a collection
            // it is easiest just to return a list of the pages we have updated
            return get_string('newviewaccesssubjectviews', 'activity', implode('", "', $titles));
        }
        else {
            return $this->incollection ? get_string('newcollectionaccesssubject', 'activity', $this->title) : get_string('newviewaccesssubject1', 'activity', $this->title);
        }
    }

    public function get_message($user) {
        $title = $this->title;
        $plural = '';
        if ($titles = $this->get_view_titles()) {
            $title = implode('", "', $titles);
            $plural = 'views';
        }
        $newaccessmessagestr = $this->incollection ? 'newcollectionaccessmessage' . $plural : 'newviewaccessmessage' . $plural;
        if ($this->ownername) {
            return get_string_from_language($user->lang, $newaccessmessagestr, 'activity',
                                            $title, $this->ownername, $this->title);
        }
        $newaccessmessagenoownerstr = $this->incollection ? 'newcollectionaccessmessagenoowner' : 'newviewaccessmessagenoowner' . $plural;
        return get_string_from_language($user->lang, $newaccessmessagenoownerstr, 'activity', $title, $this->title);
    }

    public function get_required_parameters() {
        return array('view', 'oldusers');
    }
}

class ActivityTypeGroupMessage extends ActivityType {

    protected $group;
    protected $roles;
    protected $deletedgroup;

    /**
     * @param array $data Parameters:
     *                    - group (integer)
     *                    - roles (list of roles)
     */
    public function __construct($data, $cron=false) {
        require_once('group.php');

        parent::__construct($data, $cron);
        $members = group_get_member_ids($this->group, isset($this->roles) ? $this->roles : null, $this->deletedgroup);
        if (!empty($members)) {
            $this->users = activity_get_users($this->get_id(), $members);
        }
    }

    public function get_required_parameters() {
        return array('group');
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
    $message = preg_replace('/<br( ?\/)?>/', '', $message);
    $message = preg_replace('/^(\s|&nbsp;|\xc2\xa0)*/', '', $message);
    // convert any htmlspecialchars back so we don't double escape as part of format_whitespace()
    $message = htmlspecialchars_decode($message);
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
        $types = explode(',', preg_replace('/[^a-z,]+/', '', $type));
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
    safe_require('module', 'multirecipientnotification');
    if (PluginModuleMultirecipientnotification::is_active()) {
        $paginationurl = get_config('wwwroot') . 'module/multirecipientnotification/inbox.php?type=' . $type;
    }
    else {
        $paginationurl = get_config('wwwroot') . 'account/activity/index.php?type=' . $type;
    }


    $pagination = build_pagination(array(
        'id'         => 'activitylist_pagination',
        'url'        => $paginationurl,
        'jsonscript' => 'account/activity/index.json.php',
        'datatable'  => 'activitylist',
        'count'      => $count,
        'limit'      => $limit,
        'offset'     => $offset,
        'jumplinks'  =>  6,
        'numbersincludeprevnext' => 2,
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

/**
 * Get a table of elements that can be used to set notification settings for the specified user, or for the site defaults.
 *
 * @param object $user whose settings are being displayed or...
 * @param bool $sitedefaults true if the elements should be loaded from the site default settings.
 * @return array of elements suitable for adding to a pieforms form.
 */
function get_notification_settings_elements($user = null, $sitedefaults = false) {
    global $SESSION;

    if ($user == null && !$sitedefaults) {
        throw new SystemException("Function get_notification_settings_elements requires a user or sitedefaults must be true");
    }

    if ($sitedefaults || $user->get('admin') || $user->is_institutional_admin()) {
        $activitytypes = get_records_array('activity_type', '', '', 'id');
    }
    else {
        $activitytypes = get_records_array('activity_type', 'admin', 0, 'id');
        $activitytypes = get_special_notifications($user, $activitytypes);
    }

    $notifications = plugins_installed('notification');

    $elements = array();

    $options = array();
    foreach ($notifications as $notification) {
        $options[$notification->name] = get_string('name', 'notification.' . $notification->name);
    }

    $maildisabledmsg = false;
    foreach ($activitytypes as $type) {
        // Find the default value.
        if ($sitedefaults) {
            $dv = $type->defaultmethod;
        }
        else {
            $dv = $user->get_activity_preference($type->id);
            if ($dv === false) {
                $dv = $type->defaultmethod;
            }
        }
        if (empty($dv)) {
            $dv = 'none';
        }

        // Create one maildisabled error message if applicable.
        if (!$sitedefaults && $dv == 'email' && !isset($maildisabledmsg) && get_account_preference($user->get('id'), 'maildisabled')) {
            $SESSION->add_error_msg(get_string('maildisableddescription', 'account', get_config('wwwroot') . 'account/index.php'), false);
            $maildisabledmsg = true;
        }

        // Calculate the key.
        if (empty($type->plugintype)) {
            $key = "activity_{$type->name}";
        }
        else {
            $key = "activity_{$type->name}_{$type->plugintype}_{$type->pluginname}";
        }

        // Find the row title and section.
        $rowtitle = $type->name;
        if (!empty($type->plugintype)) {
            $section = $type->plugintype . '.' . $type->pluginname;
        }
        else {
            $section = 'activity';
        }

        // Create the element.
        $elements[$key] = array(
            'defaultvalue' => $dv,
            'type' => 'select',
            'title' => get_string('type' . $rowtitle, $section),
            'options' => $options,
            'help' => true,
        );

        // Set up the help.
        $elements[$key]['helpformname'] = 'activityprefs';
        if (empty($type->plugintype)) {
            $elements[$key]['helpplugintype'] = 'core';
            $elements[$key]['helppluginname'] = 'account';
        }
        else {
            $elements[$key]['helpplugintype'] = $type->plugintype;
            $elements[$key]['helppluginname'] = $type->pluginname;
        }

        // Add the 'none' option if applicable.
        if ($type->allownonemethod) {
            $elements[$key]['options']['none'] = get_string('none');
        }
    }

    $title = array();
    foreach ($elements as $key => $row) {
      $title[$key] = $row['title'];
    }
    array_multisort($title, SORT_ASC, $elements);

    return $elements;
}

/**
 * Save the notification settings.
 *
 * @param array $values returned from submitting a pieforms form.
 * @param object $user whose settings are being updated or...
 * @param bool $sitedefaults true if the elements should be saved to the site default settings.
 */
function save_notification_settings($values, $user = null, $sitedefaults = false) {
    if ($user == null && !$sitedefaults) {
        throw new SystemException("Function save_notification_settings requires a user or sitedefaults must be true");
    }

    if ($sitedefaults || $user->get('admin') || $user->is_institutional_admin()) {
        $activitytypes = get_records_array('activity_type');
    }
    else {
        $activitytypes = get_records_array('activity_type', 'admin', 0);
        $activitytypes = get_special_notifications($user, $activitytypes);
    }

    foreach ($activitytypes as $type) {
        if (empty($type->plugintype)) {
            $key = "activity_{$type->name}";
        }
        else {
            $key = "activity_{$type->name}_{$type->plugintype}_{$type->pluginname}";
        }
        $value = $values[$key] == 'none' ? null : $values[$key];
        if ($sitedefaults) {
            execute_sql("UPDATE {activity_type} SET defaultmethod = ? WHERE id = ?", array($value, $type->id));
        }
        else {
            $user->set_activity_preference($type->id, $value);
        }
    }
}

/**
 * Get special case activity types.
 * Currently checks if a non admin is an admin/moderator of a group and
 * adds that notification type to the array.
 *
 * @param object $user whose settings are being displayed
 * @param array  $activitytypes array of elements
 * @return array $activitytypes amended array of elements
 */
function get_special_notifications($user, $activitytypes) {
    if (empty($user)) {
        return $activitytypes;
    }
    // Check if the non-admin is a group admin/moderator in any of their groups
    if ($user->get('grouproles') !== null) {
        $groups = $user->get('grouproles');
        $allowreportpost = false;
        foreach ($groups as $group => $role) {
            if ($role == 'admin') {
                $allowreportpost = true;
                break;
            }
            else if ($moderator = get_record_sql("SELECT i.id
                FROM {interaction_forum_moderator} m, {interaction_instance} i
                WHERE i.id = m.forum AND i.group = ? AND i.deleted = 0 and m.user = ?", array($group, $user->get('id')))) {
                $allowreportpost = true;
                break;
            }
        }
        if ($allowreportpost) {
            // Add the reportpost option to the $activitytypes
            $reportpost = get_records_array('activity_type', 'name', 'reportpost', 'id');
            $activitytypes = array_merge($activitytypes, $reportpost);
        }
    }
    return $activitytypes;
}
