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
$put = array();


/**
 * The user class represents any user in the system.
 *
 */
class User {

    /**
     * Defaults for user information.
     *
     * @var array
     */
    protected $defaults;
    protected $stdclass;
    protected $authenticated = false;
    protected $changed       = false;
    protected $attributes    = array();
    protected $SESSION;

    /**
     * Sets defaults for the user object (only because PHP5 does not appear
     * to support private static const arrays), and resumes a session
     */
    public function __construct() {
        $this->defaults = array(
            'logout_time'      => 0,
            'id'               => 0,
            'username'         => '',
            'password'         => '',
            'institution'      => 'mahara',
            'salt'             => '',
            'passwordchange'   => 0,
            'active'           => 1,
            'deleted'          => 0,
            'expiry'           => 0,
            'expirymailsent'   => 0,
            'lastlogin'        => 0,
            'inactivemailsent' => 0,
            'staff'            => 0,
            'admin'            => 0,
            'firstname'        => '',
            'lastname'         => '',
            'studentid'        => '',
            'preferredname'    => '',
            'email'            => '',
            'profileicon'      => null,
            'suspendedctime'   => null,
            'suspendedreason'  => null,
            'suspendedcusr'    => null,
            'quota'            => 0,
            'quotaused'        => 0,
            'authinstance'     => 1,
            'sessionid'        => '', /* The real session ID that PHP knows about */
            'accountprefs'     => array(),
            'activityprefs'    => array(),
            'sesskey'          => ''
        );
        $this->attributes = array();
        $this->SESSION = Session::singleton();

    }

    /**
     * 
     */
    public function find_by_id($id) {
        if (!is_numeric($id) || $id < 0) {
            throw new InvalidArgumentException('parameter must be a positive integer to create a User object');
        }

        $user = get_record('usr', 'id', $id, '*, ' . db_format_tsfield('expiry') . ', ' . db_format_tsfield('lastlogin'). ', ' . db_format_tsfield('suspendedctime'));
        if (false == $user) {
            throw new AuthUnknownUserException("User with id \"$id\" is not known");
        }

        $this->populate($user);
        return $this;
    }

    /**
     * Take a row object from the usr table and populate this object with the
     * values
     *
     * @param  object $data  The row data
     */
    protected function populate($data) {
        reset($this->defaults);
        while(list($key, ) = each($this->defaults)) {
            if (property_exists($data, $key)) {
                $this->set($key, $data->{$key});
            }
        }
    }

    /**
     * Gets the user property keyed by $key.
     *
     * @param string $key The key to get the value of
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get($key) {
        if (!array_key_exists($key, $this->defaults)) {
            throw new InvalidArgumentException($key);
        }
        if (array_key_exists($key, $this->attributes) && null !== $this->attributes[$key]) {
            return $this->attributes[$key];
        }
        return $this->defaults[$key];
    }

    /**
     * Gets the user property keyed by $key.
     *
     * @param string $key The key to get the value of
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function __get($key) {
        return $this->get($key);
    }

    /**
     * Sets the property keyed by $key
     */
    protected function set($key, $value) {

        if (!array_key_exists($key, $this->defaults)) {
            throw new InvalidArgumentException($key);
        }

        $this->attributes[$key] = $value;

        // For now, these fields are saved to the DB elsewhere
        if ($key != 'activityprefs' && $key !=  'accountprefs') {
            $this->changed = true;
        }
        return $this;
    }

    /**
     * Sets the property keyed by $key
     */
    public function __set($key, $value) {
        if ($key == 'quotaused') {
            throw new InvalidArgumentException('quotaused should be set via the quota_* methods');
        }

        if ($key == 'username' && $this->id != 0) {
            throw new InvalidArgumentException('We cannot change the username of an existing user');
        }

        $this->set($key, $value);
    }

