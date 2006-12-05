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
function activity_occured($activitytype, $data) {
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
 * <b>admin types (contactus, objectionable, virusrepeat, virusrelease)</b> don't have any extra requirements
 * <b>maharamessage</b> must contain $users, an array of userids.
 * <b>usermessage</b> must contain $userto, id of recipient user.
 * <b>feedback</b> must contain either $view (id of view) or $artefact (id of artefact)
 * <b>watchlist</b> must contain either $view (id of view) or $artefact (id of artefact) or $community (id of community)
 * <b>newview</b> must contain $owner userid of view owner AND $view (id of new view)
 */
function handle_activity($activitytype, $data) {

    $data = (object)$data;
    if (empty($data->message)) {
        throw new InvalidArgumentException("message was empty for $activitytype!");
    }

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
    }
    else {
        switch ($activitytype->name) {
            // easy ones first :)
            case 'maharamessage':
                $users = activity_get_users($activitytype->name, $data->users);
                break;
            case 'usermessage':
                $users = activity_get_users($activitytype->name, array($data->userto));
                break;
            case 'feedback':
                if ($data->view) {
                    $userid = get_field('view', 'owner', 'id', $data->view);
                } 
                else if ($data->artefact) {
                    $userid = get_field('artefact', 'owner', 'id', $data->artefact);
                }
                $users = activity_get_users($activitytype->name, array($userid));
                break;
            // and now the harder ones
            case 'watchlist':
                if (!empty($data->view)) {
                    $sql = 'SELECT u.*, p.method, ? AS url
                                FROM ' . $prefix . 'usr_watchlist_view wv
                                JOIN ' . $prefix . 'usr u
                                    ON wa.usr = u.id
                                JOIN ' . $prefix . 'usr_activity_preference p
                                    ON p.usr = u.id
                                WHERE p.activity = ?
                                AND wv.view = ?
                           ';
                    $users = get_records_sql_array($sql, 
                                                   array(get_config('wwwroot') . 'view/view.php?id=' 
                                                         . $data->view, 'watchlist', $data->view));
                } 
                else if (!empty($data->artefact)) {
                    $sql = 'SELECT DISTINCT u.*, p.method, ?||wa.view as url
                                FROM ' . $prefix . 'usr_watchlist_artefact wa
                                LEFT JOIN ' . $prefix . 'artefact_parent_cache pc
                                    ON (pc.parent = wa.artefact OR pc.artefact = wa.artefact)
                                JOIN ' . $prefix . 'usr u 
                                    ON wa.usr = u.id
                                JOIN ' . $prefix . 'usr_activity_preference p
                                    ON p.usr = u.id
                                WHERE p.activity = ?
                                AND (pc.parent = ? OR wa.artefact = ?)
                            ';
                    $users = get_records_sql_array($sql, 
                                                   array(get_config('wwwroot') . 'view/artefact.php?id=' 
                                                         . $data->artefact . '&view=', 'watchlist', 
                                                         $data->artefact, $data->artefact));
                }
                else if (!empty($data->community)) {
                    $sql = 'SELECT DISTINCT u.*, p.method, ? AS url, 
                                FROM ' . $prefix . 'usr_watchlist_community c
                                JOIN ' . $prefix . 'usr u
                                    ON c.usr = u.id
                                JOIN ' . $prefix . 'usr_activity_preference p
                                    ON p.usr = u.id
                                WHERE p.activity = ?
                                AND c.community = ?
                            ';
                    $users = get_records_sql_array($sql, 
                                                   array(getconfig('wwwroot') . 'community/view.php?id='
                                                         . $data->community, 'watchlist', $data->community));
                }
                else {
                    throw new InvalidArgumentException("Invalid watchlist type");
                }
                break;
            case 'newview':
                // add users on friendslist, userlist or grouplist...
                $sql = 'SELECT userid, u.*, p.method
                        FROM (
                            SELECT (CASE WHEN usr1 = ? THEN usr2 ELSE usr1 END) AS userid 
                                FROM ' . $prefix . 'usr_friend 
                                WHERE (usr1 = ? OR usr2 = ?)
                            UNION SELECT member AS userid 
                                FROM ' . $prefix . 'usr_group_member m
                                JOIN ' . $prefix . 'view_access_group g ON m.group = g.group 
                            WHERE g.view = ?
                            UNION SELECT usr AS userid 
                                  FROM ' . $prefix . 'view_access_usr u 
                                  WHERE u.view = ?
                        ) AS userlist
                            JOIN ' . $prefix . 'usr u ON u.id = userlist.userid
                            JOIN ' . $prefix . 'usr_activity_preference p ON p.usr = u.id';
                $users = get_records_sql_array($sql, array($data->owner, $data->owner, $data->owner,  
                                                     $data->view, $data->view));
                break;
        }
    }
    if (empty($users)) {
        return;
    }
    safe_require('notification', 'internal', 'lib.php', 'require_once');
    $data->type = $activitytype->name;
    foreach ($users as $user) {
        if (!empty($user->url) && empty($data->url)) {
            $data->url = $user->url;
        }
        if ($user->method != 'internal') {
            safe_require('notification', $method, 'lib.php', 'require_once');
            call_static_method(generate_class_name('notification', $method), 'notify_user', $user, $data);
            $user->markasread = true; // if we're doing something else, don't generate unread internal ones.
        }
        // always do internal
        call_static_method('PluginNotificationInternal', 'notify_user', $user, $data);
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
                JOIN ' . get_config('dbprefix') . 'usr_activity_preference p
                    ON p.usr = u.id
                WHERE p.activity = ? ';
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


?>
