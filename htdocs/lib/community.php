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
 * is a user allowed to leave a community? 
 * checks if they're the owner and the membership type
 *
 * @param object $community (corresponds to db record). if an id is given, record will be fetched.
 * @param int $userid (optional, will default to logged in user)
 */
function community_user_can_leave($community, $userid=null) {

    $userid = optional_userid($userid);
    
    if (is_numeric($community)) {
        if (!$community = get_record('community', 'id', $community)) {
            return false;
        }
    }
    
    if ($community->owner == $userid) {
        return false;
    }
    
    if ($community->jointype == 'controlled') {
        return false;
    }
    return true;
}

/**
 * removes a user from a community
 *
 * @param int $community id of community
 * @param int $user id of user to remove
 */
function community_remove_user($community, $userid) {    
    db_begin();
    delete_records('community_member', 'community', $community, 'member', $userid);
    delete_records('usr_watchlist_community', 'community', $community, 'usr', $userid);
    db_commit();
}

/**
 * all communities the user is a member of
 * 
 * @param int userid (optional, defaults to $USER id) 
 * @return array of community db rows
 */
function get_member_communities($userid=0, $offset=0, $limit=0) {

    $userid = optional_userid($userid);
    $prefix = get_config('dbprefix');

    return get_records_sql_array('SELECT c.*, cm.ctime, cm.tutor
              FROM ' . $prefix . 'community c 
              JOIN ' . $prefix . 'community_member cm ON cm.community = c.id
              WHERE c.owner != ? AND cm.member = ?', array($userid, $userid), $offset, $limit);
}


/**
 * all communities the user owns
 * 
 * @param int userid (optional, defaults to $USER id) 
 * @param string $jointype (optional), will filter by jointype.
 * @return array of community db rows
 */
function get_owned_communities($userid=0, $jointype=null) {

    $userid = optional_userid($userid);
    $prefix = get_config('dbprefix');

    $sql = 'SELECT c.* FROM ' . $prefix . 'community c 
             WHERE c.owner = ?';
    $values = array($userid);

    if (!empty($jointype)) {
        $sql .= ' AND jointype = ?';
        $values[] = $jointype;
    }
       
    return get_records_sql_array($sql, $values);
}

/**
 * all communities the user has pending invites to
 * 
 * @param int userid (optional, defaults to $USER id)
 * @return array of community db rows
 */
function get_invited_communities($userid=0) {

    $userid = optional_userid($userid);
    $prefix = get_config('dbprefix');

    return get_records_sql_array('SELECT c.*, cmi.ctime, cmi.reason
             FROM ' . $prefix . 'community c 
             JOIN ' . $prefix . 'community_member_invite cmi ON cmi.community = c.id
             WHERE cmi.member = ?)', array($userid));
}

/**
 * all communities the user has pending requests for 
 * 
 * @param int $userid (optional, defaults to $USER id)
 * @return array of community db rows
 */

function get_requested_communities($userid=0) {

    $userid = optional_userid($userid);
    $prefix = get_config('dbprefix');

    return get_records_sql_array('SELECT c.*, cmr.ctime, cmr.reason 
              FROM ' . $prefix . 'community c 
              JOIN ' . $prefix . 'community_member_request cmr ON cmr.community = c.id
              WHERE cmr.member = ?', array($userid));
}

/**
 * all communities this user is associated with at all
 * either member, invited or requested.
 * 
 * @param int $userid (optional, defaults to $USER id)
 * @return array of community db rows (with type=member|invite|request)
 */

function get_associated_communities($userid=0) {

    $userid = optional_userid($userid);
    $prefix = get_config('dbprefix');
    
    $sql = "SELECT c.*, a.type FROM " . $prefix . "community c JOIN (
    SELECT cm.community, 'invite' AS type
        FROM " . $prefix . "community_member_invite cm WHERE cm.member = ?
    UNION 
    SELECT cm.community, 'request' AS type
        FROM " . $prefix . "community_member_request cm WHERE cm.member = ?
    UNION 	
    SELECT cm.community, 'member' AS type
        FROM " . $prefix . "community_member cm WHERE cm.member = ?
    ) AS a ON a.community = c.id";
    
    return get_records_sql_assoc($sql, array($userid, $userid, $userid));
}


/**
 * gets communities the user is a tutor in
 * 
 * @param int $userid (optional, defaults to $USER id)
 * @param string $jointype (optional, will filter by jointype
 */
function get_tutor_communities($userid=0, $jointype=null) {

    $userid = optional_userid($userid);
    $prefix = get_config('dbprefix');

    $sql = 'SELECT c.*, cm.ctime
              FROM ' . $prefix . 'community c 
              JOIN ' . $prefix . 'community_member cm ON cm.community = c.id
              WHERE c.owner != ? AND cm.member = ? AND cm.tutor = ? ';
    $values = array($userid, $userid, 1);
    
    if (!empty($jointype)) {
        $sql .= ' AND c.jointype = ? ';
        $values[] = $jointype;
    }
    return get_records_sql_array($sql, $values);
}


// constants for community membership type
define('COMMUNITY_MEMBERSHIP_ADMIN', 1);
define('COMMUNITY_MEMBERSHIP_STAFF', 2);
define('COMMUNITY_MEMBERSHIP_OWNER', 3);
define('COMMUNITY_MEMBERSHIP_TUTOR', 4);
define('COMMUNITY_MEMBERSHIP_MEMBER', 5);


/**
 * Can a user access a given community?
 * 
 * @param mixed $community id of community or db record (object)
 * @param mixed $user optional (object or id), defaults to logged in user
 *
 * @returns constant access level or FALSE
 */
function user_can_access_community($community, $user=null) {

    if (empty($userid)) {
        global $USER;
        $user = $USER;
    }
    else if (is_int($user)) {
        $user = get_user($user);
    }
    else if (is_object($user) && !$user instanceof User) {
        $user = get_user($user->get('id'));
    }

    if (!$user instanceof User) {
        throw new InvalidArgumentException("not useful user arg given to user_can_access_community: $user");
    }

    if (is_int($community)) {
        $community = get_record('community', 'id', $community);
    }

    if (!is_object($community)) {
        throw new InvalidArgumentException("not useful community arg given to user_can_access_community: $community");
    }

    if ($user->get('admin')) {
        return COMMUNITY_MEMBERSHIP_ADMIN;
    }
    if ($user->get('staff')) {
        return COMMUNITY_MEMBERSHIP_STAFF;
    }
    if ($community->owner == $user->get('id')) {
        return COMMUNITY_MEMBERSHIP_OWNER;
    }

    if (!$membership = get_record('community_member', 'community', $community->id, 'member', $user->get('id'))) {
        return false;
    }

    if ($membership->tutor) {
        return COMMUNITY_MEMBERSHIP_TUTOR;
    }
    
    return COMMUNITY_MEMBERSHIP_MEMBER;
}

?>