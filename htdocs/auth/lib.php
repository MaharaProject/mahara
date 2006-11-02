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
 * @subpackage auth
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Unknown user exception
 */
class AuthUnknownUserException extends Exception {}

/**
 * Base authentication class. Provides a common interface with which
 * authentication can be carried out for system users.
 */
abstract class Auth {

    /**
     * Given a username, password and institution, attempts to log the use in.
     *
     * @todo Later, needs to deal with institution
     *
     * @param string $username  The username to attempt to authenticate
     * @param string $password  The password to use for the attempt
     * @param string $institute The institution the user belongs to
     * @return bool             Whether the authentication was successful
     * @throws AuthUnknownUserException  If the user is unknown to the
     *                                   authentication method
     */
    public static abstract function authenticate_user_account($username, $password, $institute);

    /**
     * Given a username, returns a hash of information about a user.
     *
     * @param string $username The username to look up information for
     * @return array           The information for the user
     * @throws AuthUnknownUserException If the user is unknown to the
     *                                  authentication method
     */
    public static abstract function get_user_info($username);

    /**
     * Returns a hash of information that will be rendered into a form when
     * configuring authentication.
     *
     * This is defined to be empty, so that authentication methods do not have
     * to specify a form if they do not need to.
     *
     * If an authentication method is to return any elements, the return result
     * <b>must</b> be wrapped in a call to {@link build_form}.
     *
     * For example:
     *
     * <pre>
     * $elements = array(
     *     // ... describe elements here ...
     * );
     * return Auth::build_form($elements);
     * </pre>
     *
     * @return array The form for configuring the authentication method
     */
    public static function get_configuration_form() {
    }

    /**
     * Given a submission from the configuration form, validates it
     *
     * This is defined to be empty, so that authentication methods do not have
     * to specify any validation rules if they do not need to.
     *
     * @param array $values The submitted values for the form
     * @param Form $form    The form being validated
     */
    public static function validate_configuration_form(Form $form, $values) {
    }

    /**
     * Given a username, returns whether it is in a valid format for this
     * authentication method.
     *
     * Note: This does <b>not</b> check that the username is an existing user
     * that this authentication method could log in given the correct password.
     * It only checks that the format that the username is in is allowed - i.e.
     * that it matches a specific regular expression for example.
     *
     * This is defined to be empty, so that authentication methods do not have
     * to specify a format if they do not need to.
     *
     * The default behaviour is to assume that the username is in a valid form,
     * so make sure to implement this method if this is not the case!
     *
     * @param string $username The username to check
     * @return bool            Whether the username is in valid form.
     */
    public static function is_username_valid($username) {
        return true;
    }

    /**
     * Given a password, returns whether it is in a valid format for this
     * authentication method.
     *
     * This is defined to be empty, so that authentication methods do not have
     * to specify a format if they do not need to.
     *
     * The default behaviour is to assume that the password is in a valid form,
     * so make sure to implement this method if this is not the case!
     *
     * @param string $password The password to check
     * @return bool            Whether the username is in valid form.
     */
    public static function is_password_valid($password) {
        return true;
    }

    /**
     * If a validation form is to be used, the result of 
     * {@link get_configuration_form} should be passed through this method
     * before being returned. This method builds the rest of the form.
     *
     * @param string $method  The name of the authentication method (for
     *                        example 'internal'). Lowercase please.
     * @param array $elements The elements in the form.
     * @return array          The form definition. <kbd>false</kbd> if there
     *                        is no form for the authentication method.
     */
    protected static final function build_form($method, $elements) {
        if (count($elements)) {
            $elements['submit'] = array(
                'type' => 'submit',
                'value' => 'Update'
            );
            $elements['method'] = array(
                'type' => 'hidden',
                'value' => $method 
            );
            return array(
                'name' => 'auth',
                'elements' => $elements
            );
        }
        return false;
    }

}


