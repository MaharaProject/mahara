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
    if (empty($userid)) {
        global $USER;
        $userid = $USER->get('id');
    }
    
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
    
    delete_records('community_member', 'community', $community, 'member', $userid);

}



?>