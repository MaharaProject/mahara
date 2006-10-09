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
 * Messages are stored in the session and are displayed the next time
 * a page is displayed to a user, even over multiple requests (but not
 * over multiple sessions)
 *
 * This is a _really rough_ first cut, in order to support messaging.
 */
class Session {

    function __construct() {
        session_start();
        if (!$_SESSION) {
            $_SESSION = array();
        }
    }

    /**
     * Adds a message that indicates something was successful
     *
     * @param string $message The message to add
     * @param boolean $escape Whether to HTML escape the message
     */
    public function add_ok_msg($message, $escape=true) {
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
        foreach ($_SESSION['messages'] as $data) {
            if ($data['type'] == 'ok') {
                $color = 'green';
            }
            elseif ($data['type'] == 'info') {
                $color = '#990;';
            }
            else {
                $color = 'red';
            }
            $result .= '<div style="color:' . $color . ';">' . $data['msg'] . '</div>';
        }
        $_SESSION['messages'] = array();
        return $result;
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