/**
 * Handles authentication by setting up a session for a user if they are logged in.
 *
 * This function combined with the Session class is smart - if the user is not
 * logged in then they do not get a session, which prevents simple curl hits
 * or search engine crawls to a page from getting sessions they won't use.
 *
 * Once the user has a session, they keep it even if the log out, so it can
 * be reused. The session does expire, but the expiry time is typically a week
 * or more.
 *
 * If the user is not authenticated for this page, then this function will
 * exit, printing the login page. Therefore, after including init.php, you can
 * be sure that the user is logged in, or has a valid guest key. However, no
 * testing is done to make sure the user has the required permissions to see
 * the page.
 *
 * @return object The $USER object, if the user is logged in and continuing
 *                their session.
 */
function auth_setup () {
    global $SESSION, $USER;

    // If the system is not installed, let the user through in the hope that
    // they can fix this little problem :)
    if (!get_config('installed')) {
        $SESSION->logout();
        log_debug('system not installed, letting user through');
        return;
    }

    // Check the time that the session is set to log out. If the user does
    // not have a session, this time will be 0.
    $sessionlogouttime = $SESSION->get('logout_time');
    if ($sessionlogouttime > time()) {
        if (isset($_GET['logout'])) {
            log_debug('logging user ' . $SESSION->get('username') . ' out');
            $SESSION->logout();
            $SESSION->add_ok_msg(get_string('loggedoutok'));
            redirect(get_config('wwwroot'));
        }
        // The session is still active, so continue it.
        log_debug('session still active from previous time');
        $USER = $SESSION->renew();
        auth_check_password_change();
        return $USER;
    }
    else if ($sessionlogouttime > 0) {
        // The session timed out
        log_debug('session timed out');
        $SESSION->logout();
        $SESSION->add_info_msg(get_string('sessiontimedout'));
        auth_draw_login_page();
        exit;
    }
    else {
        // There is no session, so we check to see if one needs to be started.
        // First, check if the page is public or the site is configured to be public.
        if (defined('PUBLIC')) {
            return;
        }

        // Build login form. If the form is submitted it will be handled here,
        // and set $USER for us.
        require_once('form.php');
        $form = new Form(auth_get_login_form());
        if ($USER) {
            log_debug('user logged in just fine');
            auth_check_password_change();
            return;
        }
        
        log_debug('no session or old session, and page is private');
        auth_draw_login_page($form);
        exit;
    }
}

/**
 * Given an institution, returns the authentication method used by it.
 *
 * @return string
 * @todo<nigel>: Currently, the system doesn't have a concept of institution
 * at the database level, so the internal authentication method is assumed.
 */
function auth_get_authtype_for_institution($institution) {
    return 'internal';
}

/**
 * Checks whether the current user needs to change their password, and handles
 * the password changing if it's required.
 *
 * This only applies for the internal authentication plugin. Other plugins
 * will, in theory, have different data stores, making changing the password
 * via the internal form difficult.
 */
function auth_check_password_change() {
    global $SESSION;
    log_debug('checking if the user needs to change their password');
    if (auth_get_authtype_for_institution($SESSION->get('institution')) == 'internal' && $SESSION->get('passwordchange')) {
        log_debug('user DOES need to change their password');
        require_once('form.php');
        $form = array(
            'name' => 'change_password',
            'method' => 'post',
            'elements' => array(
                'password1' => array(
                    'type' => 'password',
                    'title' => 'New Password:',
                    'description' => 'Your new password',
                    'rules' => array(
                        'required' => true
                    )
                ),
                'password2' => array(
                    'type' => 'password',
                    'title' => 'Confirm Password:',
                    'description' => 'Your new password again',
                    'rules' => array(
                        'required' => true
                    )
                ),
                'submit' => array(
                    'type' => 'submit',
                    'value' => 'Change Password'
                )
            )
        );

        $smarty = smarty();
        $smarty->assign('change_password_form', form($form));
        $smarty->display('change_password.tpl');
        exit;
    }
}

/**
 * Validates the form for changing the password for a user.
 *
 * This only applies to the internal authentication plugin.
 *
 * @todo check that the password isn't something simple, like 'mahara'.
 * @param Form  $form   The form to check
 * @param array $values The values to check
 */
