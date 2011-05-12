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
 * @subpackage auth-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();
require_once(get_config('docroot') . 'auth/lib.php');

/**
 * The internal authentication method, which authenticates users against the
 * Mahara database.
 */
class AuthInternal extends Auth {

    public function __construct($id = null) {
        $this->has_instance_config = false;
        $this->type       = 'internal';
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
        return $this->validate_password($password, $user->password, $user->salt);
    }

    /**
     * Internal authentication never auto-creates users - users instead 
     * register through register.php
     */
    public function can_auto_create_users() {
        return false;
    }

    /**
     * For internal authentication, passwords can contain a range of letters,
     * numbers and symbols. There is a minimum limit of six characters allowed
     * for the password, and no upper limit
     *
     * @param string $password The password to check
     * @return bool            Whether the password is valid
     */
    public function is_password_valid($password) {
        if (!preg_match('/^[a-zA-Z0-9 ~!@#\$%\^&\*\(\)_\-=\+\,\.<>\/\?;:"\[\]\{\}\\\|`\']{6,}$/', $password)) {
            return false;
        }
        // The password must have at least one digit and two letters in it
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        $password = preg_replace('/[a-zA-Z]/', "\0", $password);
        if (substr_count($password, "\0") < 2) {
            return false;
        }
        return true;
    }

    /**
     * Changes the user's password.
     *
     * This method is not strictly part of the authentication API, but if
     * defined allows the method to change a user's password.
     *
     * @param object  $user     The user to change the password for
     * @param string  $password The password to set for the user
     * @param boolean $resetpasswordchange Whether to reset the passwordchange variable or not
     * @return string The new password, or empty if the password could not be set
     */
    public function change_password(User $user, $password, $resetpasswordchange = true) {
        $this->must_be_ready();
        // Create a salted password and set it for the user
        $user->salt = substr(md5(rand(1000000, 9999999)), 2, 8);
        $user->password = $this->encrypt_password($password, $user->salt);
        if ($resetpasswordchange) {
            $user->passwordchange = 0;
        }
        $user->commit();
        return $user->password;
    }

    /**
     * Internal authentication allows most standard us-keyboard-typable characters
     * for username, as long as the username is between three and thirty 
     * characters in length.
     *
     * This method is NOT part of the authentication API. Other authentication
     * methods never have to do anything regarding usernames being validated on
     * the Mahara side, so they do not need this method.
     *
     * @param string $username The username to check
     * @return bool            Whether the username is valid
     */
    public function is_username_valid($username) {
        return preg_match('/^[a-zA-Z0-9!@#$%^&*()\-_=+\[{\]}\\|;:\'",<\.>\/?`]{3,30}$/', $username);
    }
    /**


     * Internal authentication allows most standard us-keyboard-typable characters
     * for username, as long as the username is between three and 236 
     * characters in length.
     *
     * This method is NOT part of the authentication API. Other authentication
     * methods never have to do anything regarding usernames being validated on
     * the Mahara side, so they do not need this method.
     *
     * This method is meant to only be called for validation by an admin of the user
     * and is able to set a password longer than thirty characters in length
     *
     * @param string $username The username to check
     * @return bool            Whether the username is valid
     */
    public function is_username_valid_admin($username) {
        return preg_match('/^[a-zA-Z0-9!@#$%^&*()\-_=+\[{\]}\\|;:\'",<\.>\/?`]{3,236}$/', $username);
    }

    /**
     * Changes the user's username.
     *
     * This method is not strictly part of the authentication API, but if
     * defined allows the method to change a user's username.
     *
     * @param object  $user     The user to change the password for
     * @param string  $username The username to set for the user
     * @return string The new username, or the original username if it could not be set
     */
    public function change_username(User $user, $username) {
        global $USER;

        $this->must_be_ready();

        // proposed username must pass validation
        $valid = false;
        if ($USER->is_admin_for_user($user)) {
            $valid = $this->is_username_valid_admin($username);
        } else {
            $valid = $this->is_username_valid($username);
        }

        if ($valid) {
            $user->username = $username;
            $user->commit();
        }

        // return the new username, or the original one if it failed validation
        return $user->username;
    }

    /*
     The following two functions are inspired by Andrew McMillan's salted md5
     functions in AWL, adapted with his kind permission. Changed to use sha1
     and match the coding standards for Mahara.
    */

   /**
    * Given a password and an optional salt, encrypt the given password.
    *
    * Passwords are stored in SHA1 form.
    *
    * @param string $password The password to encrypt
    * @param string $salt     The salt to use to encrypt the password
    * @todo salt mandatory
    */
    public function encrypt_password($password, $salt='') {
        if ($salt == '') {
            $salt = substr(md5(rand(1000000, 9999999)), 2, 8);
        }
        return sha1($salt . $password);
    }

    /**
     * Given a password that the user has sent, the password we have for them
     * and the salt we have, see if the password they sent is correct.
     *
     * @param string $theysent The password the user sent
     * @param string $wehave   The password we have in the database for them
     * @param string $salt     The salt we have.
     */
    private function validate_password($theysent, $wehave, $salt) {
        $this->must_be_ready();

        if ($salt == '*') {
            // This is a special salt that means this user simply CAN'T log in.
            // It is used on the root user (id=0)
            return false;
        }

        // The main type - a salted sha1
        $sha1sent = $this->encrypt_password($theysent, $salt);
        return $sha1sent == $wehave;
    }

}

/**
 * Plugin configuration class
 */
class PluginAuthInternal extends PluginAuth {

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
