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
 * The user class represents a single logged in user in the system. The user
 * has several properties that can be accessed and set, including account
 * and activity preferences.
 *
 * The user class stores this information in the session, so that it does not
 * need to be requested every page load.
 */
class User {
    
    /**
     * Defaults for user information.
     *
     * @var array
     */
    private $defaults;
    private $stdclass;

    /**
     * Sets defaults for the user object (only because PHP5 does not appear
     * to support private static const arrays), and resumes a session
     */
    public function __construct($SESSION) {
        $this->defaults = array(
            'logout_time'      => 0,
            'id'               => 0,
            'username'         => '',
            'password'         => '',
            'salt'             => '',
            'institution'      => 'mahara',
            'passwordchange'   => false,
            'deleted'          => false,
            'expiry'           => 0,
            'expirymailsent'   => 0,
            'lastlogin'        => 0,
            'inactivemailsent' => 0,
            'staff'            => false,
            'admin'            => false,
            'quota'            => 0,
            'quotaused'        => 0,
            'firstname'        => '',
            'lastname'         => '',
            'preferredname'    => '',
            'email'            => '',
            'profileicon'      => '',
            'accountprefs'     => array(),
            'activityprefs'    => array(),
            'sesskey'          => ''
        );

        $this->SESSION = $SESSION;
    }
    
    /**
     * Gets the user property keyed by $key.
     *
     * @param string $key The key to get the value of
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get($key) {
        if (!isset($this->defaults[$key])) {
            throw new InvalidArgumentException($key);
        }
        if (null !== ($value = $this->SESSION->get("user/$key"))) {
            return $value;
        }
        return $this->defaults[$key];
    }

    /**
     * Sets the property keyed by $key
     */
    public function set($key, $value) {
        if (!isset($this->defaults[$key])) {
            throw new InvalidArgumentException($key);
        }
        // @todo: Martyn Only for external calls??
        //if ($key == 'quotaused') {
        //    throw new InvalidArgumentException('quotaused should be set via the quota_* methods');
        //}
        $this->SESSION->set("user/$key", $value);
    }

    /** 
     * This function returns a method for a particular
     * activity type, or null if it's not set.
     * 
     * @param string $key the activity type
     */
    public function get_activity_preference($key) {
        $activityprefs = $this->get('activityprefs');
        return isset($activityprefs[$key]) ? $activityprefs[$key] : null;
    }
    
    /** @todo document this method */
    public function set_activity_preference($activity, $method) {
        log_debug("set_activity_preference($activity, $method)");
        set_activity_preference($this->get('id'), $activity, $method);
        $activityprefs = $this->get('activityprefs');
        $activityprefs[$activity] = $method;
        $this->set('activityprefs', $activityprefs);
    }

    /** 
     * This function returns a value for a particular
     * account preference, or null if it's not set.
     * 
     * @param string $key the field name
     */
    public function get_account_preference($key) {
        $accountprefs = $this->get('accountprefs');
        return isset($accountprefs[$key]) ? $accountprefs[$key] : null;
    }
    
    /** @todo document this method */
    public function set_account_preference($field, $value) {
        log_debug("set_account_preference($field, $value)");
        set_account_preference($this->get('id'), $field, $value);
        $accountprefs = $this->get('accountprefs');
        $accountprefs[$field] = $value;
        $this->set('accountprefs', $accountprefs);
    }
    
    /**
     * Determines if the user is currently logged in
     *
     * @return boolean
     */
    public function is_logged_in() {
        return ($this->get('logout_time') > 0 ? true : false);
    }
    
    /**
     * Logs in the given user.
     *
     * The passed object should contain the basic information to persist across
     * page loads.
     *
     * @param object $userdata Information to persist across page loads
     */
    public function login($userdata) {
        foreach (array_keys($this->defaults) as $key) {
            $this->set($key, (isset($userdata->{$key})) ? $userdata->{$key} : $this->defaults[$key]);
        }
        
        $this->set('logout_time', time() + get_config('session_timeout'));
        $this->set('sesskey', get_random_key());
        $this->set('activityprefs', load_activity_preferences($this->get('id')));
        $this->set('accountprefs', load_account_preferences($this->get('id')));
    }
    
    /**
     * Logs the current user out
     */
    public function logout () {
        $this->set('logout_time', 0);
        $this->SESSION->set('messages', array());
        foreach (array_keys($this->defaults) as $key) {
            $this->set($key, $this->defaults[$key]);
        }
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
    }
    
    public function to_stdclass() {
        if (empty($this->stdclass)) {
            $this->stdclass = new StdClass;
            foreach (array_keys($this->defaults) as $k) {
                $this->stdclass->{$k} = $this->get($k);
            }
        }
        return $this->stdclass;
    }

    public function quota_add($bytes) {
        if (!is_numeric($bytes) || $bytes < 0) {
            throw new InvalidArgumentException('parameter must be a positive integer to add to the quota');
        }
        if (!$this->quota_allowed($bytes)) {
            throw new QuotaExceededException('Adding ' . $bytes . ' bytes would exceed the user\'s quota');
        }
        $newquota = $this->get('quotaused') + $bytes;
        $this->SESSION->set("user/quotaused", $newquota);
        update_record('usr', array('quotaused' => $newquota), array('id' => $this->get('id')));
    }

    public function quota_remove($bytes) {
        if (!is_numeric($bytes) || $bytes < 0) {
            throw new InvalidArgumentException('parameter must be a positive integer to remove from the quota');
        }
        $newquota = $this->get('quotaused') - $bytes;
        if ($newquota < 0) {
            $newquota = 0;
        }
        $this->SESSION->set("user/quotaused", $newquota);
        update_record('usr', array('quotaused' => $newquota), array('id' => $this->get('id')));
    }

    public function quota_allowed($bytes) {
        if ($this->get('quotaused') + $bytes > $this->get('quota')) {
            return false;
        }

        return true;
    }
}


?>
