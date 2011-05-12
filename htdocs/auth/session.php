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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

//
// Set session settings
//
session_name(get_config('cookieprefix') . 'mahara');
ini_set('session.save_path', '3;' . get_config('dataroot') . 'sessions');
ini_set('session.gc_divisor', 1000);
// Session timeout is stored in minutes in the database
ini_set('session.gc_maxlifetime', get_config('session_timeout') * 60);
ini_set('session.use_only_cookies', true);
ini_set('session.cookie_path', get_mahara_install_subdirectory());
ini_set('session.cookie_httponly', 1);
ini_set('session.hash_bits_per_character', 4);
ini_set('session.hash_function', 0);


// TEMPORARY: this will be REMOVED after the session path changing
// has been around for a bit.
// Attempt to create session directories
$sessionpath = get_config('dataroot') . 'sessions';
if (!is_dir("$sessionpath/0")) {
    // Create three levels of directories, named 0-9, a-f
    $characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
    foreach ($characters as $c1) {
        check_dir_exists("$sessionpath/$c1");
        foreach ($characters as $c2) {
            check_dir_exists("$sessionpath/$c1/$c2");
            foreach ($characters as $c3) {
                check_dir_exists("$sessionpath/$c1/$c2/$c3");
            }
        }
    }
}


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
 */
class Session {

    /**
     * Resumes an existing session, only if there is one
     */
    private function __construct() {
        // Resume an existing session if required
        if (isset($_COOKIE[session_name()])) {
            @session_start();
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
    public function get($key) {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return null;
    }

    /**
     * Sets the session property keyed by $key.
     *
     * @param string $key   The key to set.
     * @param string $value The value to set for the key
     */
    public function set($key, $value) {
        $this->ensure_session();
        $_SESSION[$key] = $value;
    }

    /**
     * Clears the session property keyed by $key (by setting it to null).
     *
     * @param string $key   The key to set.
     */
    public function clear($key) {
        $this->ensure_session();
        $_SESSION[$key] = null;
    }

    /**
     * Adds a message that indicates something was successful
     *
     * @param string $message The message to add
     * @param boolean $escape Whether to HTML escape the message
     */
    public function add_ok_msg($message, $escape=true) {
        $this->ensure_session();
        if ($escape) {
            $message = self::escape_message($message);
        }
        $_SESSION['messages'][] = array('type' => 'ok', 'msg' => $message);
    }

    /**
     * Adds a message that indicates an informational message
     *
     * @param string $message The message to add
     * @param boolean $escape Whether to HTML escape the message
     */
    public function add_info_msg($message, $escape=true) {
        $this->ensure_session();
        if ($escape) {
            $message = self::escape_message($message);
        }
        $_SESSION['messages'][] = array('type' => 'info', 'msg' => $message);
    }

    /**
     * Adds a message that indicates a failure to do something
     *
     * @param string $message The message to add
     * @param boolean $escape Whether to HTML escape the message
     */
    public function add_error_msg($message, $escape=true) {
        $this->ensure_session();
        if ($escape) {
            $message = self::escape_message($message);
        }
        $_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
    }

    /**
     * Builds HTML that represents all of the messages and returns it.
     *
     * This is designed to let smarty templates hook in any session messages.
     *
     * Calling this function will destroy the session messages that were
     * rendered, so they do not inadvertently get displayed again.
     *
     * @return string The HTML representing all of the session messages.
     */
    public function render_messages() {
        global $THEME;
        $result = '<div id="messages">';
        if (isset($_SESSION['messages'])) {
            foreach ($_SESSION['messages'] as $data) {
                $result .= '<div class="' . $data['type'] . '"><div>';
                $result .= $data['msg'] . '</div></div>';
            }
            $_SESSION['messages'] = array();
        }
        $result .= '</div>';
        return $result;
    }

    /**
     * Create a session, by initialising the $_SESSION array.
     */
    private function ensure_session() {
        if (empty($_SESSION)) {
            if (!session_id()) {
                @session_start();
            }
            $_SESSION = array(
                'messages' => array()
            );
        }
    }

    /**
     * Destroy a session
     */
    public function destroy_session() {
        if ($this->is_live()) {
            $_SESSION = array();
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 65536,
                    ini_get('session.cookie_path'),
                    ini_get('session.cookie_domain'),
                    ini_get('session.cookie_secure'),
                    ini_get('session.cookie_httponly')
                );
            }
            session_destroy();
        }
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
function insert_messages() {
    global $SESSION;
    return $SESSION->render_messages();
}


/**
 * Delete all sessions belonging to a given user except for the current one
 */
function remove_user_sessions($userid) {
    global $sessionpath, $USER;

    $sessionids = get_column('usr_session', 'session', 'usr', (int) $userid);

    if (empty($sessionids)) {
        return;
    }

    $alive = array();
    $dead = array();
    $sid = $USER->get('sessionid');
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
        if (session_start()) {
            session_destroy();
            session_commit();
        }
    }

    session_id($sid);
    session_start();

    delete_records_select('usr_session', 'session IN (' . join(',', array_map('db_quote', $alive)) . ')');
}

/**
 * Delete all session files except for the current one
 */
function remove_all_sessions() {
    global $sessionpath, $USER;

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
