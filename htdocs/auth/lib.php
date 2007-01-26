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
require(get_config('docroot') . 'auth/user.php');

/**
 * Unknown user exception
 */
class AuthUnknownUserException extends UserException {}

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
 */
function auth_setup () {
    global $SESSION, $USER;

    // If the system is not installed, let the user through in the hope that
    // they can fix this little problem :)
    if (!get_config('installed')) {
        $USER->logout();
        return;
    }

    // Check the time that the session is set to log out. If the user does
    // not have a session, this time will be 0.
    $sessionlogouttime = $USER->get('logout_time');
    if ($sessionlogouttime && isset($_GET['logout'])) {
        $USER->logout();
        $SESSION->add_ok_msg(get_string('loggedoutok'));
        redirect();
    }
    if ($sessionlogouttime > time()) {
        // The session is still active, so continue it.
        // Make sure that if a user's admin status has changed, they're kicked
        // out of the admin section
        if (defined('ADMIN')) {
            $userreallyadmin = get_field('usr', 'admin', 'id', $USER->get('id'));
            if (!$USER->get('admin') && $userreallyadmin) {
                // The user has been made into an admin
                $USER->set('admin', 1);
            }
            else if ($USER->get('admin') && !$userreallyadmin) {
                // The user's admin rights have been taken away
                $USER->set('admin', 0);
                $SESSION->add_error_msg(get_string('accessforbiddentoadminsection'));
                redirect();
            }
            elseif (!$USER->get('admin')) {
                // The user never was an admin
                $SESSION->add_error_msg(get_string('accessforbiddentoadminsection'));
                redirect();
            }
        }
        $USER->renew();
        auth_check_password_change();
    }
    else if ($sessionlogouttime > 0) {
        // The session timed out
        $USER->logout();

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
    }
    else {
        // There is no session, so we check to see if one needs to be started.
        // Build login form. If the form is submitted it will be handled here,
        // and set $USER for us (this will happen when users hit a page and
        // specify login data immediately
        require_once('pieforms/pieform.php');
        $form = new Pieform(auth_get_login_form());
        if ($USER->is_logged_in()) {
            return;
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
    if (!$USER->get('passwordchange')) {
        return;
    }

    $authtype  = auth_get_authtype_for_institution($USER->get('institution'));
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

        require_once('pieforms/pieform.php');
        $form = array(
            'name'       => 'change_password',
            'method'     => 'post',
            'plugintype' => 'auth',
            'pluginname' => 'internal',
            'elements'   => array(
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
                        'required' => true,
                    ),
                ),
                'email' => array(
                    'type'   => 'text',
                    'title'  => get_string('principalemailaddress', 'artefact.internal'),
                    'ignore' => (trim($USER->get('email')) != '' && !preg_match('/@example\.org$/', $USER->get('email'))),
                    'rules'  => array(
                        'required' => true,
                        'email'    => true,
                    ),
                ),
                'submit' => array(
                    'type'  => 'submit',
                    'value' => get_string('changepassword'),
                ),
            )
        );

        $smarty = smarty();
        $smarty->assign('change_password_form', pieform($form));
        $smarty->display('change_password.tpl');
        exit;
    }
}

/**
 * Validates the form for changing the password for a user.
 *
 * Change password will only be if a URL for it exists, or a function exists.
 *
 * @param Pieform  $form   The form to check
 * @param array    $values The values to check
 */
function change_password_validate(Pieform $form, $values) {
    global $USER;

    // Get the authentication type for the user (based on the institution), and
    // use the information to validate the password
    $authtype  = auth_get_authtype_for_institution($USER->get('institution'));
    $authclass = 'Auth' . ucfirst($authtype);
    $authlang  = 'auth.' . $authtype;
    safe_require('auth', $authtype);

    // @todo this could be done by a custom form rule... 'password' => $user
    password_validate($form, $values, $USER->get('username'), $USER->get('institution'));

    // The password cannot be the same as the old one
    if (!$form->get_error('password1')
        && call_static_method($authclass, 'authenticate_user_account', $USER->get('username'), $values['password1'], $USER->get('institution'))) {
        $form->set_error('password1', get_string('passwordnotchanged'));
    }
}

