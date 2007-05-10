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
 * We tried to call a method on an auth plugin that hasn't been init'ed 
 * successfully
 */
class UninitialisedAuthException extends SystemException {}

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

    protected $instanceid;
    protected $institution;
    protected $instancename;
    protected $priority;
    protected $authname;
    protected $config;
    protected $has_config;
    protected $type;
    protected $ready;

    /**
     * Given an id, create the auth object and retrieve the config settings
     * If an instance ID is provided, get all the *instance* config settings
     *
     * @param  int  $id   The unique ID of the auth instance
     * @return bool       Whether the create was successful
     */
    public function __construct($id = null) {
        $this->ready = false;
    }

    /**
     * Instantiate the plugin by pulling the config data for an instance from
     * the database
     *
     * @param  int  $id   The unique ID of the auth instance
     * @return bool       Whether the create was successful
     */
    public function init($id) {
        if (!is_numeric($id) || intval($id) != $id) {
            throw new UserNotFoundException();
        }

        $instance = get_record('auth_instance', 'id', $id);
        if (empty($instance)) {
            throw new UserNotFoundException();
        }

        $this->instanceid   = $id;
        $this->institution  = $instance->institution;
        $this->instancename = $instance->instancename;
        $this->priority     = $instance->priority;
        $this->authname     = $instance->authname;

        // Return now if the plugin type doesn't require any config 
        // (e.g. internal)
        if ($this->has_config == false) {
            return true;
        }

        $records = get_records_array('auth_instance_config', 'instance', $this->instanceid);

        if ($records == false) {
            return false;
        }

        foreach($records as $record) {
            $this->config[$record->field] = $record->value;
        }

        return true;
    }

    /**
     * The __get overloader is invoked when the requested member is private or
     * protected, or just doesn't exist.
     * 
     * @param  string  $name   The name of the value to fetch
     * @return mixed           The value
     */
    public function __get($name) {
        $approved_members = array('instanceid', 'institution', 'instancename', 'priority', 'authname');

        if (in_array($name, $approved_members)) {
            return $this->{$name};
        }

        if (isset($this->config['name'])) {
            return $this->config['name'];
        }
        return null;
    }

    /**
     * The __set overloader is invoked when the specified member is private or
     * protected, or just doesn't exist.
     * 
     * @param  string  $name   The name of the value to set
     * @param  mixed   $value  The value to assign
     * @return void
     */
    public function __set($name, $value) {
        /*
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
            return;
        }
        */
        throw new SystemException('It\'s forbidden to set values on Auth objects');
    }

    /**
     * Check that the plugin has been initialised before we try to use it.
     * 
     * @throws UninitialisedAuthException
     * @return bool 
     */
    protected function must_be_ready() {
        if ($this->ready == false) {
            throw new UninitialisedAuthException('This Auth plugin has not been initialised');
        }
        return true;
    }

    /**
     * Fetch the URL that users can visit to change their passwords. This might
     * be a Moodle installation, for example.
     * 
     * @return  mixed   URL to change password or false if there is none
     */
    public function changepasswordurl() {
        $this->must_be_ready();
        if (empty($this->config['changepasswordurl'])) {
            return false;
        }
        return $this->config['changepasswordurl'];
    }

    /**
     * Given a username and password, attempts to log the user in.
     *
     * @param object $user      An object with username member (at least)
     * @param string $password  The password to use for the attempt
     * @return bool             Whether the authentication was successful
     * @throws AuthUnknownUserException  If the user is unknown to the
     *                                   authentication method
     */
    public function authenticate_user_account($user, $password) {
        $this->must_be_ready();
        return false;
    }

    /**
     * Given a username, returns whether the user exists in the usr table
     *
     * @param string $username The username to attempt to identify
     * @return bool            Whether the username exists
     */
    public function user_exists($username) {
        $this->must_be_ready();
        if (record_exists('usr', 'LOWER(username)', strtolower($username), 'authinstance', $this->instanceid)) {
            return true;
        }
        throw new AuthUnknownUserException("\"$username\" is not known to Auth");
    }

    /**
     * Given a username, returns a hash of information about a user from the
     * external data source, e.g. Moodle or Drupal.
     *
     * @param string $username The username to look up information for
     * @return array           The information for the user
     * @throws AuthUnknownUserException If the user is unknown to the
     *                                  authentication method
     */
    public function get_user_info($username) {
        return false;
    }

    /**
     * Given a username, return information about the user from the database.
     * This object must_be_ready, which means it will have an authinstanceid. 
     * This is used to disambiguate between users with the same username.
     *
     * The information retrieved must be all rows in the user table, with the
     * timestamps formatted as unix timestamps. An example (taken from the
     * internal authentication mechanism, which allows usernames to be case
     * insensitive):
     *
     * <code>
     * get_record('usr', 'LOWER(username)', strtolower($username), null, null, null, null,
     *    '*, ' . db_format_tsfield('expiry') . ', ' . db_format_tsfield('lastlogin'));
     * </code>
     *
     * @param string $username The username to get information for
     * @return array           Data that can be used to populate the session
     * @throws AuthUnknownUserException If the user is unknown to the
     *                                  authentication method
     */
    public function get_user_info_cached($username) {
        $this->must_be_ready();
        if (!$result = get_record('usr', 'LOWER(username)', strtolower($username), 'authinstance', $this->instanceid,
                    '*, ' . db_format_tsfield('expiry') . ', ' . db_format_tsfield('lastlogin'))) {
            throw new AuthUnknownUserException("\"$username\" is not known to AuthInternal");
        }
        $cache[$result->username] = $result;
        return $result;
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
    public function is_password_valid($password) {
        return true;
    }

}