function change_password_validate(Form $form, $values) {
    global $SESSION;

    // Get the authentication type for the user (based on the institution), and
    // use the information to validate the password
    $authtype = auth_get_authtype_for_institution($SESSION->get('institution'));
    if ($authtype == 'internal') {
        safe_require('auth', $authtype, 'lib.php', 'require_once');

        // Check that the password is in valid form
        if (!$form->get_error('password1')
            && !call_static_method('AuthInternal', 'is_password_valid', $values['password1'])) {
            $form->set_error('password1', 'Your password is not in a valid form');
        }

        // The password must not be too easy :)
        $suckypasswords = array(
            'mahara', 'password', $SESSION->get('username')
        );
        if (!$form->get_error('password1') && in_array($values['password1'], $suckypasswords)) {
            $form->set_error('password1', 'Your password is too easy! Please choose a harder password');
        }

        // The password cannot be the same as the old one
        if (!$form->get_error('password1') && $values['password1'] == get_field('usr', 'password', 'username', $SESSION->get('username'))) {
            $form->set_error('password1', 'Your did not change your password!');
        }

        // The passwords must match
        if (!$form->get_error('password1') && !$form->get_error('password2') && $values['password1'] != $values['password2']) {
            $form->set_error('password2', 'Your passwords do not match');
        }
    }
    else {
        throw new Exception('The user "' . $USER->username . '" is trying to'
            . ' change their password, but they do not use the internal'
            . ' authentication method');
    }
}

/**
 * Changes the password for a user, given that it is valid.
 *
 * This only applies to the internal authentication plugin.
 *
 * @param array $values The submitted form values
 */
function change_password_submit($values) {
    global $SESSION;
    log_debug('changing password to ' . $values['password1']);

    $authtype = auth_get_authtype_for_institution($SESSION->get('institution'));
    if ($authtype == 'internal') {
        // Create a salted password and set it for the user
        safe_require('auth', $authtype, 'lib.php', 'require_once');
        $user = new StdClass;
        $user->salt = substr(md5(rand(1000000, 9999999)), 2, 8);
        $user->password = call_static_method('AuthInternal', 'encrypt_password', $values['password1'], $user->salt);
        $user->passwordchange = 0;
        $where = new StdClass;
        $where->username = $SESSION->get('username');
        update_record('usr', $user, $where);

        $SESSION->set('passwordchange', 0);
        $SESSION->add_ok_msg('Your new password has been saved');
        redirect(get_config('wwwroot'));
        exit;
    }
    else {
        throw new Exception('The user "' . $USER->username . '" is trying to'
            . ' change their password, but they do not use the internal'
            . ' authentication method');
    }
}

/**
 * Creates and displays the transient login page.
 *
 * This login page remembers all GET/POST data and passes it on. This way,
 * users can have their sessions time out, and then can log in again without
 * losing any of their data.
 *
 * @param Form $form If specified, just build this form to get the HTML
 *                   required. Otherwise, this function will build and
 *                   validate the form itself.
 * @access private
 */
function auth_draw_login_page(Form $form=null) {
    if ($form != null) {
        $loginform = $form->build();
    }
    else {
        require_once('form.php');
        $loginform = form(auth_get_login_form());
    }
    $smarty = smarty();
    $smarty->assign('login_form', $loginform);
    $smarty->display('login.tpl');
    exit;
}

/**
 * Returns the definition of the login form.
 *
 * @return array   The login form definition array.
 * @access private
 */
