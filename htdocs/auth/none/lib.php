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
 * @subpackage auth-none
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();
require_once(get_config('docroot') . 'auth/lib.php');

/**
 * This authentication method allows ANY user access to Mahara. It's useful for 
 * testing ONLY!
 */
class AuthNone extends Auth {

    public function __construct($id = null) {
        $this->has_instance_config = false;
        $this->type       = 'none';
        if (!empty($id)) {
            return $this->init($id);
        }
        return true;
    }

    public function init($id) {
        $this->ready = parent::init($id);
        return true;
    }

    /**
     * Attempt to authenticate user
     *
     * @param object $user     As returned from the usr table
     * @param string $password The password being used for authentication
     * @return bool            True/False based on whether the user
     *                         authenticated successfully
     * @throws AuthUnknownUserException If the user does not exist
     */
    public function authenticate_user_account($user, $password) {
        $this->must_be_ready();
        return true;
        //return $this->validate_password($password, $user->password, $user->salt);
    }

    public function can_auto_create_users() {
        return true;
    }

    public function get_user_info($username) {
        $user = new stdClass;
        $user->firstname = ucfirst(strtolower($username));
        $user->lastname  = 'McAuthentication';
        $user->email     = 'test@example.org';
        return $user;
    }

    /**
     * Any old password is valid
     *
     * @param string $password The password to check
     * @return bool            True, always
     */
    public function is_password_valid($password) {
        return true;
    }
}

/**
 * Plugin configuration class
 */
class PluginAuthNone extends PluginAuth {

    public static function has_config() {
        return false;
    }

    public static function get_config_options() {
        return array();
    }

    public static function has_instance_config() {
        return false;
    }

    public static function get_instance_config_options() {
        return array();
    }
}
