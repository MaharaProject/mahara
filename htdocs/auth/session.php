<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

Session::setup_server_settings();
Session::setup_response_settings();

/**
 * The session class handles session data and messages.
 *
 * This class stores information across page loads, using only a cookie to
 * remember the info. User information is stored in the session so it does
 * not have to be requested each time the page is loaded, however any other
 * information can also be stored using this class.
 *
 * This class also is smart about giving out sessions - if a visitor
 * has not logged in (e.g. they are a guest, searchbot or a simple
 * 'curl' request), a session will not be created for them.
 *
 * Messages are stored in the session and are displayed the next time
 * a page is displayed to a user, even over multiple requests.
 *
 * In addition, to allow the json progress meter to do its work, this
 * class maintains the session state, keeping it read-only except as
 * necessary (in order to not block the progress meter json calls).
 */
class Session {

    /**
     * Configures Session settings that affect server-side behavior. These
     * should be set up as soon as possible on page load, so that they'll
     * be ready in case some bad third-party code calls session_start()
     * out of the normal sequence.
     *
     * So, these should try to avoid relying on $CFG values that are
     * determined dynamically (such as $CFG->wwwroot)
     */
    public static function setup_server_settings() {

        session_name(get_config('cookieprefix') . 'mahara');

        // Secure session settings
        // See more at http://php.net/manual/en/session.security.php
        ini_set('session.use_cookies', true);
        ini_set('session.use_only_cookies', true);
        if (version_compare(PHP_VERSION, '7.1.0') >= 0) {
            ini_set('session.sid_bits_per_character', 5);
        }
        else {
            ini_set('session.hash_bits_per_character', 4);
        }
        ini_set('session.gc_divisor', 1000);

        if (get_config('session_timeout')) {
            // Limit session timeout to 30 days.
            $session_timeout = min(get_config('session_timeout'), 60 * 60 * 24 * 30);
        }
        else {
            // If session was started up by an error message before the database was initiated,
            // then fall back to a default session timeout of 1 hour.
            $session_timeout = 60 * 60;
        }
        // Note: session.gc_maxlifetime is not the main way login session expiry is enforced.
        // We do that by looking at usr.last_access, in htdocs/auth/user.php.
        // And if you're using the default PHP file session handler with depthdir 3, cleanup
        // of old session files is actually handled by the Mahara cron task auth_remove_old_session_files.
        ini_set('session.gc_maxlifetime', $session_timeout);

        ini_set('session.use_trans_sid', false);
        if (version_compare(PHP_VERSION, '7.1.0') >= 0) {
            ini_set('session.sid_length', 32);
        }
        else {
            ini_set('session.hash_function', 'sha256'); // stronger hash functions are sha384 and sha512
        }
        if (version_compare(PHP_VERSION, '5.5.2') > 0) {
            ini_set('session.use_strict_mode', true);
        }

        // Now lets use the correct session handler
        // Currently only deals with file and memcached
        switch (get_config('sessionhandler')) {
            case 'memcache':
                throw new ConfigSanityException(get_string('memcacheusememcached', 'error'));
                break;
            case 'memcached':
                $memcacheservers = get_config('memcacheservers');
                if (!$memcacheservers) {
                    throw new ConfigSanityException(get_string('nomemcacheserversdefined', 'error', get_config('sessionhandler')));
                }
                if (!extension_loaded(get_config('sessionhandler'))) {
                    throw new ConfigSanityException(get_string('nophpextension', 'error', get_config('sessionhandler')));
                }
                // Because we only want memcached servers we need to strip off any 'tcp://' if accidentally added
                $servers = preg_replace('#tcp://#', '', $memcacheservers);
                foreach (explode(',', $servers) as $server) {
                    list($destination, $port) = explode(':', $server);
                    $memcached = new Memcached;
                    if (!empty($destination) && !empty($port)) {
                        $memcached->addServer($destination, $port);
                        // addServer doesn't make a connection to the server
                        // but if the server is added, but not running pid will be -1
                        $server_stats = $memcached->getStats();
                        if ($server_stats[$destination . ":" . $port]['pid'] <= 0) {
                            // can't reach the destination:port
                            $server_version = $memcached->getVersion();
                            if (empty($server_version[$destination . ":" . $port])) {
                                throw new ConfigSanityException(get_string('nomemcachedserver', 'error', $server));
                            }
                        }
                    }
                    else {
                        throw new ConfigSanityException(get_string('nomemcachedserver', 'error', $server));
                    }
                }
                ini_set('session.save_handler', 'memcached');
                ini_set('session.save_path', $servers);

                $sess = new MemcachedSession();
                session_set_save_handler($sess, true);
                break;
            case 'file':
                $sessionpath = get_config('sessionpath');
                ini_set('session.save_path', '3;' . $sessionpath);
                // Attempt to create session directories
                if (!is_dir("$sessionpath/0")) {
                    // Create three levels of directories, named 0-9, a-f
                    Session::create_directory_levels($sessionpath);
                }
                break;
            case 'redis':
                if (!extension_loaded(get_config('sessionhandler'))) {
                    throw new ConfigSanityException(get_string('nophpextension', 'error', get_config('sessionhandler')));
                }
                else if (
                         ($redissentinelservers = get_config('redissentinelservers')) &&
                         ($redismastergroup = get_config('redismastergroup')) &&
                         ($redisprefix = get_config('redisprefix'))
                        ) {
                    require_once(get_config('libroot') . 'redis/sentinel.php');

                    $sentinel = new sentinel($redissentinelservers);
                    $master = $sentinel->get_master_addr($redismastergroup);

                    if (!empty($master)) {
                        ini_set('session.save_handler', 'redis');
                        ini_set('session.save_path', 'tcp://' . $master->ip . ':' . $master->port . '?prefix=' . $redisprefix);
                    }
                    else {
                        throw new ConfigSanityException(get_string('badsessionhandle', 'error', get_config('sessionhandler')));
                    }
                }
                else {
                    throw new ConfigSanityException(get_string('badsessionhandle', 'error', get_config('sessionhandler')));
                }
                break;
            default:
                throw new ConfigSanityException(get_string('wrongsessionhandle', 'error', get_config('sessionhandler')));
        }
    }