/******************************************************************************/
    // End of Auth base-class
/******************************************************************************/

/**
 * Handles authentication by setting up a session for a user if they are logged 
 * in.
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
            $userreallyadmin = get_field('usr', 'admin', 'id', $USER->id);
            if (!$USER->get('admin') && $userreallyadmin) {
                // The user has been made into an admin
                $USER->admin = 1;
            }
            else if ($USER->get('admin') && !$userreallyadmin) {
                // The user's admin rights have been taken away
                $USER->admin = 0;
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
 * Given an institution, returns the authentication methods used by it.
 *
 * @param  string   $institution     Name of the institution
 * @return array                     Array of auth instance records
 */
function auth_get_auth_instances_for_institution($institution) {
    static $cache = array();

    if (!isset($cache[$institution])) {
        $cache[$institution] = get_records_array('auth_instance', 'institution', $institution, 'priority, instancename', 'id, instancename, priority, authname');
        if(empty($cache[$institution])) {
            return false;
        }
    }

    return $cache[$institution];
}

/**
 * Given a user, returns the authentication instance used.
 *
 * @param  object   $user            User object with an authinstance member
 * @return array                     Array of auth instance records
 */
function auth_get_auth_instance($user) {
    static $cache = array();

    if (!isset($cache[$user->id])) {
        $cache[$user->id] = get_record('auth_instance', 'id', $user->authinstance, null, null, null, null, 'id, instancename, priority, authname');
    }

    return $cache[$user->id];
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

    $authobj = AuthFactory::create($USER->authinstance);

    if ($authobj->changepasswordurl) {
        redirect($authobj->changepasswordurl);
        exit;
    }

    // @todo auth preference for a password change screen for all auth methods other than internal
    if (method_exists($authobj, 'change_password')) {

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

    // Get the authentication type for the user, and
    // use the information to validate the password
    $authobj = AuthFactory::create($USER->authinstance);

    // @todo this could be done by a custom form rule... 'password' => $user
    password_validate_user($form, $values, $USER);

    // The password cannot be the same as the old one
    if (!$form->get_error('password1')
        && $authobj->authenticate_user_account($USER, $values['password1'])) {
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

    $authobj = AuthFactory::create($USER->authinstance);

    // This method should exist, because if it did not then the change
    // password form would not have been shown.
    if ($password = $authobj->change_password($USER, $values['password1'])) {
        $SESSION->add_ok_msg(get_string('passwordsaved'));
        if (!empty($values['email'])) {
            set_profile_field($USER->id, 'email', $values['email']);
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
var loginbox = $('loginform_container');
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
 * Class to build and cache instances of auth objects
 */
class AuthFactory {

    static $authcache = array();

    /**
     * Take an instanceid and create an auth object for that instance. 
     * 
     * @param  int      $id     The id of the auth instance
     * @return mixed            An intialised auth object or false, if the
     *                          instance doesn't exist (Should never happen)
     */
    public static function create($id) {

        if (isset(self::$authcache[$id]) && is_object(self::$authcache[$id])) {
            return self::$authcache[$id];
        }

        $authinstance = get_record('auth_instance', 'id', $id, null, null, null, null, 'authname');

        if (!empty($authinstance)) {
            $authclassname = 'Auth' . ucfirst($authinstance->authname);
            safe_require('auth', $authinstance->authname);
            self::$authcache[$id] = new $authclassname($id);
            return self::$authcache[$id];
        }

        return false;
    }
}

/**
 * Called when the login form is submitted. Validates the user and password, and
 * if they are valid, starts a new session for the user.
 *
 * @param object $form   The Pieform form object
 * @param array  $values The submitted values
 * @access private
 */
function login_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    $username      = $values['login_username'];
    $password      = $values['login_password'];
    $institution   = (isset($values['login_institution'])) ? $values['login_institution'] : 'mahara';
    $authenticated = false;
    $oldlastlogin  = 0;

    try {
        //var_dump(array($username, $password, $institution, $_SESSION));
        $authenticated = $USER->login($username, $password, $institution);

        if (empty($authenticated)) {
            $SESSION->add_error_msg(get_string('loginfailed'));
        }

    } catch (AuthUnknownUserException $e) {
        try {
            // The user doesn't exist, but maybe the institution wants us to 
            // create users that don't exist
            $institution  = get_record('institution', 'name', $institution);
            if ($institution != false) {
                if ($institution->updateuserinfoonlogin == 0) {
                    // Institution does not want us to create unknown users
                    throw new AuthUnknownUserException("\"$username\" at \"$institution\" is not known");
                }
            }

            // Institution says we should create unknown users
            // Get all the auth options for the institution
            $authinstances = get_records_array('auth_instance', 'institution', $institution, 'priority, instancename', 'id, instancename, priority, authname');

            $USER->username    = $username;
            $USER->institution = $institution->name;

            while (list(, $authinstance) = each($authinstances) && false == $authenticated) {
                // TODO: Test this code with an auth plugin that provides a 
                // get_user_info method
                $auth = AuthFactory::create($authinstance->id);
                if ($auth->authenticate_user_account($USER, $password)) {
                    $authenticated = true;
                } else {
                    continue;
                }

                $USER->authinstance = $authinstance->id;

                if($auth->authenticate_user_account($username, $password, $institution)) {
                    $userdata = $auth->get_user_info();
                    if (
                         empty($userdata) ||
                         empty($userdata->firstname) ||
                         empty($userdata->lastname) ||
                         empty($userdata->email) 
                        ) {
                        throw new AuthUnknownUserException("\"$username\" at \"$institution\" is not known");
                    } else {
                        // We have the data - create the user
                        $USER->expiry    = db_format_timestamp(time() + 86400);
                        $USER->lastlogin = db_format_timestamp(time());
                        $USER->firstname = $userdata->firstname;
                        $USER->lastname  = $userdata->lastname;
                        $USER->email     = $userdata->email;

                        try {
                            db_begin();
                            $USER->commit();
    
                            handle_event('createuser', $USER);
                            db_commit();
                        } catch (Exception $e) {
                            db_rollback();
                            throw $e;
                        }
                    }
                }
            }

        } catch (AuthUnknownUserException $e) {
            $SESSION->add_error_msg(get_string('loginfailed'));
        }
    } catch (Exception $e) {
        // Unknown Exception!!!
        $SESSION->add_error_msg(get_string('loginfailed'));
    }

    // Only admins in the admin section!
    if (defined('ADMIN') && !$USER->admin) {
        $SESSION->add_error_msg(get_string('accessforbiddentoadminsection'));
        redirect();
    }

    // Check if the user's account has been deleted
    if ($USER->deleted) {
        die_info(get_string('accountdeleted'));
    }

    // Check if the user's account has expired
    if ($USER->expiry > 0 && time() > $USER->expiry) {
        die_info(get_string('accountexpired'));
    }

    // Check if the user's account has become inactive
    $inactivetime = get_field('institution', 'defaultaccountinactiveexpire', 'name', $USER->institution);
    if ($inactivetime && $oldlastlogin > 0
        && $oldlastlogin + $inactivetime < time()) {
        die_info(get_string('accountinactive'));
    }

    // Check if the user's account has been suspended
    if ($USER->suspendedcusr) {
        die_info(get_string('accountsuspended', 'mahara', $USER->suspendedctime, $USER->suspendedreason));
    }

    // User is allowed to log in
    //$USER->login($userdata);
    auth_check_password_change();
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

function auth_generate_login_form() {
    if (!get_config('installed')) {
        return;
    }
    require_once('pieforms/pieform.php');
    $institutions = get_records_menu('institution', '', '', 'name, displayname');
    $defaultinstitution = get_cookie('institution');
    if (!$defaultinstitution) {
        $defaultinstitution = 'mahara';
    }
    $loginform = get_login_form_js(pieform(array(
        'name'       => 'login',
        'renderer'   => 'div',
        'submit'     => false,
        'plugintype' => 'auth',
        'pluginname' => 'internal',
        'elements'   => array(
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
                'type' => 'select',
                'title' => get_string('institution') . ':',
                'defaultvalue' => $defaultinstitution,
                'options' => $institutions,
                'rules' => array(
                    'required' => true
                ),
                'ignore' => count($institutions) == 1,
                'help' => true,
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('login')
            ),
            'register' => array(
                'value' => '<div><a href="' . get_config('wwwroot') . 'register.php" tabindex="2">' . get_string('register') . '</a>'
                    . '<br><a href="' . get_config('wwwroot') . 'forgotpass.php" tabindex="2">' . get_string('passwordreminder') . '</a></div>'
            )
        )
    )));

    return $loginform;
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

    public static function update_active_flag($event, $user) {
        $active = true;

        // ensure we have everything we need
        $user = get_user($user['id']);

        $inactivetime = get_field('institution', 'defaultaccountinactiveexpire', 'name', $user->institution);
        if ($user->suspendedcusr) {
            $active = false;
        }
        else if ($user->expiry && $user->expiry < time()) {
            $active = false;
        }
        else if ($inactivetime && $user->lastlogin + $inactivetime < time()) {
            $active = false;
        }
        else if ($user->deleted) {
            $active = false;
        }

        if ($active != $user->active) {
            set_field('usr', 'active', (int)$active, 'id', $user->id);
        }
    }

}

?>
