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
 * @subpackage auth-internal
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * The internal authentication method, which authenticates users against the
 * Mahara database.
 */
class AuthInternal extends Auth {

    /**
     * Attempt to authenticate user
     */
    public static function authenticate_user_account($username, $password, $institution) {
        if (!$user = get_record_sql('SELECT username, password, salt
            FROM ' . get_config('dbprefix') . 'usr
            WHERE LOWER(username) = ?', strtolower($username))) {
            throw new AuthUnknownUserException("\"$username\" is not known to AuthInternal");
        }

        return self::validate_password($password, $user->password, $user->salt);
    }

    /**
     * Given a user that we know about, return an array of information about them
     *
     * NOTE: Does not need to be implemented for the internal authentication
     * method, as by default information is sourced from the database.
     */
    public static function get_user_info($username) {
    }

    /**
     * Returns a form that allows an administrator to configure this
     * authentication method.
     *
     * The internal method has no configuration options. This is just
     * here until I can document it properly.
     */
    public static function get_configuration_form() {
        //return Auth::build_form('internal', array(
        //    'foo' => array(
        //        'type' => 'text',
        //        'title' => 'wtf',
        //        'description' => 'Testing',
        //        'help' => 'help',
        //        'defaultvalue' => get_config_plugin('auth', 'internal', 'foo')
        //    )
        //));
    }

    public static function validate_configuration_form(Form $form, $values) {
        //if (!$form->get_error('foo') && $values['foo'] != 'bar') {
        //    $form->set_error('foo', 'WTF man!');
        //}
    }

    /**
     * For internal authentication, passwords can contain a range of letters,
     * numbers and symbols. There is a minimum limit of six characters allowed
     * for the password, and no upper limit
     *
     * @param string $password The password to check
     * @return bool            Whether the password is valid
     */
    public static function is_password_valid($password) {
        if (!preg_match('/^[a-zA-Z0-9 ~!#\$%\^&\*\(\)_\-=\+\,\.<>\/\?;:"\[\]\{\}\\\|`\']{6,}$/', $password)) {
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
     * @param string $username The user to change the password for
     * @param string $password The password to set for the user
     * @return string The new password, or empty if the password could not be set
     */
    public static function change_password($username, $password) {
        // Create a salted password and set it for the user
        $user = new StdClass;
        $user->salt = substr(md5(rand(1000000, 9999999)), 2, 8);
        $user->password = self::encrypt_password($password, $user->salt);
        $where = new StdClass;
        $where->username = $username;
        update_record('usr', $user, $where);
        return $user->password;
    }

    /**
     * For internal authentication, usernames can only contain alphanumeric
     * characters, and the symbols underscore, full stop and the @ symbol.
     *
     * The username must also be between three and thirty characters in length.
     *
     * This method is NOT part of the authentication API. Other authentication
     * methods never have to do anything regarding usernames being validated on
     * the Mahara side, so they do not need this method.
     *
     * @param string $username The username to check
     * @return bool            Whether the username is valid
     */
    public static function is_username_valid($username) {
        return preg_match('/^[a-zA-Z0-9\._@]{3,30}$/', $username);
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
    public static function encrypt_password($password, $salt='') {
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
     * @param string $salt     The salt we have. If null, plaintext password
     *                         checking is assumed. A null salt is not used
     *                         by the application - instead, this gives
     *                         administrators a way to set passwords inside the
     *                         database manually without having to make up and
     *                         encrypt a password using a salt.
     */
    private static function validate_password($theysent, $wehave, $salt) {
        if ($salt == null) {
            // This allows "plaintext" passwords, which are eaiser for an admin to
            // create by hacking in the database directly. The application does not
            // create passwords in this form.
            return $theysent == $wehave;
        }

        // The main type - a salted sha1
        $sha1sent = self::encrypt_password($theysent, $salt);
        return $sha1sent == $wehave;
    }

}

/**
 * Plugin configuration class. Nothing special required for this plugin...
 */
class PluginAuthInternal extends Plugin {

    static function has_config() {
        return true;
    }
}

?>
