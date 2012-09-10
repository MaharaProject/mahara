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
 * @subpackage auth
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

require('session.php');
require(get_config('docroot') . 'auth/user.php');
require_once(get_config('docroot') . '/lib/htmloutput.php');

/**
 * Unknown user exception
 */
class AuthUnknownUserException extends UserException {}

/**
 * An instance of an auth plugin failed during execution 
 * e.g. LDAP auth failed to connect to a directory
 * Developers can use this to fail an individual auth
 * instance, but not kill all from being tried.
 * If appropriate - the 'message' of the exception will be used
 * as the display message, so don't forget to language translate it
 */
class AuthInstanceException extends UserException {

    public function strings() {
        return array_merge(parent::strings(),
                           array('title' => $this->get_sitename() . ': Authentication problem'));
    }
}

/**
 * We tried to call a method on an auth plugin that hasn't been init'ed 
 * successfully
 */
class UninitialisedAuthException extends SystemException {}

/**
 * We tried creating automatically creating an account for a user but
 * it failed for a reason that the user might want to know about
 * (e.g. they used an email address that's already used on the site)
 */
class AccountAutoCreationException extends AuthInstanceException {

    public function strings() {
        return array_merge(parent::strings(),
                           array('message' => 'The automatic creation of your user account failed.'
                                 . "\nDetails if any, follow:"));
    }
}

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
    protected $has_instance_config;
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
        if ($this->has_instance_config == false) {
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
        $approved_members = array('instanceid', 'institution', 'instancename', 'priority', 'authname', 'type');

        if (in_array($name, $approved_members)) {
            return $this->{$name};
        }
        if (isset($this->config[$name])) {
            return $this->config[$name];
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
        if (record_exists_select('usr', 'LOWER(username) = ?', array(strtolower($username)))) {
            return true;
        }
        throw new AuthUnknownUserException("\"$username\" is not known to Auth");
    }

    /**
     * Returns whether the authentication instance can automatically create a 
     * user record.
     *
     * Auto creating users means that the authentication plugin can say that 
     * users who don't exist yet in Mahara's usr table are allowed, and Mahara
     * should create a user account for them. Example: the first time a user logs
     * in, when authenticating against an ldap store or similar).
     *
     * However, if a plugin says a user can be authenticated, then it must 
     * implement the get_user_info() method which will be called to find out 
     * information about the user so a record in the usr table _can_ be created 
     * for the new user.
     *
     * Authentication methods must implement this method. Some may choose to 
     * implement it by returning an instance config value that the admin user 
     * can set.
     *
     * @return bool
     */
    public abstract function can_auto_create_users();

    /**
     * Given a username, returns a hash of information about a user from the
     * external data source.
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

    /**
     * Called when a user is being logged in, after the main authentication routines.
     *
     * You can use $USER->login() to perform any additional tasks, for example
     * to set a cookie that another application can read, or pull some data
     * from somewhere.
     *
     * This method has no parameters and needs no return value
     */
    public function login() {
    }

    /**
     * Called when a user is being logged out, either by clicking a logout 
     * link, their session timing out or some other method where their session 
     * is explicitly being ended with no more processing to take place on this 
     * page load.
     *
     * You can use $USER->logout() to log a user out but continue page 
     * processing if necessary. register.php is an example of such a place 
     * where this happens.
     *
     * If you define this hook, you can call $USER->logout() in it if you need 
     * to before redirecting. Otherwise, it will be called for you once your 
     * hook has run.
     *
     * If you do not explicitly redirect yourself, once this hook is finished 
     * the user will be redirected to the homepage with a message saying they 
     * have been logged out successfully.
     *
     * This method has no parameters and needs no return value
     */
    public function logout() {
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

    // Lock the site until core upgrades are done
    require(get_config('libroot') . 'version.php');
    $siteclosed    = $config->version > get_config('version');
    $disablelogin  = $config->disablelogin;

    if (!$siteclosed && get_config('forcelocalupgrades')) {
        require(get_config('docroot') . 'local/version.php');
        $siteclosed = $config->version > get_config('localversion');
    }

    $cfgsiteclosed = get_config('siteclosed');
    if ($siteclosed && !$cfgsiteclosed || !$siteclosed && $cfgsiteclosed) {
        // If the admin closed the site manually, open it automatically
        // when an upgrade is successful.
        if ($cfgsiteclosed && get_config('siteclosedbyadmin')) {
            set_config('siteclosedbyadmin', false);
        }
        set_config('siteclosed', $siteclosed);
        set_config('disablelogin', $disablelogin);
    }

    // Check the time that the session is set to log out. If the user does
    // not have a session, this time will be 0.
    $sessionlogouttime = $USER->get('logout_time');
    if ($sessionlogouttime && isset($_GET['logout'])) {
        // Call the authinstance' logout hook
        $authinstance = $SESSION->get('authinstance');
        if ($authinstance) {
            $authobj = AuthFactory::create($authinstance);
            $authobj->logout();
        }
        else {
            log_debug("Strange: user " . $USER->get('username') . " had no authinstance set in their session");
        }

        if (function_exists('local_logout')) {
            local_logout();
        }

        $USER->logout();
        $SESSION->add_ok_msg(get_string('loggedoutok'));
        redirect();
    }
    if ($sessionlogouttime > time()) {
        // The session is still active, so continue it.
        // Make sure that if a user's admin status has changed, they're kicked
        // out of the admin section
        if (in_admin_section()) {
            // Reload site admin/staff permissions
            $realuser = get_record('usr', 'id', $USER->id, null, null, null, null, 'admin,staff');
            if (!$USER->get('admin') && $realuser->admin) {
                // The user has been made into an admin
                $USER->admin = 1;
            }
            else if ($USER->get('admin') && !$realuser->admin) {
                // The user's admin rights have been taken away
                $USER->admin = 0;
            }
            if (!$USER->get('staff') && $realuser->staff) {
                $USER->staff = 1;
            }
            else if ($USER->get('staff') && !$realuser->staff) {
                $USER->staff = 0;
            }
            // Reload institutional admin/staff permissions
            $USER->reset_institutions();
            auth_check_admin_section();
        }
        $USER->renew();
        auth_check_required_fields();
    }
    else if ($sessionlogouttime > 0) {
        // The session timed out

        $authinstance = $SESSION->get('authinstance');
        if ($authinstance) {
            $authobj = AuthFactory::create($authinstance);

            $mnetuser = 0;
            if ($SESSION->get('mnetuser') && $authobj->parent) {
                // We wish to remember that the user is an MNET user - even though 
                // they're using the local login form
                $mnetuser = $USER->get('id');
            }

            $authobj->logout();
            $USER->logout();

            if ($mnetuser != 0) {
                $SESSION->set('mnetuser', $mnetuser);
                $SESSION->set('authinstance', $authinstance);
            }
        }
        else {
            log_debug("Strange: user " . $USER->get('username') . " had no authinstance set in their session");
        }

        if (defined('JSON')) {
            json_reply('global', get_string('sessiontimedoutreload'), 1);
        }
        if (defined('IFRAME')) {
            header('Content-type: text/html');
            print_auth_frame();
            exit;
        }

        // If the page the user is viewing is public, inform them that they can
        // log in again
        if (defined('PUBLIC')) {
            // @todo this links to ?login - later it should do magic to make
            // sure that whatever GET string is made it includes the old data
            // correctly
            $loginurl = $_SERVER['REQUEST_URI'];
            $loginurl .= (false === strpos($loginurl, '?')) ? '?' : '&';
            $loginurl .= 'login';
            $SESSION->add_info_msg(get_string('sessiontimedoutpublic', 'mahara', hsc($loginurl)), false);
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
            if ($lang = param_alphanumext('lang', null)) {
                $SESSION->set('lang', $lang);
            }
            return;
        }

        // No session and a json request
        if (defined('JSON')) {
            json_reply('global', get_string('nosessionreload'), 1);
        }
        
        auth_draw_login_page(null, $form);
        exit;
    }
}

/**
 * 
 * Returns all auth instances
 *
 * @return array                     Array of auth instance records
 */
function auth_get_auth_instances() {
    static $cache = array();

    if (count($cache) > 0) {
        return $cache;
    }

    $sql ='
        SELECT DISTINCT
            i.id,
            inst.name,
            inst.displayname,
            i.instancename,
            i.authname
        FROM 
            {institution} inst,
            {auth_instance} i
        WHERE 
            i.institution = inst.name
        ORDER BY
            inst.displayname,
            i.instancename';

    $cache = get_records_sql_array($sql, array());

    if (empty($cache)) {
        return array();
    }

    return $cache;
}


/**
 * 
 * Given a list of institutions, returns all auth instances associated with them
 *
 * @return array                     Array of auth instance records
 */
function auth_get_auth_instances_for_institutions($institutions) {
    if (empty($institutions)) {
        return array();
    }
    $sql ='
        SELECT DISTINCT
            i.id,
            inst.name,
            inst.displayname,
            i.instancename,
            i.authname
        FROM 
            {institution} inst,
            {auth_instance} i
        WHERE 
            i.institution = inst.name AND
            inst.name IN (' . join(',', array_map('db_quote',$institutions)) . ')
        ORDER BY
            inst.displayname,
            i.instancename';

    return get_records_sql_array($sql, array());
}


/**
 * Given an institution, returns the authentication methods used by it, sorted 
 * by priority.
 *
 * @param  string   $institution     Name of the institution
 * @return array                     Array of auth instance records
 */
function auth_get_auth_instances_for_institution($institution=null) {
    static $cache = array();

    if (null == $institution) {
        return array();
    }

    if (!isset($cache[$institution])) {
        // Get auth instances in order of priority
        // DO NOT CHANGE THE SORT ORDER OF THIS RESULT SET
        // YEAH EINSTEIN - THAT MEANS YOU!!!

        // TODO: work out why this won't accept a placeholder - had to use db_quote
        $sql ='
            SELECT DISTINCT
                i.id,
                i.instancename,
                i.priority,
                i.authname,
                a.requires_config,
                a.requires_parent
            FROM 
                {auth_instance} i,
                {auth_installed} a
            WHERE 
                a.name = i.authname AND
                i.institution = '. db_quote($institution).'
            ORDER BY
                i.priority,
                i.instancename';

        $cache[$institution] = get_records_sql_array($sql, array());

        if (empty($cache[$institution])) {
            return false;
        }
    }

    return $cache[$institution];
}

/**
 * Given a wwwroot, find any auth instances that can come from that host
 * 
 * @param   string  wwwroot of the host that is connecting to us
 * @return  array   array of record objects
 */
function auth_get_auth_instances_for_wwwroot($wwwroot) {

    // TODO: we just need ai.id and ai.authname... rewrite query, or
    // just drop this function
    $query = "  SELECT
                    ai.*,
                    aic.*,
                    i.*
                FROM
                    {auth_instance} ai, 
                    {auth_instance_config} aic,
                    {institution} i
                WHERE
                    aic.field = 'wwwroot' AND
                    aic.value = ? AND
                    aic.instance = ai.id AND
                    i.name = ai.institution";

    return get_records_sql_array($query, array('value' => $wwwroot));
}

/**
 * Given an institution, get all the auth types EXCEPT those that are already 
 * enabled AND do not require configuration.
 *
 * @param  string   $institution     Name of the institution
 * @return array                     Array of auth instance records
 */
function auth_get_available_auth_types($institution=null) {

    if (!is_null($institution) && (!is_string($institution) || strlen($institution) > 255)) {
        return array();
    }

    // TODO: work out why this won't accept a placeholder - had to use db_quote
    $sql ='
        SELECT DISTINCT
            a.name,
            a.requires_config
        FROM 
            {auth_installed} a
        LEFT JOIN 
            {auth_instance} i
        ON 
            a.name = i.authname AND
            i.institution = '. db_quote($institution).'
        WHERE
           (a.requires_config = 1 OR
            i.id IS NULL) AND
            a.active = 1
        ORDER BY
            a.name';          

    if (is_null($institution)) {
        $result = get_records_array('auth_installed', '','','name','name, requires_config');
    } else {
        $result = get_records_sql_array($sql, array());
    }

    if (empty($result)) {
        return array();
    }

    foreach ($result as &$row) {
        $row->title       = get_string('title', 'auth.' . $row->name);
        safe_require('auth', $row->name);
        if ($row->is_usable = call_static_method('PluginAuth' . $row->name, 'is_usable')) {
            $row->description = get_string('description', 'auth.' . $row->name);
        }
        else {
            $row->description = get_string('notusable', 'auth.' . $row->name);
        }
    }
    usort($result, create_function('$a, $b', 'if ($a->is_usable != $b->is_usable) return $b->is_usable; return strnatcasecmp($a->title, $b->title);'));

    return $result;
}
/**
 * Checks that all the required fields are set, and handles setting them if required.
 *
 * Checks whether the current user needs to change their password, and handles
 * the password changing if it's required.
 */
function auth_check_required_fields() {
    global $USER, $SESSION;

    if (defined('NOCHECKREQUIREDFIELDS')) {
        return;
    }

    $changepassword = true;
    $elements = array();

    if (
        !$USER->get('passwordchange')                                // User doesn't need to change their password
        || ($USER->get('parentuser') && $USER->get('loginanyway'))   // User is masquerading and wants to log in anyway
        || defined('NOCHECKPASSWORDCHANGE')                          // The page wants to skip this hassle
        ) {
        $changepassword = false;
    }

    // Check if the user wants to log in anyway
    if ($USER->get('passwordchange') && $USER->get('parentuser') && isset($_GET['loginanyway'])) {
        $USER->loginanyway = true;
        $changepassword = false;
    }

    if ($changepassword) {
        $authobj = AuthFactory::create($USER->authinstance);

        if ($authobj->changepasswordurl) {
            redirect($authobj->changepasswordurl);
            exit;
        }

        if (method_exists($authobj, 'change_password')) {

            if ($SESSION->get('resetusername')) {
                $elements['username'] = array(
                    'type' => 'text',
                    'defaultvalue' => $USER->get('username'),
                    'title' => get_string('changeusername', 'account'),
                    'description' => get_string('changeusernamedesc', 'account', hsc(get_config('sitename'))),
                );
            }

            $elements['password1'] = array(
                'type'        => 'password',
                'title'       => get_string('newpassword') . ':',
                'description' => get_string('yournewpassword'),
                'rules'       => array(
                    'required' => true
                )
            );

            $elements['password2'] = array(
                'type'        => 'password',
                'title'       => get_string('confirmpassword') . ':',
                'description' => get_string('yournewpasswordagain'),
                'rules'       => array(
                    'required' => true,
                ),
            );

            $elements['email'] = array(
                'type'   => 'text',
                'title'  => get_string('principalemailaddress', 'artefact.internal'),
                'ignore' => (trim($USER->get('email')) != '' && !preg_match('/@example\.org$/', $USER->get('email'))),
                'rules'  => array(
                    'required' => true,
                    'email'    => true,
                ),
            );
        }
    }
    else if (defined('JSON')) {
        // Don't need to check this for json requests
        return;
    }

    safe_require('artefact', 'internal');
    require_once('pieforms/pieform.php');

    $alwaysmandatoryfields = array_keys(ArtefactTypeProfile::get_always_mandatory_fields());
    foreach(ArtefactTypeProfile::get_mandatory_fields() as $field => $type) {
        // Always mandatory fields are stored in the usr table, so are part of 
        // the user session object. We can save a query by grabbing them from 
        // the session.
        if (in_array($field, $alwaysmandatoryfields) && $USER->get($field) != null) {
            continue;
        }
        // Not cached? Get value the standard way.
        if (get_profile_field($USER->get('id'), $field) != null) {
            continue;
        }

        if ($field == 'email') {
            if (isset($elements['email'])) {
                continue;
            }
            // Use a text field for their first e-mail address, not the
            // emaillist element
            $type = 'text';
        }

        $elements[$field] = array(
            'type'  => $type,
            'title' => get_string($field, 'artefact.internal'),
            'rules' => array('required' => true)
        );

        // @todo ruthlessly stolen from artefact/internal/index.php, could be merged
        if ($type == 'wysiwyg') {
            $elements[$field]['rows'] = 10;
            $elements[$field]['cols'] = 60;
        }
        if ($type == 'textarea') {
            $elements[$field]['rows'] = 4;
            $elements[$field]['cols'] = 60;
        }
        if ($field == 'country') {
            $elements[$field]['options'] = getoptions_country();
            $elements[$field]['defaultvalue'] = get_config('country');
        }

        if ($field == 'email') {
            $elements[$field]['rules']['email'] = true;
        }
    }

    if (empty($elements)) { // No mandatory fields that aren't set
        return;
    }

    $elements['submit'] = array(
        'type' => 'submit',
        'value' => get_string('submit')
    );

    $form = pieform(array(
        'name'     => 'requiredfields',
        'method'   => 'post',
        'action'   => '',
        'elements' => $elements
    ));

    $smarty = smarty();
    if ($USER->get('parentuser')) {
        $smarty->assign('loginasoverridepasswordchange',
            get_string('loginasoverridepasswordchange', 'admin',
                       '<a href="' . get_config('wwwroot') . '?loginanyway">', '</a>'));
    }
    $smarty->assign('changepassword', $changepassword);
    $smarty->assign('changeusername', $SESSION->get('resetusername'));
    $smarty->assign('form', $form);
    $smarty->display('requiredfields.tpl');
    exit;
}

function requiredfields_validate(Pieform $form, $values) {
    global $USER;
    if (!isset($values['password1'])) {
        return true;
    }

    // Get the authentication type for the user, and
    // use the information to validate the password
    $authobj = AuthFactory::create($USER->authinstance);

    // @todo this could be done by a custom form rule... 'password' => $user
    password_validate($form, $values, $USER);

    // The password cannot be the same as the old one
    try {
        if (!$form->get_error('password1')
            && $authobj->authenticate_user_account($USER, $values['password1'])) {
            $form->set_error('password1', get_string('passwordnotchanged'));
        }
    }
    // propagate error up as the collective error AuthUnknownUserException
     catch  (AuthInstanceException $e) {
        $form->set_error('password1', $e->getMessage());
    }


    if ($authobj->authname == 'internal' && isset($values['username']) && $values['username'] != $USER->get('username')) {
        if (!AuthInternal::is_username_valid($values['username'])) {
            $form->set_error('username', get_string('usernameinvalidform', 'auth.internal'));
        }
        if (!$form->get_error('username') && record_exists_select('usr', 'LOWER(username) = ?', strtolower($values['username']))) {
            $form->set_error('username', get_string('usernamealreadytaken', 'auth.internal'));
        }
    }
}

function requiredfields_submit(Pieform $form, $values) {
    global $USER, $SESSION;

    if (isset($values['password1'])) {
        $authobj = AuthFactory::create($USER->authinstance);

        // This method should exist, because if it did not then the change
        // password form would not have been shown.
        if ($password = $authobj->change_password($USER, $values['password1'])) {
            $SESSION->add_ok_msg(get_string('passwordsaved'));
        }
        else {
            throw new SystemException('Attempt by "' . $USER->get('username') . '@'
                . $USER->get('institution') . 'to change their password failed');
        }
    }

    if (isset($values['username'])) {
        $SESSION->set('resetusername', false);
        if ($values['username'] != $USER->get('username')) {
            $USER->username = $values['username'];
            $USER->commit();
            $otherfield = true;
        }
    }

    foreach ($values as $field => $value) {
        if (in_array($field, array('submit', 'sesskey', 'password1', 'password2', 'username'))) {
            continue;
        }
        if ($field == 'email') {
            $USER->email = $values['email'];
            $USER->commit();
        }
        set_profile_field($USER->get('id'), $field, $value);
        $otherfield = true;
    }

    if (isset($otherfield)) {
        $SESSION->add_ok_msg(get_string('requiredfieldsset', 'auth'));
    }

    redirect();
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

    $externallogin = get_config('externallogin');
    if ($externallogin) {
        $externallogin = preg_replace('/{shorturlencoded}/', urlencode(get_relative_script_path()), $externallogin);
        $externallogin = preg_replace('/{wwwroot}/', get_config('wwwroot'), $externallogin);
        redirect($externallogin);
    }

    if ($message) {
        $SESSION->add_info_msg($message);
    }
    $smarty = smarty(array(), array(), array(), array('pagehelp' => false, 'sidebars' => false));
    $smarty->assign('login_form', $loginform);
    $smarty->assign('PAGEHEADING', get_string('loginto', 'mahara', get_config('sitename')));
    $smarty->assign('LOGINPAGE', true);
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
    $elements = auth_get_login_form_elements();

    $elements['login']['elements']['login_submitted'] = array(
        'type'  => 'hidden',
        'value' => 1
    );

    // Change login redirection for clean urls
    $url = get_relative_script_path();
    $getstart = strrpos($url, '?');
    if ($getstart !== false) {
        $getpart = substr($url, $getstart + 1);
        $url = substr($url, 0, $getstart);
    }
    if (!file_exists(get_config('docroot') . $url)) {
        // clean url, treat get string differently
        $get = array();
        if (isset($getpart)) {
            $getarr = split('&', $getpart);
            if ($getarr) {
                foreach ($getarr as $data) {
                    $arr = split('=', $data);
                    $get[$arr[0]] = isset($arr[1]) ? $arr[1] : null;
                }
            }
        }
    }
    else {
        $get = $_GET;
    }
    // The login page is completely transient, and it is smart because it
    // remembers the GET and POST data sent to it and resends that on
    // afterwards.
    $action = '';
    if ($get) {
        if (isset($get['logout'])) {
            // You can log the user out on any particular page by appending
            // ?logout to the URL. In this case, we don't want the "action"
            // of the url to include that, or be blank, else the next time
            // the user logs in they will be logged out again.
            $action = hsc(substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')));
        }
        else {
            $action .= '?';
            foreach ($get as $key => $value) {
                if ($key != 'login') {
                    $action .= hsc($key) . '=' . hsc($value) . '&';
                }
            }
            $action = substr($action, 0, -1);
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
        'renderer'       => 'div',
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
 * Returns the definition of the login form elements.
 *
 * @return array   The login form elements array.
 * @access private
 */
function auth_get_login_form_elements() {
    // See if user can register
    if (count_records('institution', 'registerallowed', 1, 'suspended', 0)) {
        $registerlink = '<a href="' . get_config('wwwroot') . 'register.php" tabindex="2">' . get_string('register') . '</a><br>';
    }
    else {
        $registerlink = '';
    }

    $elements = array(
        'login_username' => array(
            'type'        => 'text',
            'title'       => get_string('username') . ':',
            'description' => get_string('usernamedescription'),
            'defaultvalue' => (isset($_POST['login_username'])) ? $_POST['login_username'] : '',
            'rules' => array(
                'required'    => true
            )
        ),
        'login_password' => array(
            'type'        => 'password',
            'title'       => get_string('password') . ':',
            'description' => get_string('passworddescription'),
            'defaultvalue'       => '',
            'rules' => array(
                'required'    => true
            )
        ),
        'submit' => array(
            'type'  => 'submit',
            'value' => get_string('login')
        ),
        'register' => array(
            'type' => 'markup',
            'value' => '<div id="login-helplinks">' . $registerlink
                . '<a href="' . get_config('wwwroot') . 'forgotpass.php" tabindex="2">' . get_string('lostusernamepassword') . '</a></div>'
        ),
    );
    $elements = array(
        'login' => array(
            'type' => 'container',
            'class' => 'login',
            'elements' => $elements
        )
    );


    // Get any extra elements from the enabled auth plugins
    $extraelements = array();
    $showbasicform = false;
    $authplugins = auth_get_enabled_auth_plugins();
    foreach ($authplugins as $plugin) {
        $classname = 'PluginAuth' . ucfirst(strtolower($plugin));
        $pluginelements = call_static_method($classname, 'login_form_elements');
        if (!empty($pluginelements)) {
            $extraelements = array_merge($extraelements, $pluginelements);
        }
        if (call_static_method($classname, 'need_basic_login_form')) {
            $showbasicform = true;
        }
    }
    if (!empty($extraelements) && $showbasicform) {
        $loginlabel = array(
            'type' => 'markup',
            'value' => '<label>'.get_string('orloginvia') . '</label>'
        );
        $extraelements = array_merge(array('label' => $loginlabel), $extraelements);
        $keys = array_keys($extraelements);
        if (!empty($keys)) {
            $key = $keys[count($keys) - 1];
            $extraelements[$key]['value'] .= '<div class="cb"></div>';
        }
    }
    if (count($extraelements)) {
        $extraelements = array(
            'login_extra' => array(
                'type' => 'container',
                'class' => 'login_extra',
                'elements' => $extraelements
            )
        );
    }
    // Replace or supplement the standard login form elements
    if ($showbasicform) {
        $elements = array_merge($elements, $extraelements);
    }
    else {
        $elements = $extraelements;
    }
    return $elements;
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
    $form = json_encode($form);
    $strcookiesnotenabled    = json_encode(get_string('cookiesnotenabled'));
    $cookiename = get_config('cookieprefix') . 'ctest';
    $js = <<< EOF
<script type="text/javascript">
var loginbox = $('loginform_container');
document.cookie = "$cookiename=1";
if (document.cookie) {
    loginbox.innerHTML = $form;
    document.cookie = '$cookiename=1;expires=1/1/1990 00:00:00';
}
else {
    replaceChildNodes(loginbox, P(null, $strcookiesnotenabled));
}
</script>
EOF;

    $authplugins = auth_get_enabled_auth_plugins();
    foreach ($authplugins as $plugin) {
        $classname = 'PluginAuth' . ucfirst(strtolower($plugin));
        $pluginjs = call_static_method($classname, 'login_form_js');
        if (!empty($pluginjs)) {
            $js .= $pluginjs;
        }
    }

    return $js;
}

/**
 * Return a list of all enabled and usable auth plugins.
 */
function auth_get_enabled_auth_plugins() {

    static $cached_plugins = null;
    if ($cached_plugins !== null) {
        return $cached_plugins;
    }

    $sql = 'SELECT
                DISTINCT(authname)
            FROM
                {auth_instance} ai
            JOIN
                {institution} i ON ai.institution = i.name
            JOIN
                {auth_installed} inst ON inst.name = ai.authname
            WHERE
                i.suspended = 0 AND
                inst.active = 1
            ORDER BY authname';
    $authplugins = get_column_sql($sql);

    $usableplugins = array();
    foreach ($authplugins as $plugin) {
        safe_require('auth', strtolower($plugin));
        $classname = 'PluginAuth' . ucfirst(strtolower($plugin));
        if (call_static_method($classname, 'is_usable')) {
            $usableplugins[] = $plugin;
        }
    }

    $cached_plugins = $usableplugins;
    return $cached_plugins;
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
        if (is_object($id) && isset($id->id)) {
            $id = $id->id;
        }

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

    $username      = trim($values['login_username']);
    $password      = $values['login_password'];
    $authenticated = false;

    try {
        $authenticated = $USER->login($username, $password);

        if (empty($authenticated)) {
            $SESSION->add_error_msg(get_string('loginfailed'));
            return;
        }

    }
    catch (AuthUnknownUserException $e) {
        // If the user doesn't exist, check for institutions that
        // want to create users automatically.
        try {
            // Reset the LiveUser object, since we are attempting to create a 
            // new user
            $SESSION->destroy_session();
            $USER = new LiveUser();

            $authinstances = get_records_sql_array("
                SELECT a.id, a.instancename, a.priority, a.authname, a.institution, i.suspended, i.displayname
                FROM {institution} i JOIN {auth_instance} a ON a.institution = i.name
                WHERE a.authname != 'internal'
                ORDER BY a.institution, a.priority, a.instancename", null);

            if ($authinstances == false) {
                throw new AuthUnknownUserException("\"$username\" is not known");
            }

            $USER->username = $username;

            reset($authinstances);
            while ((list(, $authinstance) = each($authinstances)) && false == $authenticated) {
                $auth = AuthFactory::create($authinstance->id);
                if (!$auth->can_auto_create_users()) {
                    continue;
                }
                // catch semi-fatal auth errors, but allow next auth instance to be
                // tried
                try {
                    if ($auth->authenticate_user_account($USER, $password)) {
                        $authenticated = true;
                    }
                    else {
                        continue;
                    }
                } catch  (AuthInstanceException $e) {
                    continue;
                }

                // Check now to see if the institution has its maximum quota of users
                require_once('institution.php');
                $institution = new Institution($authinstance->institution);
                if ($institution->isFull()) {
                    throw new AuthUnknownUserException('Institution has too many users');
                }

                $USER->authinstance = $authinstance->id;
                $userdata = $auth->get_user_info($username);

                if (empty($userdata)) {
                    throw new AuthUnknownUserException("\"$username\" is not known");
                }

                // Check for a suspended institution
                if ($authinstance->suspended) {
                    $sitename = get_config('sitename');
                    throw new AccessTotallyDeniedException(get_string('accesstotallydenied_institutionsuspended', 'mahara', $authinstance->displayname, $sitename));
                }

                // We have the data - create the user
                $USER->lastlogin = db_format_timestamp(time());
                if (isset($userdata->firstname)) {
                    $USER->firstname = sanitize_firstname($userdata->firstname);
                }
                if (isset($userdata->lastname)) {
                    $USER->lastname = sanitize_firstname($userdata->lastname);
                }
                if (isset($userdata->email)) {
                    $USER->email = sanitize_email($userdata->email);
                }
                else {
                    // The user will be asked to populate this when they log in.
                    $USER->email = null;
                }

                $profilefields = array();
                foreach (array('studentid', 'preferredname') as $pf) {
                    if (isset($userdata->$pf)) {
                        $sanitize = 'sanitize_' . $pf;
                        if (($USER->$pf = $sanitize($userdata->$pf)) !== '') {
                            $profilefields[$pf] = $USER->$pf;
                        }
                    }
                }

                try {
                    // If this authinstance is a parent auth for some xmlrpc authinstance, pass it along to create_user
                    // so that this username also gets recorded as the username for sso from the remote sites.
                    $remoteauth = count_records('auth_instance_config', 'field', 'parent', 'value', $authinstance->id) ? $authinstance : null;
                    create_user($USER, $profilefields, $institution, $remoteauth);
                    $USER->reanimate($USER->id, $authinstance->id);
                }
                catch (Exception $e) {
                    db_rollback();
                    throw $e;
                }
            }

            if (!$authenticated) {
                $SESSION->add_error_msg(get_string('loginfailed'));
                return;
            }

        }
        catch (AuthUnknownUserException $e) {
            // We weren't able to authenticate the user for some reason that 
            // probably isn't their fault (e.g. ldap extension not available 
            // when using ldap authentication)
            log_info($e->getMessage());
            $SESSION->add_error_msg(get_string('loginfailed'));
            return;
        }
    }

    auth_check_admin_section();
    ensure_user_account_is_active();

    // User is allowed to log in
    //$USER->login($userdata);
    auth_check_required_fields();
}

/**
 * Redirect to the home page if the user is trying to access the admin
 * area without permission
 */
function auth_check_admin_section() {
    global $USER, $SESSION;

    if (defined('ADMIN')) {
        $allowed = $USER->get('admin');
    }
    else if (defined('STAFF')) {
        $allowed = $USER->get('admin') || $USER->get('staff');
    }
    else if (defined('INSTITUTIONALADMIN')) {
        $allowed = $USER->get('admin') || $USER->is_institutional_admin();
    }
    else if (defined('INSTITUTIONALSTAFF')) {
        $allowed = $USER->get('admin') || $USER->get('staff') || $USER->is_institutional_admin() || $USER->is_institutional_staff();
    }
    else {
        return;
    }

    if (!$allowed) {
        $SESSION->add_error_msg(get_string('accessforbiddentoadminsection'));
        redirect();
    }
}

/**
 * Die and log the user out if their account is not active.
 *
 * @param $user The user object to check or null for the currently logged in user.
 */
function ensure_user_account_is_active($user=null) {

    $dologout = false;
    if (!$user) {
        global $USER;
        $user = $USER;
        $dologout = true;
    }

    // Check if the user's account has been deleted
    if ($user->deleted) {
        if ($dologout) {
            $user->logout();
        }
        die_info(get_string('accountdeleted'));
    }

    // Check if the user's account has expired
    if ($user->expiry > 0 && time() > $user->expiry) {
        if ($dologout) {
            $user->logout();
        }
        die_info(get_string('accountexpired'));
    }

    // Check if the user's account has been suspended
    if ($user->suspendedcusr) {
        $suspendedctime  = strftime(get_string('strftimedaydate'), $user->suspendedctime);
        $suspendedreason = $user->suspendedreason;
        if ($dologout) {
            $user->logout();
        }
        die_info(get_string('accountsuspended', 'mahara', $suspendedctime, $suspendedreason));
    }
}

/**
 * Removes registration requests that were not completed in the allowed amount of time
 */
function auth_clean_partial_registrations() {
    delete_records_sql('DELETE FROM {usr_registration}
        WHERE expiry < ?', array(db_format_timestamp(time())));
}


function _email_or_notify($user, $subject, $bodytext, $bodyhtml) {
    try {
        email_user($user, null, $subject, $bodytext, $bodyhtml);
    }
    catch (EmailException $e) {
        // Send a notification instead - email is invalid or disabled for this user
        $message = new StdClass;
        $message->users = array($user->id);
        $message->subject = $subject;
        $message->message = $bodytext;

        require_once('activity.php');
        activity_occurred('maharamessage', $message);
    }
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
    $sitename = get_config('sitename');
    $wwwroot  = get_config('wwwroot');
    $expire   = get_config('defaultaccountinactiveexpire');
    $warn     = get_config('defaultaccountinactivewarn');

    $daystoexpire = ceil($warn / 86400) . ' ';
    $daystoexpire .= ($daystoexpire == 1) ? get_string('day') : get_string('days');

    // Expiry warning messages
    if ($users = get_records_sql_array('SELECT u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.admin, u.staff
        FROM {usr} u
        WHERE ' . db_format_tsfield('u.expiry', false) . ' < ?
        AND expirymailsent = 0 AND deleted = 0', array(time() + $warn))) {
        foreach ($users as $user) {
            $displayname  = display_name($user);
            _email_or_notify($user, get_string('accountexpirywarning'),
                get_string('accountexpirywarningtext', 'mahara', $displayname, $sitename, $daystoexpire, $wwwroot . 'contact.php', $sitename),
                get_string('accountexpirywarninghtml', 'mahara', $displayname, $sitename, $daystoexpire, $wwwroot . 'contact.php', $sitename)
            );
            set_field('usr', 'expirymailsent', 1, 'id', $user->id);
        }
    }

    // Actual expired users
    if ($users = get_records_sql_array('SELECT id
        FROM {usr}
        WHERE ' . db_format_tsfield('expiry', false) . ' < ?', array(time()))) {
        // Users have expired!
        foreach ($users as $user) {
            expire_user($user->id);
        }
    }

    
    if ($expire) {
        // Inactivity (lastlogin is too old)

        // MySQL doesn't want to compare intervals, so when editing the where clauses below, make sure
        // the intervals are always added to datetimes first.
        $dbexpire = db_interval($expire);
        $dbwarn = db_interval($warn);

        $installationtime = get_config('installation_time');
        $lastactive = "COALESCE(u.lastaccess, u.lastlogin, u.ctime, ?)";

        // Actual inactive users
        if ($users = get_records_sql_array("
            SELECT u.id
            FROM {usr} u
            WHERE $lastactive + $dbexpire < current_timestamp
                AND (u.expiry IS NULL OR u.expiry > current_timestamp) AND id > 0", array($installationtime))) {
            // Users have become inactive!
            foreach ($users as $user) {
                deactivate_user($user->id);
            }
        }

        // Inactivity warning emails
        if ($users = get_records_sql_array("
            SELECT u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.admin, u.staff
            FROM {usr} u
            WHERE $lastactive + $dbexpire < current_timestamp + $dbwarn
                AND (u.expiry IS NULL OR u.expiry > current_timestamp)
                AND inactivemailsent = 0 AND deleted = 0 AND id > 0",  array($installationtime))) {
            foreach ($users as $user) {
                $displayname = display_name($user);
                _email_or_notify($user, get_string('accountinactivewarning'),
                    get_string('accountinactivewarningtext', 'mahara', $displayname, $sitename, $daystoexpire, $sitename),
                    get_string('accountinactivewarninghtml', 'mahara', $displayname, $sitename, $daystoexpire, $sitename)
                );
                set_field('usr', 'inactivemailsent', 1, 'id', $user->id);
            }
        }
    }

    // Institution membership expiry
    delete_records_sql('DELETE FROM {usr_institution} 
        WHERE ' . db_format_tsfield('expiry', false) . ' < ? AND expirymailsent = 1', array(time()));

    // Institution membership expiry warnings
    if ($users = get_records_sql_array('
        SELECT
            u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.admin, u.staff,
            ui.institution, ui.expiry, i.displayname as institutionname
        FROM {usr} u
        INNER JOIN {usr_institution} ui ON u.id = ui.usr
        INNER JOIN {institution} i ON ui.institution = i.name
        WHERE ' . db_format_tsfield('ui.expiry', false) . ' < ?
        AND ui.expirymailsent = 0 AND u.deleted = 0', array(time() + $warn))) {
        foreach ($users as $user) {
            $displayname  = display_name($user);
            _email_or_notify($user, get_string('institutionmembershipexpirywarning'),
                get_string('institutionmembershipexpirywarningtext', 'mahara', $displayname, $user->institutionname,
                    $sitename, $daystoexpire, $wwwroot . 'contact.php', $sitename),
                get_string('institutionmembershipexpirywarninghtml', 'mahara', $displayname, $user->institutionname,
                    $sitename, $daystoexpire, $wwwroot . 'contact.php', $sitename)
            );
            set_field('usr_institution', 'expirymailsent', 1, 'usr', $user->id,
                      'institution', $user->institution);
        }
    }

}

/**
 * Sends notification e-mails to site and institutional admins when:
 *
 *  - An institution is expiring within the institution expiry warning
 *    period, set in site options.
 *
 * The actual prevention of users logging in is handled by the authentication
 * code. This cron job sends e-mails to notify users that these events will
 * happen soon.
 */
function auth_handle_institution_expiries() {
    // The 'expiry' flag on the usr table
    $sitename = get_config('sitename');
    $wwwroot  = get_config('wwwroot');
    $expire   = get_config('institutionautosuspend');
    $warn     = get_config('institutionexpirynotification');

    $daystoexpire = ceil($warn / 86400) . ' ';
    $daystoexpire .= ($daystoexpire == 1) ? get_string('day') : get_string('days');

    // Get site administrators
    $siteadmins = get_records_sql_array('SELECT u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.admin, u.staff FROM {usr} u WHERE u.admin = 1', array());

    // Expiry warning messages
    if ($institutions = get_records_sql_array(
      'SELECT i.name, i.displayname FROM {institution} i ' .
      'WHERE ' . db_format_tsfield('i.expiry', false) . ' < ? AND suspended != 1 AND expirymailsent != 1',
      array(time() + $warn))) {
        foreach ($institutions as $institution) {
            $institution_displayname = $institution->displayname;
            // Email site administrators
            foreach ($siteadmins as $user) {
                $user_displayname  = display_name($user);
                _email_or_notify($user, get_string('institutionexpirywarning'),
                    get_string('institutionexpirywarningtext_site', 'mahara', $user_displayname, $institution_displayname, $daystoexpire, $sitename, $sitename),
                    get_string('institutionexpirywarninghtml_site', 'mahara', $user_displayname, $institution_displayname, $daystoexpire, $sitename, $sitename)
                );
            }

            // Email institutional administrators
            $institutionaladmins = get_records_sql_array(
              'SELECT u.id, u.username, u.expiry, u.staff, u.admin AS siteadmin, ui.admin AS institutionadmin, u.firstname, u.lastname, u.email ' .
              'FROM {usr_institution} ui JOIN {usr} u ON (ui.usr = u.id) WHERE ui.admin = 1', array()
            );
            foreach ($institutionaladmins as $user) {
                $user_displayname  = display_name($user);
                _email_or_notify($user, get_string('institutionexpirywarning'),
                    get_string('institutionexpirywarningtext_institution', 'mahara', $user_displayname, $institution_displayname, $sitename, $daystoexpire, $wwwroot . 'contact.php', $sitename),
                    get_string('institutionexpirywarninghtml_institution', 'mahara', $user_displayname, $institution_displayname, $sitename, $daystoexpire, $wwwroot . 'contact.php', $sitename)
                );
            }
            set_field('institution', 'expirymailsent', 1, 'name', $institution->name);
        }
    }

    // If we can automatically suspend expired institutions
    $autosuspend = get_config('institutionautosuspend');
    if ($autosuspend) {
        // Actual expired institutions
        if ($institutions = get_records_sql_array(
          'SELECT name FROM {institution} ' .
          'WHERE ' . db_format_tsfield('expiry', false) . ' < ?', array(time()))) {
            // Institutions have expired!
            foreach ($institutions as $institution) {
                set_field('institution', 'suspended', 1, 'name', $institution->name);
            }
        }
    }
}

/**
 * Clears out old session files
 *
 * This should be run once every now and then (once a day is good), to clean 
 * out session files of users whose sessions have timed out.
 */
function auth_remove_old_session_files() {
    $basedir = get_config('dataroot') . 'sessions/';

    // delete sessions older than the session timeout plus 2 days
    $mintime = time() - get_config('session_timeout') - 2 * 24 * 60 * 60;

    // Session files are stored in a three tier md5sum layout
    // The actual files are stored in the third directory
    // This loops through all three directories, then checks the files for age

    $iter1 = new DirectoryIterator($basedir);
    foreach ($iter1 as $dir1) {
        if ($dir1->isDot()) continue;
        $dir1path = $dir1->getPath() . '/' . $dir1->getFilename();
        $iter2 = new DirectoryIterator($dir1path);
        foreach ($iter2 as $dir2) {
            if ($dir2->isDot()) continue;
            $dir2path = $dir2->getPath() . '/' . $dir2->getFilename();
            $iter3 = new DirectoryIterator($dir2path);
            foreach ($iter3 as $dir3) {
                if ($dir3->isDot()) continue;
                $dir3path = $dir3->getPath() . '/' . $dir3->getFilename();
                $fileiter = new DirectoryIterator($dir3path);
                foreach ($fileiter as $file) {
                    if ($file->isFile() && $file->getCTime() < $mintime) {
                        unlink($file->getPath() . '/' . $file->getFilename());
                    }
                }
            }
        }
    }
    // Throw away records of old login sessions. Should check whether any are still alive.
    delete_records_select('usr_session', 'ctime < ?', array(db_format_timestamp(time() - 86400 * 30)));
}

/**
 * Generates the login form for the sideblock
 *
 * {@internal{Not sure why this form definition doesn't use 
 * auth_get_login_form, but keep that in mind when making changes.}}
 */
function auth_generate_login_form() {
    require_once('pieforms/pieform.php');
    if (!get_config('installed')) {
        return;
    }
    $elements = auth_get_login_form_elements();
    $loginform = get_login_form_js(pieform(array(
        'name'       => 'login',
        'renderer'   => 'div',
        'submit'     => false,
        'plugintype' => 'auth',
        'pluginname' => 'internal',
        'autofocus'  => false,
        'elements'   => $elements,
    )));

    return $loginform;
}

/**
 * Given a form, an array of values with 'password1' and 'password2'
 * indices and a user, validate that the user can change their password to
 * the one in $values.
 *
 * This provides one place where validation of passwords can be done. This is
 * used by:
 *  - registration
 *  - user forgot password
 *  - user changing password on their account page
 *  - user forced to change their password by the <kbd>passwordchange</kbd>
 *    flag on the <kbd>usr</kbd> table.
 *
 * The password is checked for:
 *  - Being in valid form according to the rules of the authentication method
 *    for the user
 *  - Not being an easy password (a blacklist of strings, NOT a length check or
 *     similar), including being the user's username
 *  - Both values being equal
 *
 * @param Pieform $form         The form to validate
 * @param array $values         The values passed through
 * @param string $authplugin    The authentication plugin that the user uses
 */
function password_validate(Pieform $form, $values, $user) {

    $authobj = AuthFactory::create($user->authinstance);

    if (!$form->get_error('password1') && !$authobj->is_password_valid($values['password1'])) {
        $form->set_error('password1', get_string('passwordinvalidform', "auth.$authobj->type"));
    }

    $suckypasswords = array(
        'mahara', 'password', $user->username, 'abc123'
    );

    if (!$form->get_error('password1') && in_array($values['password1'], $suckypasswords)) {
        $form->set_error('password1', get_string('passwordtooeasy'));
    }

    if (!$form->get_error('password1') && $values['password1'] != $values['password2']) {
        $form->set_error('password2', get_string('passwordsdonotmatch'));
    }

}

function auth_get_random_salt() {
    return substr(md5(rand(1000000, 9999999)), 2, 8);
}

// Add salt and encrypt the pw for a user, if their auth instance allows for it
function reset_password($user, $resetpasswordchange=true, $quickhash=false) {
    $userobj = new User();
    $userobj->find_by_id($user->id);
    $authobj = AuthFactory::create($user->authinstance);
    if (isset($user->password) && $user->password != '' && method_exists($authobj, 'change_password')) {
        $authobj->change_password($userobj, $user->password, $resetpasswordchange, $quickhash);
    }
    else {
        $userobj->password = '';
        $userobj->salt = auth_get_random_salt();
        $userobj->commit();
    }
}

function user_login_tries_to_zero() {
    execute_sql('UPDATE {usr} SET logintries = 0 WHERE logintries > 0');
}

function auth_generate_registration_form($formname, $authname='internal', $goto) {
    require_once(get_config('libroot').'antispam.php');
    $elements = array(
        'firstname' => array(
            'type' => 'text',
            'title' => get_string('firstname'),
            'rules' => array(
                'required' => true
            )
        ),
        'lastname' => array(
            'type' => 'text',
            'title' => get_string('lastname'),
            'rules' => array(
                'required' => true
            )
        ),
        'email' => array(
            'type' => 'text',
            'title' => get_string('emailaddress'),
            'rules' => array(
                'required' => true,
                'email' => true
            )
        )
    );

    $sql = 'SELECT
                i.*
            FROM
                {institution} i,
                {auth_instance} ai
            WHERE
                ai.authname = ? AND
                ai.institution = i.name AND
                i.registerallowed = 1';
    $institutions = get_records_sql_array($sql, array($authname));
    $registerconfirm = array();
    $reason = false;

    if (count($institutions) > 0) {
        $options = array();
        foreach ($institutions as $institution) {
            $options[$institution->name] = $institution->displayname;
            if ($registerconfirm[$institution->name] = $institution->registerconfirm) {
                if ($authname != 'internal') {
                    $authinstance = get_record('auth_instance', 'institution', $institution->name, 'authname', $authname);
                    $auth = AuthFactory::create($authinstance->id);
                    $registerconfirm[$institution->name] = !$auth->weautocreateusers;
                }
                if ($registerconfirm[$institution->name]) {
                    $options[$institution->name] .= ' (' . get_string('approvalrequired') . ')';
                    $reason = true;
                }
            }
        }
        natcasesort($options);
        if (count($institutions) > 1) {
            array_unshift($options, get_string('chooseinstitution', 'mahara'));
        }
        $elements['institution'] = array(
            'type' => 'select',
            'title' => get_string('institution'),
            'options' => $options,
            'rules' => array(
                'required' => true
            )
        );
    }
    else {
        return;
    }

    if ($reason) {
        $elements['reason'] = array(
            'type' => 'textarea',
            'title' => get_string('registrationreason', 'auth.internal'),
            'description' => get_string('registrationreasondesc1', 'auth.internal'),
            'class' => 'js-hidden',
            'rows' => 4,
            'cols' => 60,
        );
    }

    $registerterms = get_config('registerterms');
    if ($registerterms) {
        $elements['tandc'] = array(
            'type' => 'radio',
            'title' => get_string('iagreetothetermsandconditions', 'auth.internal'),
            'options' => array(
                'yes' => get_string('yes'),
                'no'  => get_string('no')
            ),
            'defaultvalue' => 'no',
            'rules' => array(
                'required' => true
            ),
            'separator' => ' &nbsp; '
        );
    }

    $elements['submit'] = array(
        'type' => 'submit',
        'value' => get_string('register'),
    );

    $elements['goto'] = array(
        'type' => 'hidden',
        'value' => $goto,
    );

    // swap the name and email fields at random
    if (rand(0,1)) {
        $emailelement = $elements['email'];
        unset($elements['email']);
        $elements = array('email' => $emailelement) + $elements;
    }

    $form = array(
        'name' => $formname,
        'validatecallback' => 'auth_' . $formname . '_validate',
        'successcallback' => 'auth_' . $formname . '_submit',
        'method' => 'post',
        'action' => '',
        'showdescriptiononerror' => false,
        'renderer' => 'table',
        'elements' => $elements,
        'spam' => array(
            'secret'       => get_config('formsecret'),
            'mintime'      => 5,
            'hash'         => array('firstname', 'lastname', 'email', 'institution', 'reason', 'tandc', 'submit'),
        ),
    );

    if ($authname == 'internal') {
        $form['plugintype'] = 'core';
        $form['pluginname'] = 'register';
    }

    return array($form, $registerconfirm);
}

function auth_generate_registration_form_js($aform, $registerconfirm) {

    // The javascript needs to refer to field names, but they are obfuscated in this form,
    // so construct and build the form in separate steps, so we can get the field names.
    $form = new Pieform($aform);
    $institutionid = $form->get_name() . '_' . $form->hashedfields['institution'];
    $reasonid = $form->get_name() . '_' . $form->hashedfields['reason'];
    $formhtml = $form->build();

    if (count($registerconfirm) == 1) {
        $js = '
        $j(function() {
            $j("#' . $reasonid . '_container").removeClass("js-hidden");
            $j("#' . $reasonid . '_container textarea").removeClass("js-hidden");
            $j("#' . $reasonid . '_container").next("tr.textarea").removeClass("js-hidden");
        });
       ';
    }
    else {
        $js = '
        var registerconfirm = ' . json_encode($registerconfirm) . ';
        $j(function() {
            $j("#' . $institutionid . '").change(function() {
                if (this.value && registerconfirm[this.value] == 1) {
                    $j("#' . $reasonid . '_container").removeClass("js-hidden");
                    $j("#' . $reasonid . '_container textarea").removeClass("js-hidden");
                    $j("#' . $reasonid . '_container").next("tr.textarea").removeClass("js-hidden");
                }
                else {
                    $j("#' . $reasonid . '_container").addClass("js-hidden");
                    $j("#' . $reasonid . '_container textarea").addClass("js-hidden");
                    $j("#' . $reasonid . '_container").next("tr.textarea").addClass("js-hidden");
                }
            });
        });
        ';
    }

    return array($formhtml, $js);
}

/**
 * @todo add note: because the form select thing will eventually enforce
 * that the result for $values['institution'] was in the original lot,
 * and because that only allows authmethods that use 'internal' auth, we
 * can guarantee that the auth method is internal
 */
function auth_register_validate(Pieform $form, $values) {
    global $SESSION;

    $registerterms = get_config('registerterms');

    $spamtrap = new_spam_trap(array(
        array(
            'type' => 'name',
            'value' => $values['firstname'],
        ),
        array(
            'type' => 'name',
            'value' => $values['lastname'],
        ),
        array(
            'type' => 'email',
            'value' => $values['email'],
        ),
    ));

    if ($form->spam_error() || $spamtrap->is_spam()) {
        $msg = get_string('formerror');
        $emailcontact = get_config('emailcontact');
        if (!empty($emailcontact)) {
            $msg .= ' ' . get_string('formerroremail', 'mahara', $emailcontact, $emailcontact);
        }
        $form->set_error(null, $msg);
        return;
    }

    $institution = $values['institution'];
    safe_require('auth', 'internal');

    // First name and last name must contain at least one non whitespace
    // character, so that there's something to read
    if (!$form->get_error('firstname') && !preg_match('/\S/', $values['firstname'])) {
        $form->set_error('firstname', $form->i18n('required'));
    }

    if (!$form->get_error('lastname') && !preg_match('/\S/', $values['lastname'])) {
        $form->set_error('lastname', $form->i18n('required'));
    }

    // The e-mail address cannot already be in the system
    if (!$form->get_error('email')
        && (record_exists('usr', 'email', $values['email'])
        || record_exists('artefact_internal_profile_email', 'email', $values['email']))) {
        $form->set_error('email', get_string('emailalreadytaken', 'auth.internal'));
    }

    // If the user hasn't agreed to the terms and conditions, don't bother
    if ($registerterms && $values['tandc'] != 'yes') {
        $form->set_error('tandc', get_string('youmaynotregisterwithouttandc', 'auth.internal'));
    }

    $institution = get_record_sql('
        SELECT
            i.name, i.maxuseraccounts, i.registerallowed, COUNT(u.id)
        FROM {institution} i
            LEFT OUTER JOIN {usr_institution} ui ON ui.institution = i.name
            LEFT OUTER JOIN {usr} u ON (ui.usr = u.id AND u.deleted = 0)
        WHERE
            i.name = ?
        GROUP BY
            i.name, i.maxuseraccounts, i.registerallowed', array($institution));

    if (!empty($institution->maxuseraccounts) && $institution->count >= $institution->maxuseraccounts) {
        $form->set_error($hashed['institution'], get_string('institutionfull'));
    }

    if (!$institution || !$institution->registerallowed) {
        $form->set_error('institution', get_string('registrationnotallowed'));
    }
}

function auth_register_submit(Pieform $form, $values) {
    global $SESSION;

    safe_require('auth', 'internal');
    $values['key'] = get_random_key();
    $values['lang'] = $SESSION->get('lang');

    // If the institution requires approval, mark the record as pending
    // @todo the expiry date should be configurable
    if ($confirm = get_field('institution', 'registerconfirm', 'name', $values['institution'])) {
        if (isset($values['authtype']) && $values['authtype'] != 'internal') {
            $authinstance = get_record('auth_instance', 'institution', $values['institution'], 'authname', $values['authtype'] ? $values['authtype'] : 'internal');
            $auth = AuthFactory::create($authinstance->id);
            $confirm = !$auth->weautocreateusers;
        }
        if ($confirm) {
            $values['pending'] = 1;
            $values['expiry'] = db_format_timestamp(time() + (86400 * 14)); // now + 2 weeks
        }
        else {
            $values['pending'] = 0;
            $values['expiry'] = db_format_timestamp(time() + 86400);
        }
    }
    else {
        $values['pending'] = 0;
        $values['expiry'] = db_format_timestamp(time() + 86400);
    }

    if (function_exists('local_register_submit')) {
        local_register_submit($values);
    }

    try {
        if (!record_exists('usr_registration', 'email', $values['email'])) {
            insert_record('usr_registration', $values);
        }
        else {
            update_record('usr_registration', $values, array('email' => $values['email']));
        }

        $user =(object) $values;
        $user->admin = 0;
        $user->staff = 0;

        // If the institution requires approval, notify institutional admins.
        if ($confirm) {
            $fullname = sprintf("%s %s", trim($user->firstname), trim($user->lastname));
            $institution = new Institution($values['institution']);
            $pendingregistrationslink = sprintf("%sadmin/users/pendingregistrations.php?institution=%s", get_config('wwwroot'), $values['institution']);

            // list of admins for this institution
            if (count($institution->admins()) > 0) {
                $admins = $institution->admins();
            }
            else {
                // use site admins if the institution doesn't have any
                $admins = get_column('usr', 'id', 'admin', 1, 'deleted', 0);
            }

            // email each admin
            // @TODO Respect the notification preferences of the admins.
            foreach ($admins as $admin) {
                $adminuser = new User();
                $adminuser->find_by_id($admin);
                email_user($adminuser, null,
                    get_string('pendingregistrationadminemailsubject', 'auth.internal', $institution->displayname, get_config('sitename')),
                    get_string('pendingregistrationadminemailtext', 'auth.internal',
                        $adminuser->firstname, $institution->displayname, $pendingregistrationslink,
                        $fullname, $values['email'], $values['reason'], get_config('sitename')),
                    get_string('pendingregistrationadminemailhtml', 'auth.internal',
                        $adminuser->firstname, $institution->displayname, $pendingregistrationslink, $pendingregistrationslink,
                        $fullname, $values['email'], $values['reason'], get_config('sitename'))
                    );
            }
            email_user($user, null,
                get_string('approvalemailsubject', 'auth.internal', get_config('sitename')),
                get_string('approvalemailmessagetext', 'auth.internal', $values['firstname'], get_config('sitename'), get_config('sitename')),
                get_string('approvalemailmessagehtml', 'auth.internal', $values['firstname'], get_config('sitename'), get_config('sitename')));
            $_SESSION['registeredokawaiting'] = true;
        }
        else {
            if (isset($values['authtype']) && $values['authtype'] == 'browserid') {
                redirect('/register.php?key='.$values['key']);
            }
            else {
                email_user($user, null,
                    get_string('registeredemailsubject', 'auth.internal', get_config('sitename')),
                    get_string('registeredemailmessagetext', 'auth.internal', $values['firstname'], get_config('sitename'), get_config('wwwroot'), $values['key'], get_config('sitename')),
                    get_string('registeredemailmessagehtml', 'auth.internal', $values['firstname'], get_config('sitename'), get_config('wwwroot'), $values['key'], get_config('wwwroot'), $values['key'], get_config('sitename')));
            }
            // Add a marker in the session to say that the user has registered
            $_SESSION['registered'] = true;
        }
    }
    catch (EmailException $e) {
        log_warn($e);
        die_info(get_string('registrationunsuccessful', 'auth.internal'));
    }
    catch (SQLException $e) {
        log_warn($e);
        die_info(get_string('registrationunsuccessful', 'auth.internal'));
    }

    redirect($values['goto']);
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

    /**
     * Can be overridden by plugins to assert when they are able to be used. 
     * For example, a plugin might check that a certain PHP extension is loaded
     */
    public static function is_usable() {
        return true;
    }

    public static function update_active_flag($event, $user) {
        if (!isset($user['id'])) {
            log_warn("update_active_flag called without a user id");
        }

        if ($user['id'] === 0 || $user['id'] === '0') {
            return;
        }

        $active = true;

        // ensure we have everything we need
        $user = get_user($user['id']);

        $inactivetime = get_config('defaultaccountinactiveexpire');
        if ($user->suspendedcusr) {
            $active = false;
        }
        else if ($user->expiry && $user->expiry < time()) {
            $active = false;
        }
        else if ($inactivetime) {
            $lastactive = max($user->lastlogin, $user->lastaccess, $user->ctime);
            if ($lastactive && ($lastactive + $inactivetime < time())) {
                $active = false;
            }
        }
        else if ($user->deleted) {
            $active = false;
        }

        if ($active != $user->active) {
            set_field('usr', 'active', (int)$active, 'id', $user->id);
        }
    }

    public static function can_be_disabled() {
        return false;
    }

    /**
     * Can be overridden by plugins that need to inject more
     * pieform elements into the login form.
     */
    public static function login_form_elements() {
        return false;
    }

    /**
     * Can be overridden by plugins that need to inject more
     * Javascript to make the login form work.
     */
    public static function login_form_js() {
        return false;
    }

    /**
     * Can be overridden by plugins that inject the things they need
     * in the login form and don't need the standard elements.
     */
    public static function need_basic_login_form() {
        return true;
    }
}
