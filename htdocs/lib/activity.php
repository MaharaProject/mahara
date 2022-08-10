<?php
/**
 * Activity classes for notification types
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// Base activity type classes.
require_once(get_config('libroot') . '/activity/ActivityType.php');
require_once(get_config('libroot') . '/activity/ActivityTypeAdmin.php');
require_once(get_config('libroot') . '/activity/ActivityTypePlugin.php');
// Specific activity types.
require_once(get_config('libroot') . '/activity/ActivityTypeContactus.php');
require_once(get_config('libroot') . '/activity/ActivityTypeGroupMessage.php');
require_once(get_config('libroot') . '/activity/ActivityTypeInstitutionmessage.php');
require_once(get_config('libroot') . '/activity/ActivityTypeMaharamessage.php');
require_once(get_config('libroot') . '/activity/ActivityTypeObjectionable.php');
require_once(get_config('libroot') . '/activity/ActivityTypeUsermessage.php');
require_once(get_config('libroot') . '/activity/ActivityTypeViewAccess.php');
require_once(get_config('libroot') . '/activity/ActivityTypeViewAccessRevoke.php');
require_once(get_config('libroot') . '/activity/ActivityTypeVirusRelease.php');
require_once(get_config('libroot') . '/activity/ActivityTypeVirusRepeat.php');
require_once(get_config('libroot') . '/activity/ActivityTypeWatchlistnotification.php');

defined('INTERNAL') || die();

/**
 * This is the function to call whenever anything happens
 * that is going to end up on a user's activity page.
 *
 * @param string $activitytype type of activity
 * @param object $data must contain the fields specified by get_required_parameters of the activity type subclass.
 * @param string $plugintype
 * @param string $pluginname
 * @param bool $delay
 *
 * NOTE: If the $data object contains an 'id' property this needs to be the id of the activitytype
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
        $delayed = new stdClass();
        $delayed->type = $at->id;
        $delayed->data = serialize($data);
        $delayed->ctime = db_format_timestamp(time());
        if (!record_exists('activity_queue', 'type', $delayed->type, 'data', $delayed->data)) {
            $views = isset($data->views) ? $data->views : array();
            if ($delayed->type == 4 && isset($views[0]['collection_id'])) {
                // try to ensure we don't end up with multiple notifications when sharing collections
                $sql = 'SELECT * FROM {activity_queue} WHERE type = ? AND data like ';
                $sql .= "'%" . '"collection_id"' . ";s:%" . '"' . $views[0]['collection_id'] . '"' . ";%'";
                if (!record_exists_sql($sql, array($delayed->type))) {
                    insert_record('activity_queue', $delayed);
                }
            }
            else {
                insert_record('activity_queue', $delayed);
            }
        }
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
 * This function returns an array of users who subscribe to a particular activitytype
 * including the notification method they are using to subscribe to it.
 *
 * @param int $activitytype the id of the activity type
 * @param array $userids an array of userids to filter by
 * @param array $userobjs an array of user objects to filterby - the userobjs need to be converted to stdclass via ->to_stdClass()
 * @param bool $adminonly whether to filter by admin flag
 * @param array $admininstitutions list of institution names to get admins for
 * @param bool $includesuspendedusers whether to include suspended people in the results
 * @return array of users
 */
function activity_get_users($activitytype, $userids=null, $userobjs=null, $adminonly=false,
                            $admininstitutions = array(), $includesuspendedusers=false) {
    $values = array($activitytype);
    $sql = '
        SELECT
            u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.admin, u.staff,
            u.suspendedctime,
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
        $values = array_merge($values, db_array_to_fields($userobjs, 'id'));
    }
    else if (!empty($userids) && is_array($userids)) {
        $sql .= ' AND u.id IN (' . implode(',',db_array_to_ph($userids)) . ')';
        $values = array_merge($values, $userids);
    }
    if (!$includesuspendedusers) {
        $sql .= ' AND u.suspendedctime IS NULL ';
    }
    if (!empty($admininstitutions)) {
        $sql .= '
        GROUP BY
            u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.admin, u.staff,
            u.suspendedctime,
            p.method, ap.value, apm.value, aic.value, h.appname
        HAVING (SUM(ui.admin) > 0)';
    } else if ($adminonly) {
        $sql .= ' AND u.admin = 1';
    }
    return get_records_sql_assoc($sql, $values);
}


/**
 * This function inserts a default set of activity preferences for a given user
 * @param mixed $eventdata  List of event types and their settings
 */
function activity_set_defaults($eventdata) {
    $user_id = is_object($eventdata) ? $eventdata->id : $eventdata['id'];
    $activitytypes = get_records_array('activity_type', 'admin', 0);

    foreach ($activitytypes as $type) {
        insert_record('usr_activity_preference', (object)array(
            'usr' => $user_id,
            'activity' => $type->id,
            'method' => $type->defaultmethod,
        ));
    }
}

/**
 * This function inserts the default set of administrator activity preferences for the given people
 * @param array $userids  List of people's IDs
 */
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

/**
 * Process the queue of delayed activity notifications
 */
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
 * The event-listener is called when an artefact is changed or a block instance
 * is committed. Saves the view, the block instance, user and time into the
 * database
 *
 * @global User $USER
 * @param string|BlockInstance|ArtefactType $event
 */