function auth_get_login_form() {
    $elements = array(
        'login' => array(
            'type'   => 'fieldset',
            'legend' => get_string('login'),
            'elements' => array(
                'login_username' => array(
                    'type'        => 'text',
                    'title'       => get_string('username'),
                    'description' => get_string('usernamedesc'),
                    'help'        => get_string('usernamehelp'),
                    'rules' => array(
                        'required'    => true
                    )
                ),
                'login_password' => array(
                    'type'        => 'password',
                    'title'       => get_string('password'),
                    'description' => get_string('passworddesc'),
                    'help'        => get_string('passwordhelp'),
                    'value'       => '',
                    'rules' => array(
                        'required'    => true
                    )
                )
            )
        ),

        'submit' => array(
            'type'  => 'submit',
            'value' => get_string('login')
        )
    );

    // The login page is completely transient, and it is smart because it
    // remembers the GET and POST data sent to it and resends that on
    // afterwards. 
    $action = '';
    if ($_GET) {
        if (isset($_GET['logout'])) {
            // You can log the user out on any particular page by appending
            // ?logout to the URL. In this case, we don't want the "action"
            // of the url to include that, or be blank, else the next time
            // the user logs in they will be logged out again.
            $action = hsc(substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')));
        } else {
            $action = '?';
            foreach ($_GET as $key => $value) {
                if ($key != 'logout') {
                    $action .= hsc($key) . '=' . hsc($value) . '&amp;';
                }
            }
            $action = substr($action, 0, -5);
        }
    }
    if ($_POST) {
        foreach ($_POST as $key => $value) {
            if (!isset($elements[$key]) && !isset($elements['login']['elements'][$key])) {
                $elements[$key] = array(
                    'type'  => 'hidden',
                    'value' => $value
                );
            }
        }
    }

    $form = array(
        'name'     => 'login',
        'method'   => 'post',
        'action'   => $action,
        'elements' => $elements,
        'iscancellable' => false
    );

    return $form;
}


/**
 * Called when the login form is submittd. Validates the user and password, and
 * if they are valid, starts a new session for the user.
 *
 * @param array $values The submitted values
 * @access private
 */
function login_submit($values) {
    global $SESSION, $USER;

    log_debug('auth details supplied, attempting to log user in');
    $username    = $values['login_username'];
    $password    = $values['login_password'];
    $institution = (isset($values['login_institution'])) ? $values['login_institution'] : 0;
            
    $authtype = auth_get_authtype_for_institution($institution);
    safe_require('auth', $authtype, 'lib.php', 'require_once');
    $authclass = 'Auth' . ucfirst($authtype);

    try {
        if (call_static_method($authclass, 'authenticate_user_account', $username, $password, $institution)) {
            log_debug('user ' . $username . ' logged in OK');
            $USER = call_static_method($authclass, 'get_user_info', $username);
            $SESSION->login($USER);
            $USER->logout_time = $SESSION->get('logout_time');
            auth_check_password_change();
        }
        else {
            // Login attempt failed
            log_debug('login attempt FAILED');
            $SESSION->add_err_msg(get_string('loginfailed'));
        }
    }
    catch (AuthUnknownUserException $e) {
        log_debug('unknown user ' . $username);
        $SESSION->add_err_msg(get_string('loginfailed'));
    }
}

/**
 * Passes the form data through to the validation method of the appropriate
 * authentication plugin, for it to validate if necessary.
 *
 * This is for validation of the configuration form that each authentication
 * method exports
 *
 * @param Form  $form   The form to validate
 * @param array $values The values submitted to check
 * @access private
 */
function auth_validate(Form $form, $values) {
    $class = 'Auth' . $values['method'];
    safe_require('auth', $values['method'], 'lib.php', 'require_once');
    call_static_method($class, 'validate_configuration_form', $form, $values);
}

/**
 * Handles submission of the configuration form for an authentication method.
 * Sets each configuration value in the database.
 *
 * @param array $values The submitted values, successfully validated
 * @access private
 */
function auth_submit($values) {
    global $SESSION, $db;
    $db->StartTrans();

    foreach ($values as $key => $value) {
        if (!in_array($key, array('submit', 'method'))) {
            set_config_plugin('auth', $values['method'], $key, $value);
        }
    }
    if ($db->HasFailedTrans()) {
        $db->CompleteTrans();
        throw new Exception('Could not update the configuration options for the auth method');
    }
    $db->CompleteTrans();
    $SESSION->add_ok_msg(get_string('authconfigurationoptionssaved') . ' ' . get_config_plugin('auth', $values['method'], $key));
}

?>
