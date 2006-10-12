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

// Constants for authentication
define('AUTH_PASSED', 1);
define('AUTH_FAILED', 2);

/** Exception - unknown user */
class AuthUnknownUserException extends Exception {}

/**
 * Base authentication class
 *
 * Institutions are tied to a particular plugin
 */
abstract class Auth {

    /**
     * Given a username, password and institution, attempts to log the use in.
     * 
     * This method should return one of two values:
     *
     * <ul>
     *   <li>AUTH_PASSED - the user has provided correct credentials</li>
     *   <li>AUTH_FAILED - the user has provided incorrect credentials</li>
     * </ul>
     *
     * @param string $username  The username to attempt to authenticate
     * @param string $password  The password to use for the attempt
     * @param string $institute The institution the user belongs to
     */
    public static abstract function authenticate_user_account($username, $password, $institute);

    /**
     * Given a username, returns a hash of information about a user.
     *
     * Should throw an exception if the authentication method doesn't know
     * about the user, since this method should only be called after a
     * successful authentication method (so we know the user exists)
     *
     * @param string $username The username to look up information for
     * @return array           The information for the user
     * @throws AuthUnknownUserException
     */
    public static abstract function get_user_info ($username);

    /**
     * Returns a hash of information that will be rendered into a form
     * when configuring authentication.
     *
     * @return array
     */
    public static function get_config_options () {
    }

}

/**
 * Performs an authentication attempt, by cycling through all of the available
 * authentication methods allowed for the user.
 *
 */
function authenticate_user($username, $password, $institute) {
    //
    // Implementation:
    //
    // Well, institutes are tied to a particular authentication method - ONE particular authentication method
    // And users are tied to an institution
    // So they have ONE go at authentication, not like the rubbish mentioned in the technical spec.
    // So, the algorithm should be roughly:
    //
    // based on the institute, get the auth method
    // include the auth method implementation
    // try {
    //     authenticate the user using username, password
    // }
    // catch (whothehellisthisexception) {
    //     return appropriate message
    // }
    // catch (wrongpasswordexception) {
    //     return appropriate message
    // }
    //
    // all happy, return OK
    //
    //
    // So, how is this function called exactly?
    //
    // Well, the login pages are generally completely transient, which means that once this is
    // called successfully, the get and post information needs to be sent back to where we came
    // from, which is the page name itself.
    //
    // Basically, in init.php or similar:
    //
    // do_authentication();
    //
    // do_authentication:
    //     if user logged in (check session data)
    //         if session timed out or otherwise invalid
    //             display login form
    //         else
    //             all good, continue
    //     elseif has correct guest key
    //         all good
    //     else
    //         display login form
    //
    // if user logged in (check session data) == this function
}

/**
 * So how will this work? written above.
 * try {
 *    authenticate();
 * }
 * catch (AuthenticationException $e) {
 *     // can't authenticate again, something bad happened
 *     // fall through to the default exception handler where this is a default, or otherwise exit the script
 */
function auth_setup () {
    // auth stuff is run before init.php finishes, and index.php does the check
    // for install. So this function might need to detect not installed and skip
    // logging in
    if (!session_id()) {
        @session_start();
        if (!session_id()) {
            throw new AuthException('Could not start a session. Perhaps '
                . 'something has been output before the page begins?');
        }
    }

    $s =& $_SESSION;
    $username = clean_requestdata('login_username', PARAM_ALPHA);
    $password = clean_requestdata('login_password', PARAM_ALPHA);

    if (!get_config('version')) {
        // Not installed, so let the user through
        log_dbg('system not installed, letting user through');
        return;
    }
    if (isset($s['logged_in']) && $s['username'] != '') {
        log_dbg('user logged in, fine just fine (user is ' . $s['username']);
        return;
    }

    if ($username != '' && $password != '') {
        log_dbg('auth attempt with username "' . $username . '" and password "' . $password . '"');
        if (!auth_user($username, $password, $institution)) {
            auth_draw_login_form();
            exit;
        }
        // Login went fine
        return;
    }

    if (false /* guest key is available */) {
        return;
    }

    if (false /* site config claims public access ok */) {
        return;
    }

    else {
        log_dbg('dunno who this is, better get them to tell us');
        auth_draw_login_form();
        exit;
    }
}

function auth_user ($username, $password, $institution) {
    log_dbg('login attempt from user ' . $username);
    return true;
}

function auth_draw_login_form() {
    $smarty = smarty();
    $smarty->display('login.tpl');
}

?>
