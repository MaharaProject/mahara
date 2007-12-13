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
function activity_occurred($activitytype, $data) {
    if (!$at = get_record('activity_type', 'name', $activitytype)) {
        throw new Exception("Invalid activity type $activitytype");
    }

    if (!empty($at->delay)) {
        $delayed = new StdClass;
        $delayed->type = $activitytype;
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

    // mysql compatibility (sigh...)
    $casturl = 'CAST(? AS TEXT)';
    if (get_config('dbtype') == 'mysql') {
        $casturl = 'CAST(? AS CHAR)'; // note, NOT varchar
    }

    $data = (object)$data;
    if (is_string($activitytype)) {
        $activitytype = get_record('activity_type', 'name', $activitytype);
    }
    

    if (!is_object($activitytype)) {
        throw new InvalidArgumentException("Invalid activitytype $activitytype");
    }

    $users = array();

    if (!empty($activitytype->admin)) {
        // validation stuff
        switch ($activitytype->name) {
            case 'contactus':
                if (empty($data->message)) {
                    throw new InvalidArgumentException("Message was empty for activity type contactus");
                }
                $data->subject = get_string('newcontactusfrom', 'activity') . ' ' .$data->fromname 
                    . '<' . $data->fromemail .'>' . (isset($data->subject) ? ': ' . $data->subject : '');
                $data->message = $data->subject . "\n\n" . $data->message;
                $data->subject = get_string('newcontactus', 'activity');
                if (!empty($data->userfrom)) {
                    $data->url = get_config('wwwroot') . 'user/view.php?id=' . $data->userfrom;
                    $userid = $data->userfrom;
                }
                break;
            case 'objectionable':
                if (empty($data->view)) {
                    throw new InvalidArgumentException("Objectionable content requires an id of a view");
                }
                if (empty($data->message)) {
                    throw new InvalidArgumentException("Objectionable content requires a message");
                }
                if (!$view = get_record('view', 'id', $data->view, null, null, null, null, 'title,owner')) {
                    throw new InvalidArgumentException("Couldn't find view with id " . $data->view);
                }
                $userid = $view->owner;
                if (empty($data->artefact)) {
                    $data->url = get_config('wwwroot') . 'view/view.php?id=' . $data->view;
                    $data->subject = get_string('objectionablecontentview', 'activity') 
                        . ' ' . get_string('onview', 'activity') . ' ' . $view->title;
                }
                else {
                    $data->url = get_config('wwwroot') . 'view/view.php?artefact=' . $data->artefact . '&id=' . $data->view;
                    if (!$artefacttitle = get_field('artefact', 'title', 'id', $data->artefact)) {
                        throw new InvalidArgumentException("Couldn't find artefact with id " . $data->view);
                    }
                    $data->subject = get_string('objectionablecontentartefact', 'activity') 
                        . ' '  . get_string('onartefact', 'activity') . ' ' . $artefacttitle;
                }
                break;
            case 'virusrepeat':
                $userstring = $data->username . ' (' . $data->fullname . ') (userid:' . $data->userid . ')' ;
                $data->subject = get_string('virusrepeatsubject', 'mahara', $userstring);
                $data->message = get_string('virusrepeatmessage');
                $userid = $data->userid;
                break;
            case 'virusrelease':
                break;
        }
        if (empty($userid)) {
            $users = activity_get_users($activitytype->name, null, null, true);
        } else {
            $userinstitutions = get_column('usr_institution', 'institution', 'usr', $userid);
            $users = activity_get_users($activitytype->name, null, null, null, $userinstitutions);
        }
    }
    else {
        switch ($activitytype->name) {
            // easy ones first :)
            case 'maharamessage':
                if (!is_array($data->users) || empty($data->users)) {
                    throw new InvalidArgumentException("Mahara message activity type expects an array of users");
                }
                if (empty($data->subject)) {
                    throw new InvalidArgumentException("Mahara message activity type expects a subject");
                }
                if (empty($data->message)) {
                    throw new InvalidArgumentException("Mahara message activity type expects a message");
                }
                $users = activity_get_users($activitytype->name, $data->users);
                break;
            case 'institutionmessage':
                if ($data->messagetype == 'request') {
                    $userstring = $data->fullname . ' (' . $data->username . ')';
                    $data->subject = get_string('institutionrequestsubject', 'activity', $userstring, 
                                                $data->institution->displayname);
                    $data->message = get_string('institutionrequestmessage', 'activity');
                    $data->url = get_config('wwwroot') . 'admin/users/institutionusers.php';
                    $users = activity_get_users($activitytype->name, null, null, null,
                                                array($data->institution->name));
                } else if ($data->messagetype == 'invite') {
                    if (!is_array($data->users) || empty($data->users)) {
                        throw new InvalidArgumentException("Institution invitations expect an array of users");
                    }
                    $data->subject = get_string('institutioninvitesubject', 'activity', 
                                                $data->institution->displayname);
                    $data->message = get_string('institutioninvitemessage', 'activity');
                    $data->url = get_config('wwwroot') . 'account/index.php';
                    $users = activity_get_users($activitytype->name, $data->users);
                }
                break;
            case 'usermessage':
                if (!is_numeric($data->userto) || !is_numeric($data->userfrom)) {
                    throw new InvalidArgumentException("User message requires userto and userfrom to be set");
                }
                if (empty($data->subject)) {
                    $data->subject = get_string('newusermessage', 'mahara', display_name($data->userfrom));
                }
                if (empty($data->message)) {
                    throw new InvalidArgumentException("User message activity type expects a message");
                }
                $users = activity_get_users($activitytype->name, array($data->userto));
                if (empty($data->url)) {
                    // @todo when user messaging is implemented, this might change... 
                    $data->url = get_config('wwwroot') . 'user/view.php?id=' . $data->userfrom;
                }
                break;
            case 'feedback':
                if (empty($data->message)) {
                    throw new InvalidArgumentException("Feedbackactivity type expects a message");
                }
                if (empty($data->view)) {
                    throw new InvalidArgumentException("Feedback missing view id");
                }
                if (!empty($data->artefact)) { // feedback on artefact
                    $data->subject = get_string('newfeedbackonartefact', 'activity');
                    require_once(get_config('docroot') . 'artefact/lib.php');
                    $artefact = artefact_instance_from_id($data->artefact);
                    if ($artefact->feedback_notify_owner()) {
                        $userid = $artefact->get('owner');
                    }
                    else {
                        $userid = null;
                    }
                    $data->subject .= ' ' .$artefact->get('title');
                    if (empty($data->url)) {
                        // @todo this might change later
                        $data->url = get_config('wwwroot') . 'view/view.php?artefact=' 
                            . $data->artefact . '&id=' . $data->view;
                    }
                } 
                else { // feedback on view.
                    $data->subject = get_string('newfeedbackonview', 'activity');
                    if (!$view = get_record('view', 'id', $data->view)) {
                        throw new InvalidArgumentException("Couldn't find view with id " . $data->view);
                    }
                    $userid = $view->owner;
                    // Don't send a message if the view owner submitted the feedback
                    if ($data->author == $userid) {
                        $userid = null;
                    }
                    $data->subject .= ' ' .$view->title;
                    if (empty($data->url)) {
                        // @todo this might change later
                        $data->url = get_config('wwwroot') . 'view/view.php?id=' . $data->view;
                    }
                }
                if ($userid) {
                    $users = activity_get_users($activitytype->name, array($userid));
                } 
                else {
                    $users = array();
                }
                break;
            // and now the harder ones
            case 'watchlist':
                if (!empty($data->view)) {
                    if (empty($data->message)) {
                        throw new InvalidArgumentException("message must be provided for watchlist view");
                    }
                    $data->subject = get_string('newwatchlistmessagesubject', 'activity');
                    if (!$viewinfo = get_record_sql('SELECT u.*, v.title FROM {usr} u
                                                     JOIN {view} v ON v.owner = u.id
                                                     WHERE v.id = ?', array($data->view))) {
                        if (!empty($cron)) { // probably deleted already
                            return;
                        }
                        throw new InvalidArgumentException("Couldn't find view with id " . $data->view);
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
                    $users = get_records_sql_array($sql, 
                                                   array(get_config('wwwroot') . 'view/view.php?id=' 
                                                         . $data->view, 'watchlist', $data->view));
                    if (empty($users)) {
                        $users = array();
                    }
                    // ick
                    foreach ($users as $user) {
                        $user->message = display_name($viewinfo, $user) . ' ' . $data->message;
                    }
                } 
                else {
                    log_debug($data);
                    throw new InvalidArgumentException("Invalid watchlist type");
                }
                break;
            case 'newview':
                $data->oldusers = array();
            case 'viewaccess':
                if (!is_numeric($data->owner) || !is_numeric($data->view)) {
                    throw new InvalidArgumentException("view access activity type requires view and owner to be set");
                }
                if (!isset($data->oldusers)) {
                    throw new InvalidArgumentException("view access activity type requires oldusers to be set (even if empty)");
                }
                if (!$viewinfo = get_record_sql('SELECT u.*, v.title FROM {usr} u
                                                 JOIN {view} v ON v.owner = u.id
                                                 WHERE v.id = ?', array($data->view))) {
                    if (!empty($cron)) { // probably deleted already
                        return;
                    }
                    throw new InvalidArgumentException("Couldn't find view with id " . $data->view);
                }
                $data->message = get_string('newviewaccessmessage', 'activity')
                    . ' "' . $viewinfo->title . '" ' . get_string('ownedby', 'activity');
                $data->subject = get_string('newviewaccesssubject', 'activity');
                $data->url = get_config('wwwroot') . 'view/view.php?id=' . $data->view;
                $users = array_diff_key(activity_get_viewaccess_users($data->view, $data->owner, 'viewaccess'), $data->oldusers);
                if (empty($users)) {
                    $users = array();
                }
                // ick
                foreach ($users as $user) {
                    $user->message = $data->message . ' ' . display_name($viewinfo, $user);
                }
            case 'contactus':
                
                break;
                // @todo more here (admin messages!)
        }
    }
    if (empty($users)) {
        return;
    }
    safe_require('notification', 'internal', 'lib.php', 'require_once');
    $data->type = $activitytype->name;
    foreach ($users as $user) {
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
 * @param string $activitytype the name of the activity type
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
            u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.admin, u.staff, p.method
        FROM {usr} u
        LEFT JOIN {usr_activity_preference} p
            ON p.usr = u.id ' . (empty($admininstitutions) ? '' : '
        LEFT OUTER JOIN {usr_institution} ui
            ON (u.id = ui.usr
                AND ui.institution IN ('.join(',',array_map('db_quote',$admininstitutions)).'))') . '
        WHERE (p.activity = ? ' . ($adminonly ? '' : ' OR p.activity IS NULL') . ')';
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
            u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.admin, u.staff, p.method
        HAVING (u.admin = 1 OR SUM(ui.admin) > 0)';
    } else if ($adminonly) {
        $sql .= ' AND u.admin = 1';
    }
    return get_records_sql_array($sql, $values);
}

/**
 * this function inserts a default set of activity preferences for a given user
 * id
 */
function activity_set_defaults($user_id) {
    $activitytypes = get_records_array('activity_type', 'admin', 0);
    $haveemail = in_array('email', array_map(create_function('$a', 'return $a->name;'),
                                             plugins_installed('notification')));
    foreach ($activitytypes as $type) {
        if ($type->name == 'institutionmessage' && $haveemail) {
            $method = 'email';
        } else {
            $method = 'internal';
        }
        insert_record('usr_activity_preference', (object)array(
            'usr' => $user_id,
            'activity' => $type->name,
            'method' => $method,
        ));
    }
    
}


function activity_add_admin_defaults($userids) {
    $activitytypes = get_records_array('activity_type', 'admin', 1);
    foreach ($activitytypes as $type) {
        foreach ($userids as $id) {
            if (!record_exists('usr_activity_preference', 'usr', $id, 'activity', $type->name)) {
                insert_record('usr_activity_preference', (object)array(
                    'usr' => $id,
                    'activity' => $type->name,
                    'method' => 'internal',
                ));
            }
        }
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
    $values = array($owner, $owner, $owner, 'friends', $view, $view, $view, 0, 1, $view, $type);
    if (!$u = get_records_sql_assoc($sql, $values)) {
        $u = array();
    }
    return $u;
}

?>
