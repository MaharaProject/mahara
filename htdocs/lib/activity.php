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
 *  - <b>watchlist (artefact)</b> must contain $artefact (id of artefact) 
 *  -       and should also contain $subject (or a boring default will be used)
 *  - <b>watchlist (view) </b> must contain $view (id of view) 
    -       and should also contain $subject (or a boring default will be used)
 *  - <b>watchlist (community) </b> must contain $community (id of community)
    -       and should also contain $subject (or a boring default will be used)
 *  - <b>newview</b> must contain $owner userid of view owner AND $view (id of new view)
 *  - <b>viewaccess</b> must contain $owner userid of view owner AND $view (id of view) and $oldusers array of userids before access change was committed.
 */
function handle_activity($activitytype, $data, $cron=false) {

    $data = (object)$data;
    if (is_string($activitytype)) {
        $activitytype = get_record('activity_type', 'name', $activitytype);
    }
    

    if (!is_object($activitytype)) {
        throw new InvalidArgumentException("Invalid activitytype $activitytype");
    }

    $users = array();
    $prefix = get_config('dbprefix');

    if (!empty($activitytype->admin)) {
        $users = activity_get_users($activitytype->name, null, null, true);
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
                }
                break;
            case 'objectionable':
                if (empty($data->view)) {
                    throw new InvalidArgumentException("Objectionable content requires an id of a view");
                }
                if (empty($data->message)) {
                    throw new InvalidArgumentException("Objectionable content requires a message");
                }
                if (!$viewtitle = get_field('view', 'title', 'id', $data->view)) {
                    throw new InvalidArgumentException("Couldn't find view with id " . $data->view);
                }
                if (empty($data->artefact)) {
                    $data->url = get_config('wwwroot') . 'view/view.php?view=' . $data->view;
                    $data->subject = get_string('objectionablecontentview', 'activity') 
                        . ' ' . get_string('onview', 'activity') . $viewtitle;
                }
                else {
                    $data->url = get_config('wwwroot') . 'view/view.php?artefact=' . $data->artefact . '&view=' . $data->view;
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
                break;
            case 'virusrelease':
                break;
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
            case 'usermessage':
                if (!is_numeric($data->userto) || !is_numeric($data->userfrom)) {
                    throw new InvalidArgumentException("User message requires userto and userfrom to be set");
                }
                if (empty($data->subject)) {
                    throw new InvalidArgumentException("User message activity type expects a subject");
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
                    if (!$artefact = get_record('artefact', 'id', $data->artefact)) {
                        throw new InvalidArgumentException("Couldn't find artefact with id "  . $data->artefact);
                    }
                    $userid = $artefact->owner;
                    $data->subject .= ' ' .$artefact->title;
                    if (empty($data->url)) {
                        // @todo this might change later
                        $data->url = get_config('wwwroot') . 'view/view.php?artefact=' 
                            . $data->artefact . '&view=' . $data->view;
                    }
                } 
                else { // feedback on view.
                    $data->subject = get_string('newfeedbackonview', 'activity');
                    if (!$view = get_record('view', 'id', $data->view)) {
                        throw new InvalidArgumentException("Couldn't find view with id " . $data->view);
                    }
                    $userid = $view->owner;
                    $data->subject .= ' ' .$view->title;
                    if (empty($data->url)) {
                        // @todo this might change later
                        $data->url = get_config('wwwroot') . 'view/view.php?view=' . $data->view;
                    }
                }
                $users = activity_get_users($activitytype->name, array($userid));
                break;
            // and now the harder ones
            case 'watchlist':
                if (!empty($data->view)) {
                    if (empty($data->subject)) {
                        throw new InvalidArgumentException("subject must be provided for watchlist view");
                    }
                    $oldsubject = isset($data->subject) ? $data->subject : '';
                    $data->subject = get_string('watchlistmessageview', 'activity');
                    if (!$viewinfo = get_record_sql('SELECT u.*, v.title FROM ' . $prefix . 'usr u
                                                     JOIN ' . $prefix . 'view v ON v.owner = u.id
                                                     WHERE v.id = ?', array($data->view))) {
                        throw new InvalidArgumentException("Couldn't find view with id " . $data->view);
                    }
                    $data->message = $oldsubject . ' ' . get_string('onview', 'activity') 
                        . ' ' . $viewinfo->title . ' ' . get_string('ownedby', 'activity');
                    $sql = 'SELECT u.*, p.method, CAST(? AS TEXT)  AS url
                                FROM ' . $prefix . 'usr_watchlist_view wv
                                JOIN ' . $prefix . 'usr u
                                    ON wv.usr = u.id
                                LEFT JOIN ' . $prefix . 'usr_activity_preference p
                                    ON p.usr = u.id
                                WHERE (p.activity = ? OR p.activity IS NULL)
                                AND wv.view = ?
                           ';
                    $users = get_records_sql_array($sql, 
                                                   array(get_config('wwwroot') . 'view/view.php?view=' 
                                                         . $data->view, 'watchlist', $data->view));
                    if (empty($users)) {
                        $users = array();
                    }
                    // ick
                    foreach ($users as $user) {
                        $user->message = $data->message . ' ' . display_name($viewinfo, $user);
                    }
                } 
                else if (!empty($data->artefact)) {
                    $data->subject = get_string('watchlistmessageartefact', 'activity')
                        . (isset($data->subject) ? ': ' . $data->subject : '');
                    if (!$ainfo = get_record_sql('SELECT u.*, a.title FROM ' . $prefix . 'usr u
                                                  JOIN ' . $prefix . 'artefact a  ON a.owner = u.id
                                                  WHERE a.id = ?', array($data->artefact))) {
                        if (!empty($cron)) { // probably deleted already
                            return;
                        }
                        throw new InvalidArgumentException("Couldn't find artefact with id " . $data->artefact);
                    }
                    $data->message = get_string('onartefact', 'activity') 
                        . ' ' . $ainfo->title . ' ' . get_string('ownedby', 'activity');
                    $sql = 'SELECT DISTINCT u.*, p.method, ?||wa.view as url
                                FROM ' . $prefix . 'usr_watchlist_artefact wa
                                LEFT JOIN ' . $prefix . 'artefact_parent_cache pc
                                    ON (pc.parent = wa.artefact OR pc.artefact = wa.artefact)
                                JOIN ' . $prefix . 'usr u 
                                    ON wa.usr = u.id
                                LEFT JOIN ' . $prefix . 'usr_activity_preference p
                                    ON p.usr = u.id
                                WHERE (p.activity = ? OR p.activity IS NULL)
                                AND (pc.parent = ? OR wa.artefact = ?)
                            ';
                    $users = get_records_sql_array($sql, 
                                                   array(get_config('wwwroot') . 'view/view.php?view=' 
                                                         . $data->artefact . '&view=', 'watchlist', 
                                                         $data->artefact, $data->artefact));
                    if (empty($users)) {
                        $users = array();
                    }
                    // ick
                    foreach ($users as $user) {
                        $user->message = $data->message . ' ' . display_name($ainfo, $user);
                    }
                }
                else if (!empty($data->community)) {
                    if (empty($data->subject)) {
                        throw new InvalidArgumentException("subject must be provided for watchlist community");
                    }
                    if (!$communityname = get_field('community', 'name', 'id', $data->community)) {
                        throw new InvalidArgumentException("Couldn't find community with id " . $data->community);
                    }
                    $oldsubject = $data->subject;
                    $data->subject = get_string('watchlistmessagecommunity', 'activity');
                    $data->message = $oldsubject . ' ' . get_string('oncommunity', 'activity') . ' ' . $communityname;
                    $sql = 'SELECT DISTINCT u.*, p.method, CAST(? AS TEXT) AS url
                                FROM ' . $prefix . 'usr_watchlist_community c
                                JOIN ' . $prefix . 'usr u
                                    ON c.usr = u.id
                                LEFT JOIN ' . $prefix . 'usr_activity_preference p
                                    ON p.usr = u.id
                                WHERE (p.activity = ? OR p.activity IS NULL)
                                AND c.community = ?
                            ';
                    $users = get_records_sql_array($sql, 
                                                   array(get_config('wwwroot') . 'contacts/communities/view.php?id='
                                                         . $data->community, 'watchlist', $data->community));
                }
                else {
                    throw new InvalidArgumentException("Invalid watchlist type");
                }
                break;
            case 'newview':
                if (!is_numeric($data->owner) || !is_numeric($data->view)) {
                    throw new InvalidArgumentException("New view activity type requires view and owner to be set");
                }
                if (!$viewinfo = get_record_sql('SELECT u.*, v.title FROM ' . $prefix . 'usr u
                                                 JOIN ' . $prefix . 'view v ON v.owner = u.id
                                                 WHERE v.id = ?', array($data->view))) {
                    throw new InvalidArgumentException("Couldn't find view with id " . $data->view);
                }
                $data->message = get_string('newviewmessage', 'activity')
                    . ' ' . $viewinfo->title . ' ' . get_string('ownedby', 'activity');
                $data->subject = get_string('newviewsubject', 'activity');
                // add users on friendslist, userlist or grouplist...
                $users = activity_get_viewaccess_users($data->view, $data->owner);
                if (empty($users)) {
                    $users = array();
                }
                // ick
                foreach ($users as $user) {
                    $user->message = $data->message . ' ' . display_name($viewinfo, $user);
                }
                break;
            case 'viewaccess':
                if (!is_numeric($data->owner) || !is_numeric($data->view)) {
                    throw new InvalidArgumentException("view access activity type requires view and owner to be set");
                }
                if (!isset($data->oldusers)) {
                    throw new InvalidArgumentException("view access activity type requires oldusers to be set (even if empty)");
                }
                if (!$viewinfo = get_record_sql('SELECT u.*, v.title FROM ' . $prefix . 'usr u
                                                 JOIN ' . $prefix . 'view v ON v.owner = u.id
                                                 WHERE v.id = ?', array($data->view))) {
                    throw new InvalidArgumentException("Couldn't find view with id " . $data->view);
                }
                $data->message = get_string('newviewaccessmessage', 'activity')
                    . ' ' . $viewinfo->title . ' ' . get_string('ownedby', 'activity');
                $data->subject = get_string('newviewaccesssubject', 'activity');
                $users = array_diff_key(activity_get_viewaccess_users($data->view, $data->owner), $data->oldusers);
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
            call_static_method(generate_class_name('notification', $method), 'notify_user', $user, $userdata);
            $user->markasread = true; // if we're doing something else, don't generate unread internal ones.
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
 * @return array of users
 */
function activity_get_users($activitytype, $userids=null, $userobjs=null, $adminonly=false) {
    $values = array($activitytype);
    $sql = 'SELECT u.*, p.method
                FROM ' . get_config('dbprefix') .'usr u
                LEFT JOIN ' . get_config('dbprefix') . 'usr_activity_preference p
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

function activity_get_viewaccess_users($view, $owner) {

    $prefix = get_config('dbprefix');

    $sql = 'SELECT userid, u.*, p.method
                FROM (
                SELECT (CASE WHEN usr1 = ? THEN usr2 ELSE usr1 END) AS userid 
                    FROM ' . $prefix . 'usr_friend 
                        WHERE (usr1 = ? OR usr2 = ?)
                UNION SELECT member AS userid 
                    FROM ' . $prefix . 'usr_group_member m
                    JOIN ' . $prefix . 'view_access_group g ON m.grp = g.grp 
                        WHERE g.view = ?
                UNION SELECT usr AS userid 
                    FROM ' . $prefix . 'view_access_usr u 
                        WHERE u.view = ?
                ) AS userlist
                JOIN ' . $prefix . 'usr u ON u.id = userlist.userid
                LEFT JOIN ' . $prefix . 'usr_activity_preference p ON p.usr = u.id';
    return get_records_sql_assoc($sql, array($owner, $owner, $owner, $view, $view)) || array();
}

?>