function watchlist_record_changes($event) {
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

        // Check if someone has added this view to their watchlist
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
 * Is triggered when a blockinstance is deleted. Deletes all watchlist_queue
 * entries that refer to this blockinstance
 *
 * @param BlockInstance $block
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

    // Get the latest changes on views being watched
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

/**
 * Get the people that have access to the view which the activity is related to
 * @param integer $view  The view ID
 * @return array The database array of people based on view access rules
 */
function activity_get_viewaccess_users($view) {
    require_once(get_config('docroot') . 'lib/group.php');
    $sql = "SELECT userlist.userid, usr.*, actpref.method, accpref.value AS lang,
              aic.value AS mnethostwwwroot, h.appname AS mnethostapp
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
                LEFT JOIN {usr_account_preference} accpref ON accpref.usr = usr.id AND accpref.field = 'lang'
                LEFT JOIN {auth_instance} ai ON ai.id = usr.authinstance
                LEFT OUTER JOIN {auth_instance_config} aic ON (aic.instance = ai.id AND aic.field = 'wwwroot')
                LEFT OUTER JOIN {host} h ON aic.value = h.wwwroot";
    $values = array($view, $view, $view, $view);
    if (!$u = get_records_sql_assoc($sql, $values)) {
        $u = array();
    }
    return $u;
}

/**
 * Return the minimum and maximum access times if they exist for the page
 * based on user getting access. To be used with view access notifications
 *
 * @param string $viewid ID of the view
 * @param string $userid ID of the user
 * @return array Min and max access dates
 */
function activity_get_viewaccess_user_dates($viewid, $userid) {
    if ($results = get_records_sql_array("
        SELECT MIN(startdate) AS mindate, MAX(stopdate) as maxdate FROM (
            SELECT startdate, stopdate FROM {view}
            WHERE id = ?
            UNION
            SELECT startdate, stopdate FROM {view_access}
            WHERE view = ? AND usr = ?
            UNION
            SELECT startdate, stopdate FROM {view_access} va
            JOIN {group_member} gm ON gm.group = va.group
            WHERE va.view = ? AND gm.member = ?
            UNION
            SELECT startdate, stopdate FROM {view_access} va
            JOIN {usr_institution} ui ON ui.institution = va.institution
            WHERE va.view = ? and ui.usr = ?
            UNION
            SELECT startdate, stopdate FROM {view_access}
            WHERE view = ? AND accesstype IN ('loggedin','public')
            UNION
            SELECT startdate, stopdate FROM {view_access}
            WHERE accesstype = 'friends' AND view = ?
            AND EXISTS (
                SELECT * FROM {usr_friend}
                WHERE (usr1 = (SELECT owner FROM {view} WHERE id = ?) AND usr2 = ?)
                OR (usr2 = (SELECT owner FROM {view} WHERE id = ?) AND usr1 = ?)
            )
        ) AS dates", array($viewid, $viewid, $userid, $viewid, $userid, $viewid, $userid, $viewid, $viewid, $viewid, $userid, $viewid, $userid))
    ) {
        return array('mindate' => $results[0]->mindate,
                     'maxdate' => $results[0]->maxdate);
    }
    return array('mindate' => null,
                 'maxdate' => null);
}

/**
 * Find a valid activity type record
 * @param mixed $activitytype  The type of activity we want to send the notification for
 * @param string|null $plugintype Find the activity type by plugin type
 * @param string|null $pluginname Find the activity type by plugin name
 * @throws SystemException
 * @return object A Database row object
 */
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

/**
 * Format the notification so that it displays ok in both inbox and email
 * @param string $message    The body message of the notification
 * @param string|null $type  The message type
 * @return string            The formatted message
 */
function format_notification_whitespace($message, $type=null) {
    $message = preg_replace('/<br( ?\/)?>/', '', $message);
    $message = preg_replace('/^(\s|&nbsp;|\xc2\xa0)*/', '', $message);
    // convert any htmlspecialchars back so we don't double escape as part of format_whitespace()
    $message = htmlspecialchars_decode($message);
    $message = format_whitespace($message);
    // @todo Sensibly distinguish html notifications, notifications where the full text
    // appears on another page and this is just an abbreviated preview, and text-only
    // notifications where the entire text must appear here because there's nowhere else
    // to see it.
    $replace = ($type == 'newpost' || $type == 'feedback') ? '<br>' : '<br><br>';
    return preg_replace('/(<br( ?\/)?>\s*){2,}/', $replace, $message);
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

    if ($sitedefaults || $user->get('admin')) {
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
        if (!$sitedefaults && $dv == 'email' && !$maildisabledmsg && get_account_preference($user->get('id'), 'maildisabled')) {
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

    if ($sitedefaults || $user->get('admin')) {
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
    if ($user === null) {
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

    // If user is an institution admin, should receive objectionable material and contactus notifications
    if ($user->is_institutional_admin()) {
        $objectionable = get_records_array('activity_type', 'name', 'objectionable', 'id');
        $contactus = get_records_array('activity_type', 'name', 'contactus', 'id');
        $activitytypes = array_merge($activitytypes, $objectionable, $contactus);
    }

    return $activitytypes;
}

/**
 * Append the authentication method ID to the URL in the email
 * if the authentication method for the person has external login
 * so that they get redirected to the external login page if not
 * currently logged into Mahara
 * @param object $user A database object of a usr row
 * @param string $url  A URL string to update
 * @return string An updated URL
 */
function append_email_institution($user, $url) {
    if (!isset($user->id) || (isset($user->id) && empty($user->id))) {
        return $url;
    }
    // Ignore auth methods 'internal' and 'ldap' as they login direct with login box
    $local = array('internal', 'ldap');
    if ($auth = get_field_sql("SELECT ai.id
                                FROM {usr} u
                                JOIN {auth_instance} ai ON ai.id = u.authinstance
                                AND ai.authname NOT IN (" . join(',',  array_map('db_quote', $local)) . ")
                                AND u.id = ?", array($user->id))) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . 'authid=' . $auth;
    }
    return $url;
}