    public static function create_directory_levels($sessionpath) {
        $status = true;

        if (check_dir_exists($sessionpath)) {
            // Create three levels of directories, named 0-9, a-f
            $characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
            if (version_compare(PHP_VERSION, '7.1.0') >= 0) {
                $characters = array_merge($characters, array('g', 'h', 'i', 'j', 'k', 'l', 'm', 'n',
                                                             'o', 'p', 'q', 'r', 's', 't', 'u', 'v'));
            }
            foreach ($characters as $c1) {
                if (check_dir_exists("$sessionpath/$c1")) {
                    foreach ($characters as $c2) {
                        if (check_dir_exists("$sessionpath/$c1/$c2")) {
                            foreach ($characters as $c3) {
                                if (!check_dir_exists("$sessionpath/$c1/$c2/$c3")) {
                                    $status = false;
                                    break(3);
                                }
                            }
                        }
                        else {
                            $status = false;
                            break(2);
                        }
                    }
                }
                else {
                    $status = false;
                    break;
                }
            }
        }
        else {
            $status = false;
        }
        return $status;
    }

    /**
     * Sets up Session settings that affect the cookie that we write out to
     * the browser. These need to be set up correctly before we send out
     * response headers. So we can be more flexible here, and include
     * ones that are set up dynamically.
     */
    public static function setup_response_settings() {
        ini_set('session.cookie_lifetime', 0);
        ini_set('session.cookie_httponly', true);
        if (is_https()) {
            ini_set('session.cookie_secure', true);
        }
        if ($domain = get_config('cookiedomain')) {
            ini_set('session.cookie_domain', $domain);
        }
        ini_set('session.cookie_path', get_mahara_install_subdirectory());
    }

    private $sessionid = null;

    /**
     * Returns the current (or last known) session id
     */
    public function session_id() {
        if (session_id()) {
            $this->sessionid = session_id();
        }
        return $this->sessionid;
    }

    /**
     * Resumes an existing session, only if there is one
     */
    private function __construct() {
        // Resume an existing session if required
        if (isset($_COOKIE[session_name()])) {
            @session_start();
            $this->sessionid = session_id();

            // But it's not writable except through functions below.
            $this->ro_session();
        }
    }

