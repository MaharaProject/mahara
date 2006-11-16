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

require('session.php');

/**
 * Unknown user exception
 */
class AuthUnknownUserException extends Exception {}

/**
 * Base authentication class. Provides a common interface with which
 * authentication can be carried out for system users.
 *
 * @todo for authentication:
 *   - inactivity: each institution has inactivity timeout times, this needs
 *     to be supported
 *     - this means the lastlogin field needs to be updated on the usr table
 *     - warnings are handled by cron
 */
abstract class Auth {

    /**
     * Given a username, password and institution, attempts to log the use in.
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
     * Given a password, returns whether it is in a valid format for this
     * authentication method.
     *
     * This only needs to be defined by subclasses if:
     *  - They implement the change_password method, which means that the
     *    system can use the <kbd>passwordchange</kbd> flag on the <kbd>usr</kbd>
     *    table to control whether the user's password needs changing.
     *  - The password that a user can set must be in a certain format.
     *
     * The default behaviour is to assume that the password is in a valid form,
     * so make sure to implement this method if this is not the case!
     *
     * This method is defined to be empty, so that authentication methods do 
     * not have to specify a format if they do not need to.
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
        return;
    }

    // Check the time that the session is set to log out. If the user does
    // not have a session, this time will be 0.
    $sessionlogouttime = $SESSION->get('logout_time');
    if ($sessionlogouttime && isset($_GET['logout'])) {
        if (isset($_GET['logout'])) {
            $SESSION->logout();
            $SESSION->add_ok_msg(get_string('loggedoutok'));
            redirect(get_config('wwwroot'));
        }
    }
    if ($sessionlogouttime > time()) {
        // The session is still active, so continue it.
        // Make sure that if a user's admin status has changed, they're kicked
        // out of the admin section
        if (defined('ADMIN')) {
            $userreallyadmin = get_field('usr', 'admin', 'id', $SESSION->get('id'));
            if (!$SESSION->get('admin') && $userreallyadmin) {
                // The user has been made into an admin
                $SESSION->set('admin', 1);
            }
            else if ($SESSION->get('admin') && !$userreallyadmin) {
                // The user's admin rights have been taken away
                $SESSION->set('admin', 0);
                $SESSION->add_err_msg(get_string('accessforbiddentoadminsection'));
                redirect(get_config('wwwroot'));
            }
            elseif (!$SESSION->get('admin')) {
                // The user never was an admin
                $SESSION->add_err_msg(get_string('accessforbiddentoadminsection'));
                redirect(get_config('wwwroot'));
            }
        }
        $USER = $SESSION->renew();
        auth_check_password_change();
        return $USER;
    }
    else if ($sessionlogouttime > 0) {
        // The session timed out
        $SESSION->logout();

        // If the page the user is viewing is public, inform them that they can
        // log in again
        if (defined('PUBLIC')) {
            // @todo this links to ?login - later it should do magic to make
            // sure that whatever GET string is made it includes the old data
            // correctly
            $SESSION->add_info_msg(get_string('sessiontimedoutpublic'), false);
            return;
        }

        auth_draw_login_page(get_string('sessiontimedout'));
        // The auth_draw_login_page function may authenticate a user if a login
        // request was sent at the same time that the "timed out" message is to
        // be displayed.
        return $USER;
    }
    else {
        // There is no session, so we check to see if one needs to be started.
        
        // Build login form. If the form is submitted it will be handled here,
        // and set $USER for us (this will happen when users hit a page and
        // specify login data immediately
        require_once('form.php');
        $form = new Form(auth_get_login_form());
        if ($USER) {
            return $USER;
        }
        
        // Check if the page is public or the site is configured to be public.
        if (defined('PUBLIC') && !isset($_GET['login'])) {
            return;
        }
        
        auth_draw_login_page(null, $form);
        exit;
    }
}

/**
 * Given an institution, returns the authentication method used by it.
 *
 * @return string
 */
function auth_get_authtype_for_institution($institution) {
    static $cache = array();
    if (isset($cache[$institution])) {
        return $cache[$institution];
    }
    return $cache[$institution] = get_field('institution', 'authplugin', 'name', $institution);
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
    global $USER;
    if (!$USER->passwordchange) {
        return;
    }

    $authtype  = auth_get_authtype_for_institution($USER->institution);
    $authclass = 'Auth' . ucfirst($authtype);
    $url       = '';
    safe_require('auth', $authtype);
    
    // @todo auth preference for a password change screen for all auth methods other than internal
    if (
        ($url = get_config_plugin('auth', $authtype, 'changepasswordurl'))
        || (method_exists($authclass, 'change_password'))) {
        if ($url) {
            redirect($url);
            exit;
        }

        require_once('form.php');
        $form = array(
            'name'      => 'change_password',
            'method'    => 'post',
            'elements'  => array(
                'passwords' => array(
                    'type' => 'fieldset',
                    'legend' => get_string('newpassword'),
                    'elements' => array(
                        'password1' => array(
                            'type'        => 'password',
                            'title'       => get_string('newpassword') . ':',
                            'description' => get_string('yournewpassword'),
                            'rules'       => array(
                                'required' => true
                            )
                        ),
                        'password2' => array(
                            'type'        => 'password',
                            'title'       => get_string('confirmpassword') . ':',
                            'description' => get_string('yournewpasswordagain'),
                            'rules'       => array(
                                'required' => true
                            )
                        )
                    )
                ),
                'submit' => array(
                    'type'  => 'submit',
                    'value' => get_string('changepassword')
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
 * Change password will only be if a URL for it exists, or a function exists.
 *
 * @param Form  $form   The form to check
 * @param array $values The values to check
 */
function change_password_validate(Form $form, $values) {
    global $USER;

    // Get the authentication type for the user (based on the institution), and
    // use the information to validate the password
    $authtype  = auth_get_authtype_for_institution($USER->institution);
    $authclass = 'Auth' . ucfirst($authtype);
    $authlang  = 'auth.' . $authtype;
    safe_require('auth', $authtype);

    // @todo this could be done by a custom form rule... 'password' => $user
    password_validate($form, $values, $USER);

    // The password cannot be the same as the old one
    if (!$form->get_error('password1')
        && call_static_method($authclass, 'authenticate_user_account', $USER->username, $values['password1'], $USER->institution)) {
        $form->set_error('password1', get_string('passwordnotchanged'));
    }
}

/**
 * Changes the password for a user, given that it is valid.
 *
 * @param array $values The submitted form values
 */
function change_password_submit($values) {
    global $SESSION;
    $authtype = auth_get_authtype_for_institution($SESSION->get('institution'));
    $authclass = 'Auth' . ucfirst($authtype);

    // This method should exists, because if it did not then the change
    // password form would not have been shown.
    if ($password = call_static_method($authclass, 'change_password', $SESSION->get('username'), $values['password1'])) {
        $user = new StdClass;
        $user->password = $password;
        $user->passwordchange = 0;
        $where = new StdClass;
        $where->username = $SESSION->get('username');
        update_record('usr', $user, $where);
        $SESSION->set('password', $password);
        $SESSION->set('passwordchange', 0);
        $SESSION->add_ok_msg(get_string('passwordsaved'));
        redirect(get_config('wwwroot'));
        exit;
    }

    throw new Exception('Attempt by "' . $SESSION->get('username') . '@'
        . $SESSION->get('institution') . 'to change their password failed');
}

/**
 * Creates and displays the transient login page.
 *
 * This login page remembers all GET/POST data and passes it on. This way,
 * users can have their sessions time out, and then can log in again without
 * losing any of their data.
 *
 * As this function builds and validates a login form, it is possible that
 * calling this may validate a user to be logged in.
 *
 * @param Form $form If specified, just build this form to get the HTML
 *                   required. Otherwise, this function will build and
 *                   validate the form itself.
 * @access private
 */
function auth_draw_login_page($message=null, Form $form=null) {
    global $USER, $SESSION;
    if ($form != null) {
        $loginform = get_login_form_js($form->build());
    }
    else {
        require_once('form.php');
        $loginform = get_login_form_js(form(auth_get_login_form()));
        /*
         * If $USER is set, the form was submitted even before being built.
         * This happens when a user's session times out and they resend post
         * data. The request should just continue if so.
         */
        if ($USER) {
            return;
        }

    }

    if ($message) {
        $SESSION->add_info_msg($message);
    }
    $smarty = smarty();
    $smarty->assign('login_form', $loginform);
    $smarty->assign('loginmessage', get_string('loginto', 'mahara', get_config('sitename')));
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
                    'description' => get_string('usernamedescription'),
                    'help'        => get_string('usernamehelp'),
                    'rules' => array(
                        'required'    => true
                    )
                ),
                'login_password' => array(
                    'type'        => 'password',
                    'title'       => get_string('password'),
                    'description' => get_string('passworddescription'),
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
        ),
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
 * Returns javascript to assist with the rendering of the login forms. The
 * javascript is used to detect whether cookies are enabled, and not show the
 * login form if they are not.
 *
 * @param string $form A rendered login form
 * @return string      The form with extra javascript added for cookie detection
 * @private
 */
function get_login_form_js($form) {
    $form = str_replace('/', '\/', str_replace("'", "\'", (str_replace(array("\n", "\t"), '', $form))));
    $strcookiesnotenabled    = get_string('cookiesnotenabled');
    $cookiename = get_config('cookieprefix') . 'ctest';
    return <<<EOF
<script type="text/javascript">
var loginbox = $('loginbox');
document.cookie = "$cookiename=1";
if (document.cookie) {
    loginbox.innerHTML = '$form';
    document.cookie = '$cookiename=1;expires=1/1/1990 00:00:00';
}
else {
    replaceChildNodes(loginbox, P(null, '$strcookiesnotenabled'));
}
</script>
EOF;
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

    $username    = $values['login_username'];
    $password    = $values['login_password'];
    $institution = (isset($values['login_institution'])) ? $values['login_institution'] : 'mahara';
            
    $authtype = auth_get_authtype_for_institution($institution);
    safe_require('auth', $authtype);
    $authclass = 'Auth' . ucfirst($authtype);

    try {
        if (call_static_method($authclass, 'authenticate_user_account', $username, $password, $institution)) {

            if (!record_exists('usr', 'username', $username)) {
                // We don't know about this user. But if the authentication
                // method says they're fine, then we must insert data for them
                // into the usr table.
                // @todo document what needs to be returned by get_user_info
                $USER = call_static_method($authclass, 'get_user_info', $username);
                insert_record('usr', $USER);
            }
            // @todo config form option for this for each external plugin. NOT for internal
            else if (get_config_plugin('auth', $authtype, 'updateuserinfoonlogin')) {
                $USER = call_static_method($authclass, 'get_user_info', $username);
                $where = new StdClass;
                $where->username = $username;
                $where->institution = $institution;
                // @todo as per the above todo about what needs to be returned by get_user_info,
                // that needs to be validated somewhere. Because here we do an insert into the
                // usr table, that needs to work. and provide enough information for future
                // authentication attempts
                update_record('usr', $USER, $where);
            }
            else {
                $USER = get_record('usr', 'username', $username, null, null, null, null, '*, ' . db_format_tsfield('expiry'));
            }

            // Only admins in the admin section!
            if (defined('ADMIN') && !$USER->admin) {
                $SESSION->add_err_msg(get_string('accessforbiddentoadminsection'));
                redirect(get_config('wwwroot'));
            }

            // Check if the user's account has expired
            if ($USER->expiry > 0 && time() > $USER->expiry) {
                // Trash the $USER object, used for checking if the user is logged in.
                // Smarty uses it and puts login-only stuff in the output otherwise...
                $USER = null;
                die_info(get_string('accountexpired'));
            }

            // Check if the user's account has been suspended
            // Note: only the internal authentication method can say if a user is suspended for now.
            // There are problems with how searching excluding suspended users will work that would
            // need to be resolved before this could be implemented for all methods
            if ($suspend = get_record('usr_suspension', 'usr', $USER->id)) {
                $USER = null;
                die_info(get_string('accountsuspended', 'mahara', $suspend->ctime, $suspend->reason));
            }

            // User is allowed to log in
            $SESSION->login($USER);
            $USER->logout_time = $SESSION->get('logout_time');
            auth_check_password_change();
        }
        else {
            // Login attempt failed
            $SESSION->add_err_msg(get_string('loginfailed'));
        }
    }
    catch (AuthUnknownUserException $e) {
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
    // @todo use db_begin/db_commit
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
    $SESSION->add_ok_msg(get_string('authconfigoptionssaved', 'admin'));
}

?>