    /**
     * Commit the USR record to the database
     */
    public function commit() {
        if ($this->changed == false) {
            return;
        }
        $record = $this->to_stdclass();
        if (is_numeric($this->id) && 0 < $this->id) {
            update_record('usr', $record, array('id' => $this->id));
        } else {
            try {
                $this->set('id', insert_record('usr', $record, 'id', true));
            } catch (Exception $e) {
                // TODO: rethrow error
            }
        }
        $this->changed = false;
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
     * Logs the current user out
     */
    public function logout () {
        if ($this->changed == true) {
            log_debug('Destroying user with un-committed changes');
        }
        $this->set('logout_time', 0);
        if ($this->authenticated === true) {
            $this->SESSION->set('messages', array());
        }
        reset($this->defaults);
        foreach (array_keys($this->defaults) as $key) {
            $this->set($key, $this->defaults[$key]);
        }
        // We don't want to commit the USER object after logout:
        $this->changed = false;
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
        $this->stdclass = new StdClass;
        reset($this->defaults);
        foreach (array_keys($this->defaults) as $k) {
            if ($k == 'expiry' || $k == 'lastlogin' || $k == 'suspendedctime') {
                $this->stdclass->{$k} = db_format_timestamp($this->get($k));
            } else {
                $this->stdclass->{$k} = $this->get($k);//(is_null($this->get($k))? 'NULL' : $this->get($k));
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
        $this->set("quotaused", $newquota);
        return $this;
    }

    public function quota_remove($bytes) {
        if (!is_numeric($bytes) || $bytes < 0) {
            throw new InvalidArgumentException('parameter must be a positive integer to remove from the quota');
        }
        $newquota = $this->get('quotaused') - $bytes;
        if ($newquota < 0) {
            $newquota = 0;
        }
        $this->set("quotaused", $newquota);
        return $this;
    }

    public function quota_allowed($bytes) {
        if ($this->get('quotaused') + $bytes > $this->get('quota')) {
            return false;
        }

        return true;
    }
}


class LiveUser extends User {

    public function __construct() {

        parent::__construct();

        if ($this->SESSION->is_live()) {
            $this->authenticated  = true;
            while(list($key,) = each($this->defaults)) {
                $this->get($key);
            }
        }
    }

    /**
     * Take a username, password and institution and try to authenticate the
     * user
     *
     * @param  string $username
     * @param  string $password
     * @param  string $institution
     * @return bool
     */
    public function login($username, $password, $institution) {
        $users = get_records_select_array('usr', 'LOWER(username) = ? AND institution = ?', array($username, $institution), 'authinstance', '*');
        
        if ($users == false) {
            throw new AuthUnknownUserException("\"$username\" at \"$institution\" is not known");
        }

        foreach($users as $user) {
            $auth = AuthFactory::create($user->authinstance);
            if ($auth->authenticate_user_account($user, $password)) {
                $this->authenticate($user);
                return true;
            }
        }

        return false;
    }

    /**
     * When a user creates a security context by whatever method, we do some 
     * standard stuff
     *
     * @param  object $user     Record from the usr table
     * @return void
     */
    protected function authenticate($user) {
        $this->authenticated  = true;
        $this->populate($user);
        session_regenerate_id(true);
        $this->lastlogin          = time();
        $this->sessionid          = session_id();
        $this->logout_time        = time() + get_config('session_timeout');
        $this->sesskey            = get_random_key();

        // We need a user->id before we load_c*_preferences
        if (empty($user->id)) $this->commit();
        $this->activityprefs      = load_activity_preferences($user->id);
        $this->accountprefs       = load_account_preferences($user->id);
        $this->commit();
    }

    /**
     * When a user creates a security context by whatever method, we do some 
     * standard stuff
     *
     * @param  object $user     Record from the usr table
     * @return void
     */
    public function reanimate($id) {
        if ($user = get_record('usr','id',$id)) {
            $this->authenticate($user);
            return true;
        }
        return false;
    }

    /**
     * Gets the user property keyed by $key.
     *
     * @param string $key The key to get the value of
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get($key) {
        if (!array_key_exists($key, $this->defaults)) {
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
    protected function set($key, $value) {

        if (!array_key_exists($key, $this->defaults)) {
            throw new InvalidArgumentException($key);
        }

        // For now, these fields are saved to the DB elsewhere
        if ($key != 'activityprefs' && $key !=  'accountprefs') {
            $this->changed = true;
        }
        $this->SESSION->set("user/$key", $value);
        return $this;
    }
}
?>