    /**
     * Singelton function keeps us from generating multiple instances of this
     * class
     *
     * @return object   The class instance
     * @access public
     */
    public static function singleton() {
        //single instance
        static $instance;

        //if we don't have the single instance, create one
        if (!isset($instance)) {
            $instance = new Session();
        }
        return($instance);
    }

    /**
     * Gets the session property keyed by $key.
     *
     * @param string $key The key to get the value of
     * @return mixed
     */
    public function __get($key) {
        return $this->get($key);
    }

    /**
     * Gets the session property keyed by $key.
     *
     * @param string $key The key to get the value of
     * @return mixed
     */
    public function get($key) {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return null;
    }

    /**
     * Sets the session property keyed by $key.
     *
     * @param string $key The key to get the value of
     * @return mixed
     */
    public function __set($key, $value) {
        return $this->set($key, $value);
    }

    /**
     * Sets the session property keyed by $key.
     *
     * @param string $key   The key to set.
     * @param string $value The value to set for the key
     */
    public function set($key, $value) {
        $this->ensure_session();

        if (is_null($value)) {
            unset($_SESSION[$key]);
        }
        else {
            $_SESSION[$key] = $value;
        }
        $this->ro_session();
    }

    /**
     * Unsets the session property keyed by $key.
     *
     * @param string $key   The key to remove.
     */
    public function __unset($key) {
        $this->ensure_session();
        unset($_SESSION[$key]);
        $this->ro_session();
    }

    /**
     * Old way of clearing session property - added for backwards compatibility
     *
     * @param string $key   The key to remove.
     */
    public function clear($key) {
        $this->__unset($key);
    }

    /**
     * Checks that a successful message is only added once
     *
     * @param string $message The message to add
     * @param boolean $escape Whether to HTML escape the message
     * @param string $placement Place for messages to appear on page (See render_messages()
     *     for information about placement options)
     */
    public function add_msg_once($message, $type, $escape=true, $placement='messages') {
        $this->ensure_session();
        if ($escape) {
            $message = self::escape_message($message);
        }
        $msgs = $this->get('messages');
        foreach ($msgs as $msg) {
            if ($msg ['msg'] == $message && $msg['type'] == $type && $msg['placement'] == $placement) {
                // msg exists
                $this->ro_session();
                return;
            }
        }
        $typestr = 'add_' . $type . '_msg';
        $this->$typestr($message, $escape=true, $placement='messages');
        $this->ro_session();
    }
    /**
     * Adds a message that indicates something was successful
     *
     * @param string $message The message to add
     * @param boolean $escape Whether to HTML escape the message
     * @param string $placement Place for messages to appear on page (See render_messages()
     *     for information about placement options)
     */
    public function add_ok_msg($message, $escape=true, $placement='messages') {
        $this->ensure_session();
        if ($escape) {
            $message = self::escape_message($message);
        }
        $_SESSION['messages'][] = array('type' => 'ok', 'msg' => $message, 'placement' => $placement);
        $this->ro_session();
    }

    /**
     * Adds a message that indicates an informational message
     *
     * @param string $message The message to add
     * @param boolean $escape Whether to HTML escape the message
     * @param string $placement Place for messages to appear on page (See render_messages()
     *     for information about placement options)
     */
    public function add_info_msg($message, $escape=true, $placement='messages') {
        $this->ensure_session();
        if ($escape) {
            $message = self::escape_message($message);
        }
        $_SESSION['messages'][] = array('type' => 'info', 'msg' => $message, 'placement' => $placement);
        $this->ro_session();
    }

    /**
     * Adds a message that indicates a failure to do something
     *
     * @param string $message The message to add
     * @param boolean $escape Whether to HTML escape the message
     * @param string $placement Place for messages to appear on page (See render_messages()
     *     for information about placement options)
     */
    public function add_error_msg($message, $escape=true, $placement='messages') {
        $this->ensure_session();
        if ($escape) {
            $message = self::escape_message($message);
        }
        $_SESSION['messages'][] = array('type' => 'error', 'msg' => $message, 'placement' => $placement);
        $this->ro_session();
    }

