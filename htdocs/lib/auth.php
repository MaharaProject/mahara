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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Base authentication class
 */
abstract class Auth {

    /**
     * Given a username, password and institution, attempts to log the user
     * in.
     *
     * This method should return one of three values:
     *
     * <ul>
     *   <li>AUTH_PASSED - the user has provided correct credentials</li>
     *   <li>AUTH_FAILED - the user has provided incorrect credentials</li>
     *   <li>AUTH_UNKNOWN - the authentication system does not know about
     *   this user.</li>
     * </ul>
     */
    public static abstract function authenticate_user_account($username, $password, $institute);

    /**
     * Given a username, returns a hash of information about a user.
     *
     */
    public static abstract function get_user_info ($username);

    public static function get_config_options () {
    }

}

?>
