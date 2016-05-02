<?php
/**
 *
 * @package    mahara
 * @subpackage auth-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
        $result = $this->validate_password($password, $user->password, $user->salt);
        // If result == 1, password is correct
        // If result > 1, password is correct but using old settings, should be changed
        if ($result > 1 ) {
            if ($user->passwordchange != 1) {
                $userobj = new User();
                $userobj->find_by_id($user->id);
                $this->change_password($userobj, $password);
                $user->password = $userobj->password;
                $user->salt = $userobj->salt;
            }
        }
        return $result > 0;
    }

    /**
     * Internal authentication never auto-creates users - users instead
     * register through register.php
     */
    public function can_auto_create_users() {
        return false;
    }

    public static function can_use_registration_captcha() {
        return true;
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
    public function change_password(User $user, $password, $resetpasswordchange = true, $quickhash = false) {
        $this->must_be_ready();
        // Create a salted password and set it for the user
        $user->salt = substr(md5(rand(1000000, 9999999)), 2, 8);
        if ($quickhash) {
            // $6$ is SHA512, used as a quick hash instead of bcrypt for now.
            $user->password = $this->encrypt_password($password, $user->salt, '$6$', get_config('passwordsaltmain'));
        }
        else {
            // $2a$ is bcrypt hash. See http://php.net/manual/en/function.crypt.php
            // It requires a cost, a 2 digit number in the range 04-31
            $user->password = $this->encrypt_password($password, $user->salt, '$2a$' . get_config('bcrypt_cost') . '$', get_config('passwordsaltmain'));
        }
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
    * @param string $alg      The algorithm to use, defaults to $6$ which is SHA512
    * @param string $sitesalt A salt to combine with the user's salt to add an extra layer or salting
    * @todo salt mandatory
    */
    public function encrypt_password($password, $salt='', $alg='$6$', $sitesalt='') {
        if ($salt == '') {
            $salt = substr(md5(rand(1000000, 9999999)), 2, 8);
        }
        if ($alg == '$6$') { // $6$ is the identifier for the SHA512 algorithm
            // Return a hash which is sha512(originalHash, salt), where original is sha1(salt + password)
            $password = sha1($salt . $password);
            // Generate a salt based on a supplied salt and the passwordsaltmain
            $fullsalt = substr(md5($sitesalt . $salt), 0, 16); // SHA512 expects 16 chars of salt
        }
        else { // This is most likely bcrypt $2a$, but any other algorithm can take up to 22 chars of salt
            // Generate a salt based on a supplied salt and the passwordsaltmain
            $fullsalt = substr(md5($sitesalt . $salt), 0, 22); // bcrypt expects 22 chars of salt
        }
        $hash = crypt($password, $alg . $fullsalt);
        // Strip out the computed salt
        // We strip out the salt hide the computed salt (in case the sitesalt was used which isn't in the database)
        $hash = substr($hash, 0, strlen($alg)) . substr($hash, strlen($alg)+strlen($fullsalt));
        return $hash;
    }

    /**
     * Given a password that the user has sent, the password we have for them
     * and the salt we have, see if the password they sent is correct.
     *
     * @param string $theysent The password the user sent
     * @param string $wehave   The salted and hashed password we have in the database for them
     * @param string $salt     The salt we have.
     * @returns int     0 means not validated, 1 means validated, 2 means validated but needs updating
     */
    protected function validate_password($theysent, $wehave, $salt) {
        $this->must_be_ready();

        if ($salt == '*') {
            // This is a special salt that means this user simply CAN'T log in.
            // It is used on the root user (id=0)
            return false;
        }

        if (empty($wehave)) {
            // This means the user has not been set up completely yet
            // Common cause is that still in registration phase
            return false;
        }

        $sitesalt = get_config('passwordsaltmain');
        $bcrypt = substr($wehave, 0, 4) == '$2a$';
        if ($bcrypt) {
            $alg = substr($wehave, 0, 7);
            $hash = $this->encrypt_password($theysent, $salt, $alg, $sitesalt);
        }
        else {
            $alg = substr($wehave, 0, 3);
            $hash = $this->encrypt_password($theysent, $salt, $alg, $sitesalt);
        }
        if ($hash == $wehave) {
            if (!$bcrypt || substr($alg, 4, 2) != get_config('bcrypt_cost')) {
                // Either not using bcrypt yet, or the cost parameter has changed, update the hash
                return 2;
            }
            // Using bcrypt with the correct cost parameter, leave as is.
            return 1;
        }
        // See http://docs.moodle.org/20/en/Password_salting#Changing_the_salt
        if (!empty($sitesalt)) {
            // There is a sitesalt set, try without it, and update if passes
            $hash = $this->encrypt_password($theysent, $salt, $alg, '');
            if ($hash == $wehave) {
                return 2;
            }
        }
        for ($i = 1; $i <= 20; ++ $i) {
            // Try 20 alternate sitesalts
            $alt = get_config('passwordsaltalt' . $i);
            if (!empty($alt)) {
                $hash = $this->encrypt_password($theysent, $salt, $alg, $alt);
                if ($hash == $wehave) {
                    return 2;
                }
            }
        }
        // Nothing works, fail
        return 0;
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

    public static function get_instance_config_options($institution, $instance = 0) {
        return array();
    }
}