    /**
     * Builds HTML that represents all of the messages and returns it.
     *
     * This is designed to let smarty templates hook in any session messages.
     *
     * Calling this function will destroy the session messages that were
     * assigned to the $placement, so they do not inadvertently get
     * displayed again.
     *
     * To define where the messages for a particular $placement value should be displayed,
     * add this code to a page template:
     *
     *   {dynamic}{insert_messages placement='your_placement_name_here'}{/dynamic}
     *
     * The default 'messages' placement is shown on every page, and is suitable for most purposes.
     * Alternative placements should only be needed in special situations, such as showing a login-related
     * error in the login box. Note that messages will hang around in the $SESSION until a page template
     * with their "placement" in it is loaded. So, they should only be used in situations where you're
     * certain their placement zone will be present on the next page load, or else the user may be
     * confused by their appearance several page loads later.
     *
     * @param string $placement Render only messages for this placement
     *
     * @return string The HTML representing all of the session messages assigned
     * to $placement.
     */
    public function render_messages($placement = 'messages') {
        global $THEME;
        $result = '<div id="' . $placement . '" role="alert" aria-live="assertive">';
        if (isset($_SESSION['messages'])) {
            $this->ensure_session();  // Make it writable and lock against other threads.
            foreach ($_SESSION['messages'] as $key => $data) {

                $typeClass = $data['type'] === 'ok' ? 'success' : $data['type'];

                if ($typeClass === 'error') {
                    $typeClass = 'danger';
                }

                if ($data['placement'] == $placement) {
                    $result .= '<div class="alert alert-' . $typeClass . '"><div>';
                    $result .= $data['msg'] . '</div></div>';
                    unset($_SESSION['messages'][$key]);
                }
            }
            $this->ro_session();
        }
        $result .= '</div>';
        return $result;
    }

    public function set_progress($token, $content) {
        // Make the session writable.
        $this->ensure_session();

        if ($content === FALSE) {
            unset($_SESSION['progress_meters'][$token]);
        }
        else {
            $_SESSION['progress_meters'][$token] = $content;
        }

        // Release our lock.
        $this->ro_session();
    }

    /**
     * Create a session, by initialising the $_SESSION array.
     */
    private function ensure_session() {
        if (defined('CLI')) {
            return;
        }

        if (empty($_SESSION)) {
            @session_start();
            $this->sessionid = session_id();
            $_SESSION = array(
                'messages' => array()
            );
        }
        else {
            @session_start();
            $this->sessionid = session_id();
        }
        // Anytime you call session_start() more than once, PHP will usually
        // send out a duplicate session header.
        clear_duplicate_cookies();
    }

    /*
     * Make a session readonly after modifying it.
     *
     * The session must have been opened already.
     */

    private function ro_session() {
        if (defined('CLI')) {
            return;
        }

        session_write_close();
    }

    /**
     * Destroy a session. This removes all data from the $_SESSION object,
     * deletes it from the server, and rotates the user to a new session
     * id.
     */
    public function destroy_session() {
        if (defined('CLI')) {
            return;
        }

        if ($this->is_live()) {
            $_SESSION = array();
            @session_start();
            session_destroy();
            $this->sessionid = null;
            clear_duplicate_cookies();

            // Tell the browser to immediately expire the session cookie.
            // (If we take any actions that require a new session, this
            // will be ignored, and instead the old session cookie will
            // be replaced by the new one.)
            if (isset($_COOKIE[session_name()])) {
                setcookie(
                    session_name(),
                    '',
                    1,
                    ini_get('session.cookie_path'),
                    ini_get('session.cookie_domain'),
                    ini_get('session.cookie_secure'),
                    ini_get('session.cookie_httponly')
                );
            }
        }
    }

    /**
     * Regenerate session id. This does *not* clear the $_SESSION object
     * or the session data on the server. It just changes the user's
     * session ID. You should do this whenever a user logs in or otherwise
     * changes their permission level, to avoid session fixation attacks.
     *
     * If you want to clear the session, call $SESSION->destroy_session()
     *
     * @return boolean
     */
    public function regenerate_id() {
        $this->ensure_session();
        $result = session_regenerate_id(true);
        $this->sessionid = session_id();
        if (!$result) {
            log_warn("session_regenerate_id() failed");
        }
        $this->ro_session();
        return $result;
    }