/**
 * Changes the password for a user, given that it is valid.
 *
 * @param array $values The submitted form values
 */
function change_password_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    $authtype = auth_get_authtype_for_institution($USER->get('institution'));
    $authclass = 'Auth' . ucfirst($authtype);

    // This method should exist, because if it did not then the change
    // password form would not have been shown.
    if ($password = call_static_method($authclass, 'change_password', $USER->get('username'), $values['password1'])) {
        $user = new StdClass;
        $user->password = $password;
        $user->passwordchange = 0;
        $where = new StdClass;
        $where->username = $USER->get('username');
        update_record('usr', $user, $where);
        $USER->set('password', $password);
        $USER->set('passwordchange', 0);
        $SESSION->add_ok_msg(get_string('passwordsaved'));
        if (!empty($values['email'])) {
            set_profile_field($USER->get('id'), 'email', $values['email']);
        }
        redirect();
    }

    throw new Exception('Attempt by "' . $USER->get('username') . '@'
        . $USER->get('institution') . 'to change their password failed');
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
 * @param Pieform $form If specified, just build this form to get the HTML
 *                      required. Otherwise, this function will build and
 *                      validate the form itself.
 * @access private
 */
function auth_draw_login_page($message=null, Pieform $form=null) {
    global $USER, $SESSION;
    if ($form != null) {
        $loginform = get_login_form_js($form->build());
    }
    else {
        require_once('pieforms/pieform.php');
        $loginform = get_login_form_js(pieform(auth_get_login_form()));
        /*
         * If $USER is set, the form was submitted even before being built.
         * This happens when a user's session times out and they resend post
         * data. The request should just continue if so.
         */
        if ($USER->is_logged_in()) {
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
    $institutions = get_records_menu('institution', '', '', 'name, displayname');
    $defaultinstitution = get_cookie('institution');
    if (!$defaultinstitution) {
        $defaultinstitution = 'mahara';
    }

    $elements = array(
        'login' => array(
            'type'   => 'fieldset',
            'legend' => get_string('login'),
            'elements' => array(
                'login_username' => array(
                    'type'        => 'text',
                    'title'       => get_string('username') . ':',
                    'description' => get_string('usernamedescription'),
                    'rules' => array(
                        'required'    => true
                    )
                ),
                'login_password' => array(
                    'type'        => 'password',
                    'title'       => get_string('password') . ':',
                    'description' => get_string('passworddescription'),
                    'value'       => '',
                    'rules' => array(
                        'required'    => true
                    )
                ),
                'login_institution' => array(
                    'type'         => 'select',
                    'title'        => get_string('institution'). ':',
                    'defaultvalue' => $defaultinstitution,
                    'options'      => $institutions,
                    'rules' => array(
                        'required' => true
                    ),
                    'ignore' => count($institutions) == 1
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
        'name'           => 'login',
        'method'         => 'post',
        'action'         => $action,
        'plugintype'     => 'auth',
        'pluginname'     => 'internal',
        'elements'       => $elements,
        'dieaftersubmit' => false,
        'iscancellable'  => false
    );

    return $form;
}

/**
 * Returns javascript to assist with the rendering of the login forms. The
 * javascript is used to detect whether cookies are enabled, and not show the
 * login form if they are not.
 *
 * @param string  $form A rendered login form
 * @return string The form with extra javascript added for cookie detection
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
function login_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    $username    = $values['login_username'];
    $password    = $values['login_password'];
    $institution = (isset($values['login_institution'])) ? $values['login_institution'] : 'mahara';
            
    $authtype = auth_get_authtype_for_institution($institution);
    safe_require('auth', $authtype);
    $authclass = 'Auth' . ucfirst($authtype);

    try {
        if (call_static_method($authclass, 'authenticate_user_account', $username, $password, $institution)) {
            // User logged in! Set a cookie to remember their institution
            set_cookie('institution', $institution);
            $oldlastlogin = null;

            if (!record_exists('usr', 'username', $username)) {
                // We don't know about this user. But if the authentication
                // method says they're fine, then we must insert data for them
                // into the usr table.
                // @todo document what needs to be returned by get_user_info
                $userdata = call_static_method($authclass, 'get_user_info', $username);
                $userdata->lastlogin = db_format_timestamp(time());
                insert_record('usr', $userdata);
                handle_event('createuser', $userdata);
            }
            // @todo config form option for this for each external plugin. NOT for internal
            else if (get_config_plugin('auth', $authtype, 'updateuserinfoonlogin')) {
                $userdata = call_static_method($authclass, 'get_user_info', $username);
                $oldlastlogin = $userdata->lastlogin;
                $userdata->lastlogin = db_format_timestamp(time());
                $userdata->inactivemailsent = 0;
                $where = new StdClass;
                $where->username = $username;
                $where->institution = $institution;
                // @todo as per the above todo about what needs to be returned by get_user_info,
                // that needs to be validated somewhere. Because here we do an insert into the
                // usr table, that needs to work. and provide enough information for future
                // authentication attempts
                update_record('usr', $userdata, $where);
            }
            else {
                $userdata = get_record('usr', 'username', $username, null, null, null, null,
                    '*, ' . db_format_tsfield('expiry') . ', ' . db_format_tsfield('lastlogin'));
                $oldlastlogin = $userdata->lastlogin;
                $userdata->lastlogin = time();
                $userdata->inactivemailsent = 0;
                set_field('usr', 'lastlogin', db_format_timestamp($userdata->lastlogin), 'username', $username);
                set_field('usr', 'inactivemailsent', 0, 'username', $username);
            }

            // Only admins in the admin section!
            if (defined('ADMIN') && !$userdata->admin) {
                $SESSION->add_error_msg(get_string('accessforbiddentoadminsection'));
                redirect();
            }

            // Check if the user's account has been deleted
            if ($userdata->deleted) {
                die_info(get_string('accountdeleted'));
            }

            // Check if the user's account has expired
            if ($userdata->expiry > 0 && time() > $userdata->expiry) {
                die_info(get_string('accountexpired'));
            }

            // Check if the user's account has become inactive
            $inactivetime = get_field('institution', 'defaultaccountinactiveexpire', 'name', $userdata->institution);
            if ($inactivetime && $oldlastlogin > 0
                && $oldlastlogin + $inactivetime < time()) {
                die_info(get_string('accountinactive'));
            }

            // Check if the user's account has been suspended
            if ($userdata->suspendedcusr) {
                die_info(get_string('accountsuspended', 'mahara', $userdata->suspendedctime, $userdata->suspendedreason));
            }

            // User is allowed to log in
            $USER->login($userdata);
            auth_check_password_change();
        }
        else {
            // Login attempt failed
            $SESSION->add_error_msg(get_string('loginfailed'));
        }
    }
    catch (AuthUnknownUserException $e) {
        $SESSION->add_error_msg(get_string('loginfailed'));
    }
}

/**
 * Removes registration requests that were not completed in the allowed amount of time
 */
function auth_clean_partial_registrations() {
    $prefix = get_config('dbprefix');
    delete_records_sql('DELETE FROM ' . $prefix . 'usr_registration
        WHERE expiry < ?', array(db_format_timestamp(time())));
}

/**
 * Sends notification e-mails to users in two situations:
 *
 *  - Their account is about to expire. This is controlled by the 'expiry'
 *    field of the usr table. Once that time has passed, the user may not
 *    log in.
 *  - They have not logged in for close to a certain amount of time. If that
 *    amount of time has passed, the user may not log in.
 *
 * The actual prevention of users logging in is handled by the authentication
 * code. This cron job sends e-mails to notify users that these events will
 * happen soon.
 */
function auth_handle_account_expiries() {
    // The 'expiry' flag on the usr table
    $prefix   = get_config('dbprefix');
    $sitename = get_config('sitename');
    $wwwroot  = get_config('wwwroot');

    // Expiry warning messages
    if ($users = get_records_sql_array('SELECT u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, i.defaultaccountinactivewarn AS timeout
        FROM ' . $prefix . 'usr u, ' . $prefix . 'institution i
        WHERE u.institution = i.name
        AND ? - ' . db_format_tsfield('u.expiry', false) . ' < i.defaultaccountinactivewarn
        AND expirymailsent = 0', array(time()))) {
        foreach ($users as $user) {
            $displayname  = display_name($user);
            $daystoexpire = ceil($user->timeout / 86400) . ' ';
            $daystoexpire .= ($daystoexpire == 1) ? get_string('day') : get_string('days');
            email_user($user, null,
                get_string('accountexpirywarning'),
                get_string('accountexpirywarningtext', 'mahara', $displayname, $sitename, $daystoexpire, $wwwroot . 'contact.php', $sitename),
                get_string('accountexpirywarninghtml', 'mahara', $displayname, $sitename, $daystoexpire, $wwwroot . 'contact.php', $sitename)
            );
            set_field('usr', 'expirymailsent', 1, 'id', $user->id);
        }
    }

    // Actual expired users
    if ($users = get_records_sql_array('SELECT id
        FROM ' . $prefix . 'usr
        WHERE ' . db_format_tsfield('expiry', false) . ' < ?', array(time()))) {
        // Users have expired!
        foreach ($users as $user) {
            expire_user($user->id);
        }
    }

    // Inactivity (lastlogin is too old)
    if ($users = get_records_sql_array('SELECT u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, i.defaultaccountinactivewarn AS timeout
        FROM ' . $prefix . 'usr u, ' . $prefix . 'institution i
        WHERE u.institution = i.name
        AND (? - ' . db_format_tsfield('u.lastlogin', false) . ') > (i.defaultaccountinactiveexpire - i.defaultaccountinactivewarn)
        AND inactivemailsent = 0', array(time()))) {
        foreach ($users as $user) {
            $displayname = display_name($user);
            $daystoinactive = ceil($user->timeout / 86400) . ' ';
            $daystoinactive .= ($daystoexpire == 1) ? get_string('day') : get_string('days');
            email_user($user, null, get_string('accountinactivewarning'),
                get_string('accountinactivewarningtext', 'mahara', $displayname, $sitename, $daystoinactive, $sitename),
                get_string('accountinactivewarninghtml', 'mahara', $displayname, $sitename, $daystoinactive, $sitename)
            );
            set_field('usr', 'inactivemailsent', 1, 'id', $user->id);
        }
    }
    
    // Actual inactive users
    if ($users = get_records_sql_array('SELECT u.id
        FROM ' . $prefix . 'usr u
        LEFT JOIN ' . $prefix . 'institution i ON (u.institution = i.name)
        WHERE ' . db_format_tsfield('lastlogin', false) . ' < ? - i.defaultaccountinactiveexpire', array(time()))) {
        // Users have become inactive!
        foreach ($users as $user) {
            deactivate_user($user->id);
        }
    }
}


class PluginAuth extends Plugin {

    public static function get_event_subscriptions() {
        $subscriptions = array();

        $activecheck = new StdClass;
        $activecheck->plugin = 'internal';
        $activecheck->event  = 'suspenduser';
        $activecheck->callfunction = 'update_active_flag';
        $subscriptions[] = clone $activecheck;
        $activecheck->event = 'unsuspenduser';
        $subscriptions[] = clone $activecheck;
        $activecheck->event = 'deleteuser';
        $subscriptions[] = clone $activecheck;
        $activecheck->event = 'undeleteuser';
        $subscriptions[] = clone $activecheck;
        $activecheck->event = 'expireuser';
        $subscriptions[] = clone $activecheck;
        $activecheck->event = 'unexpireuser';
        $subscriptions[] = clone $activecheck;
        $activecheck->event = 'deactivateuser';
        $subscriptions[] = clone $activecheck;
        $activecheck->event = 'activateuser';
        $subscriptions[] = clone $activecheck;

        return $subscriptions;
    }

    public static function update_active_flag($event, $userid) {
        $active = true;

        $user = get_user($userid);

        $inactivetime = get_field('institution', 'defaultaccountinactiveexpire', 'name', $user->institution);
        if ($user->suspendedcusr) {
            $active = false;
        }
        else if ($user->expiry < time()) {
            $active = false;
        }
        else if ($inactivetime && $user->lastlogin + $inactivetime < time()) {
            $active = false;
        }
        else if ($user->deleted) {
            $active = false;
        }

        if ($active != $user->active) {
            set_field('usr', 'active', (int)$active, 'id', $userid);
        }
    }

}

?>
