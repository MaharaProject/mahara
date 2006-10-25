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
 * @subpackage auth/internal
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class AuthInternal extends Auth {

    /**
     * Attempt to authenticate user
     */
    public static function authenticate_user_account($username, $password, $institution) {
        if (!$user = get_record_sql('SELECT username, password, salt
            FROM ' . get_config('dbprefix') . 'usr
            WHERE LOWER(username) = ?', strtolower($username))) {
            throw new AuthUnknownUserException("\"$username\" is not known to Auth_Internal");
        }

        return self::validate_password($password, $user->password, $user->salt);
    }

    /**
     * Given a user that we know about, return an array of information about them
     */
    public static function get_user_info($username) {
        $user = new StdClass;
        $user->username = $username;
        return $user;
    }

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
                
    /*
     The following two functions are inspired by Andrew McMillan's salted md5
     functions in AWL, adapted with his kind permission. Changed to use sha1
     and match the coding standards for Mahara.
    */
    
    private static function encrypt_password($password, $salt='') {
        if ($salt == '') {
            $salt = substr(md5(rand(1000000, 9999999)), 2, 8);
        }
        return sha1($salt . $password);
    }

    private static function validate_password($theysent, $wehave, $salt) {
        if ($salt == null) {
            // This allows "plaintext" passwords, which are eaiser for an admin to
            // create by hacking in the database directly. The application does not
            // create passwords in this form.
            return $theysent == $wehave;
        }

        // The main type - a salted sha1
        $sha1sent = Auth_Internal::encrypt_password($theysent, $salt);
        return $sha1sent == $wehave;
    }
}

/**
 * Plugin configuration class. Nothing special required for this plugin...
 */
class PluginAuthInternal extends Plugin {
}

?>
