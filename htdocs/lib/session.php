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


/**
 * The session class handles user sessions and session messages.
 *
 * This class stores information about the user across page loads,
 * so it only needs to be requested once when a user logs in.
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
     * Defaults for user information.
     *
     * @var array
     */
    private $defaults;

    /**
     * Sets defaults for the session object (only because PHP5 does not appear
     * to support private static const arrays), and resumes a session only if
     * a session already exists.
     */
    public function __construct() {
        $this->defaults = array(
            'logout_time' => 0,
            'username'    => ''
        );
        // Resume an existing session if required
        if (isset($_COOKIE['PHPSESSID'])) {
            session_start();
        }
    }

    /**
     * Gets the session property keyed by $key.
     *
     * @param string $key The key to get the value of
     * @return mixed
     * @throws KeyInvalidException
     * @todo<nigel>: Given that KeyInvalidException doesn't actually exist,
     * referring to an incorrect key will be fatal. I'm not going to do anything
     * about this until more is known about what will be stored in the session.
     */
    public function get($key) {
        if (!isset($this->defaults[$key])) {
            throw new KeyInvalidException($key);
        }
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return $this->defaults[$key];
    }

    /**
     * Sets the session property keyed by $key.
     *
     * @param string $key   The key to set.
     * @param string $value The value to set for the key
     */
    public function set($key, $value) {
        if (!isset($this->defaults[$key])) {
            throw new KeyInvalidException($key);
        }
        if (!$_SESSION) {
            $this->create_session();
        }
        $_SESSION[$key] = $value;
    }

    /**
     * Logs in the given user.
     *
     * The passed object should contain the basic information to persist across
     * page loads.
     *
     * @param object $USER  The user object with properties to persist already
     *                      set
     */
    public function login($USER) {
        if (empty($_SESSION)) {
            $this->create_session();
        }
        foreach (array_keys($this->defaults) as $key) {
            $this->set($key, (isset($USER->{$key})) ? $USER->{$key} : $this->defaults[$key]);
        }
        $this->set('logout_time', time() + get_config('session_timeout'));
    }

    /**
     * Assuming that a session is already active for a user, this method
     * retrieves the information from the session and creates a user object
     * that the script can use
     *
     * @return object
     */
    public function renew() {
        $this->set('logout_time', time() + get_config('session_timeout'));
        $USER = new StdClass;
        foreach (array_keys($this->defaults) as $key) {
            $USER->{$key} = $this->get($key);
        }
        return $USER;
    }

    /**
     * Logs the current user out
     */
    public function logout () {
        $_SESSION = array(
            'logout_time' => 0,
            'messages'    => array()
        );
    }


    /**
     * Adds a message that indicates something was successful
     *
     * @param string $message The message to add
     * @param boolean $escape Whether to HTML escape the message
     */
    public function add_ok_msg($message, $escape=true) {
        if (empty($_SESSION)) {
            $this->create_session();
        }
        if ($escape) {
            $message = htmlspecialchars($message, ENT_COMPAT, 'UTF-8');
            $message = str_replace('  ', '&nbsp; ', $message);
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
        if (empty($_SESSION)) {
            $this->create_session();
        }
        if ($escape) {
            $message = htmlspecialchars($message, ENT_COMPAT, 'UTF-8');
            $message = str_replace('  ', '&nbsp; ', $message);
        }
        $_SESSION['messages'][] = array('type' => 'info', 'msg' => $message);
    }

    /**
     * Adds a message that indicates a failure to do something
     *
     * @param string $message The message to add
     * @param boolean $escape Whether to HTML escape the message
     */
    public function add_err_msg($message, $escape=true) {
        if (empty($_SESSION)) {
            $this->create_session();
        }
        if ($escape) {
            $message = htmlspecialchars($message, ENT_COMPAT, 'UTF-8');
            $message = str_replace('  ', '&nbsp; ', $message);
        }
        $_SESSION['messages'][] = array('type' => 'err', 'msg' => $message);
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
        $result = '';
        if (isset($_SESSION['messages'])) {
            foreach ($_SESSION['messages'] as $data) {
                if ($data['type'] == 'ok') {
                    $color = 'green';
                }
                elseif ($data['type'] == 'info') {
                    $color = '#aa6;';
                }
                else {
                    $color = 'red';
                }
                $result .= '<div style="color:' . $color . ';">' . $data['msg'] . '</div>';
            }
            $_SESSION['messages'] = array();
        }
        return $result;
    }

    /**
     * Create a session, by initialising the $_SESSION array.
     */
    private function create_session() {
        if (!session_id()) {
            session_start();
        }
        $_SESSION = array(
            'messages' => array()
        );
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

$SESSION =& new Session;

?>