    /**
     * Find out if the session has been started yet
     */
    public function is_live() {
        if ("" == session_id()) {
            return false;
        }
        return true;
    }

    /**
     * Escape a message for HTML output
     *
     * @param string $message The message to escape
     * @return string         The message, escaped for output as HTML
     */
    private static function escape_message($message) {
        $message = hsc($message);
        $message = str_replace('  ', '&nbsp; ', $message);
        return $message;
    }

}

/**
 * A smarty callback to insert page messages
 *
 * @return string The HTML represening all of the session messages.
 */
function insert_messages($placement='messages') {
    global $SESSION;
    $messages = $SESSION->render_messages($placement);
    return (array($placement => $messages));
}


/**
 * Delete all sessions belonging to a given user except for the current one
 */
function remove_user_sessions($userid) {
    global $USER, $SESSION;
    $sessionpath = get_config('sessionpath');
    $sessionids = get_column('usr_session', 'session', 'usr', (int) $userid);

    if (empty($sessionids)) {
        return;
    }

    $alive = array();
    $dead = array();

    // Keep track of the current session id so we can return to it at the end
    if ($SESSION->is_live()) {
        $sid = $USER->get('sessionid');
    }
    else {
        // The user has no session (this function is being called by a CLI script)
        $sid = false;
    }

    foreach ($sessionids as $sessionid) {
        if ($sessionid == $sid) {
            continue;
        }
        $file = $sessionpath;
        for ($i = 0; $i < 3; $i++) {
            $file .= '/' . substr($sessionid, $i, 1);
        }
        $file .= '/sess_' . $sessionid;
        if (file_exists($file)) {
            $alive[] = $sessionid;
        }
        else {
            $dead[] = $sessionid;
        }
    }

    if (!empty($dead)) {
        delete_records_select('usr_session', 'session IN (' . join(',', array_map('db_quote', $dead)) . ')');
    }

    if (empty($alive)) {
        return;
    }

    session_commit();

    foreach ($alive as $sessionid) {
        session_id($sessionid);
        if (session_start() && session_id() === $sessionid) {
            session_destroy();
        }
    }

    if ($sid !== false) {
        session_id($sid);
        session_start();
        session_write_close();
    }

    clear_duplicate_cookies();
    delete_records_select('usr_session', 'session IN (' . join(',', array_map('db_quote', $alive)) . ')');
}

/**
 * Delete all session files except for the current one
 */
function remove_all_sessions() {
    global $USER;
    $sessionpath = get_config('sessionpath');
    $sid = $USER->get('sessionid');

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sessionpath));
    foreach ($iterator as $path) {
        if ($path->isFile() && $path->getFilename() !== ('sess_' . $sid)) {
            @unlink($path->getPathname());
        }
    }
    clearstatcache();

    delete_records_select('usr_session', 'session != ?', array($sid));
}

/**
 * Every time you call session_start(), PHP adds another
 * identical session cookie to the response header. Do this
 * enough times, and your response header becomes big enough
 * to choke the web server.
 *
 * This method clears out the duplicate session cookies.
 */
function clear_duplicate_cookies() {
    global $SESSION;
    // If headers have already been sent, there's nothing we can do
    if (headers_sent()) {
        return;
    }

    $cookiename = session_name();
    $headers = array();
    foreach (headers_list() as $header) {
        if (strpos($header, "Set-Cookie: {$cookiename}=") !== 0) {
            $headers[] = $header;
        }
    }
    // Remove all the headers
    header_remove();

    // Now bring back the ones we want.
    foreach($headers as $header) {
        header($header, false);
    }

    // Now manually regenerate just ONE session cookie header.
    if ($SESSION->session_id()) {
        setcookie(
            $cookiename,
            $SESSION->session_id(),
            0,
            ini_get('session.cookie_path'),
            ini_get('session.cookie_domain'),
            ini_get('session.cookie_secure'),
            ini_get('session.cookie_httponly')
        );
    }
}

class MemcachedSession extends SessionHandler {
    // we need to extend and override the read method to force it to return string value
    // in order to comply with PHP 7's more strict type checking
    public function read($session_id) {
        return (string)parent::read($session_id);
    }
}
