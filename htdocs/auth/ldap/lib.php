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
 * @subpackage auth-ldap
 * @author     Howard Miller <howard.miller@udcf.gla.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2008 Howard Miller <howard.miller@udcf.gla.ac.uk>
 * @copyright  (C) 2008-2009 Catalyst IT Ltd http://catalyst.net.nz
 * @copyright  (C) portions from Moodle, (C) Martin Dougiamas http://dougiamas.com
 */

defined('INTERNAL') || die();
require_once(get_config('docroot') . 'auth/lib.php');

/**
 * Authenticates users with Lightweight Directory Access Protocol
 */
class AuthLdap extends Auth {

    public function __construct($id = null) {
        $this->type = 'ldap';
        $this->has_instance_config = true;

        $this->config['host_url'] = '';
        $this->config['contexts'] = '';
        $this->config['user_type'] = 'default';
        $this->config['user_attribute'] = '';
        $this->config['search_sub'] = 'yes';  
        $this->config['bind_dn'] = '';
        $this->config['bind_pw'] = '';
        $this->config['version'] = 3;
        $this->config['starttls'] = 0;
        $this->config['updateuserinfoonlogin'] = 0;
        $this->config['weautocreateusers'] = 1;
        $this->config['firstnamefield' ] = '';
        $this->config['surnamefield'] = '';
        $this->config['emailfield'] = '';

        if (!empty($id)) {
            return $this->init($id);
        }
        return true;
    }

    public function init($id = null) {
        $this->ready = parent::init($id);

        // Check that required fields are set
        if ( empty($this->config['host_url']) ||
             empty($this->config['contexts']) ||
             empty($this->config['user_attribute']) ||
             empty($this->config['version']) ||
             empty($this->config['search_sub']) ) {
            $this->ready = false;
        }

        return $this->ready;
    }

    /**
     * Attempt to authenticate user
     *
     * @param string $user The username to authenticate with
     * @param string $password The password being used for authentication
     * @return bool            True/False based on whether the user
     *                         authenticated successfully
     * @throws AuthUnknownUserException If the user does not exist
     */
    public function authenticate_user_account($user, $password) {
        $this->must_be_ready();
        $username = $user->username;

        // check ldap functionality exists
        if (!function_exists('ldap_bind')) {
            throw new AuthUnknownUserException('LDAP is not available in your PHP environment. Check that it is properly installed');
        }

        // empty username or password is not allowed.
        if (empty($username) or empty($password)) {    
            return false;
        }
        // For update user info on login
        $update = false;

        if ('1' == $this->config['updateuserinfoonlogin']) {
                $update = true;
        }
        // Missed out AD bit, someone might want to put it back :-)

        // attempt ldap connection
        $ldapconnection = $this->ldap_connect();
        if ($ldapconnection) {
            $ldap_user_dn = $this->ldap_find_userdn($ldapconnection, $username);

            //if ldap_user_dn is empty, user does not exist
            if (!$ldap_user_dn) {
                ldap_close($ldapconnection);
                return false;
            }

            // Try to bind with current username and password
            $ldap_login = @ldap_bind($ldapconnection, $ldap_user_dn, $password);
            ldap_close($ldapconnection);
            if ($ldap_login) {
                if ($user->id && $update) {
                    // Define ldap attributes
                    $ldapattributes = array();
                    $ldapattributes['firstname'] = $this->config['firstnamefield'];
                    $ldapattributes['lastname']  = $this->config['surnamefield' ];
                    $ldapattributes['email']     = $this->config['emailfield' ];

                    // Retrieve information of user from LDAP
                    $ldapdetails = $this->get_userinfo_ldap($username, $ldapattributes);

                    // Match database and ldap entries and update in database if required
                    $fieldstoimport = array('firstname', 'lastname', 'email');
                    foreach ($fieldstoimport as $field) {
                        if (!empty($ldapdetails[$field]) && ($user->$field != $ldapdetails[$field])) {
                            $user->$field = $ldapdetails[$field];
                            set_profile_field($user->id, $field, $user->$field);
                        }
                    }
               }
                return true;
            }
        }
        else {
            @ldap_close($ldapconnection);
            // let's do some logging too
            log_warn("LDAP connection failed: ".$this->config['host_url'].'/'.$this->config['contexts']);
            throw new AuthInstanceException(get_string('cannotconnect', 'auth.ldap'));
        }

        return false;  // No match
    }

    /**
     * Connects to ldap server
     *
     * Tries to connect to specified ldap servers.
     * Returns connection result or error.
     *
     * @return connection result
     */
    private function ldap_connect($binddn='',$bindpwd='') {
        // Select bind password, With empty values use
        // ldap_bind_* variables or anonymous bind if ldap_bind_* are empty
        if ($binddn == '' and $bindpwd == '') {
            if (!empty($this->config['bind_dn'])) {
               $binddn = $this->config['bind_dn'];
            }
            if (!empty($this->config['bind_pw'])) {
               $bindpwd = $this->config['bind_pw'];
            }
        }

        $urls = explode(";", $this->config['host_url']);

        foreach ($urls as $server) {
            $server = trim($server);
            if (empty($server)) {
                continue;
            }

            $connresult = ldap_connect($server);
            // ldap_connect returns ALWAYS true

            if (!empty($this->config['version'])) {
                ldap_set_option($connresult, LDAP_OPT_PROTOCOL_VERSION, $this->config['version']);
            }

            if ($this->config['user_type'] == 'ad') {
                 ldap_set_option($connresult, LDAP_OPT_REFERRALS, 0);
            }

            if (!empty($this->config['starttls'])) {
                if (!ldap_start_tls($connresult)) {
                    return false;
                }
            }

            if (!empty($binddn)) {
                // bind with search-user
                $bindresult = ldap_bind($connresult, $binddn,$bindpwd);
            }
            else {
                // bind anonymously
                $bindresult = @ldap_bind($connresult);
            }

            if (!empty($this->config->opt_deref)) {
                ldap_set_option($connresult, LDAP_OPT_DEREF, LDAP_DEREF_NEVER); // latter is an option in Moodle
            }

            if ($bindresult) {
                return $connresult;
            }

        }

        // If any of servers are alive we have already returned connection
        return false;
    }

    /**
     * retuns dn of username
     *
     * Search specified contexts for username and return user dn
     * like: cn=username,ou=suborg,o=org
     *
     * @param mixed $ldapconnection  $ldapconnection result
     * @param mixed $username username (external encoding no slashes)
     *
     */
    private function ldap_find_userdn($ldapconnection, $username) {
        // default return value
        $ldap_user_dn = FALSE;

        // get all contexts and look for first matching user
        $ldap_contexts = explode(";", $this->config['contexts']);

        foreach ($ldap_contexts as $context) {
            $context = trim($context);
            if (empty($context)) {
                continue;
            }

            if ($this->config['search_sub'] == 'yes') {
                // use ldap_search to find first user from subtree
                $ldap_result = ldap_search($ldapconnection, $context, '(' . $this->config['user_attribute']
                    . '=' . $this->filter_addslashes($username) . ')', array($this->config['user_attribute']));

            }
            else {
                // search only in this context
                $ldap_result = ldap_list($ldapconnection, $context, '(' . $this->config['user_attribute']
                    . '=' . $this->filter_addslashes($username) . ')', array($this->config['user_attribute']));
            }

            $entry = ldap_first_entry($ldapconnection,$ldap_result);

            if ($entry) {
                $ldap_user_dn = ldap_get_dn($ldapconnection, $entry);
                break ;
            }
        }
        return $ldap_user_dn;
    }

    /**
     * Quote control characters in texts used in ldap filters - see rfc2254.txt
     *
     * @param string
     */
    private function filter_addslashes($text) {
        $text = str_replace('\\', '\\5c', $text);
        $text = str_replace(array('*',    '(',    ')',    "\0"),
                            array('\\2a', '\\28', '\\29', '\\00'), $text);
        return $text;
    }

    /**
     * We can autocreate users if the admin has said we can
     * in weautocreateusers
     */
    public function can_auto_create_users() {
        return (bool)$this->config['weautocreateusers'];    
    }

    /**
     * Get basic user info to create new users
     * Needed if can_auto_create_users comes back true
     *
     * @param string $username The username to look up information for
     * @return array           The information for the user
     * @throws AuthUnknownUserException If the user is unknown to the
     *                                  authentication method
     */
    public function get_user_info($username) {
        // get the attribute field names
        $attributes = array();
        $attributes['firstname'] = $this->config['firstnamefield'];
        $attributes['lastname']  = $this->config['surnamefield' ];
        $attributes['email']     = $this->config['emailfield'];

        $userinfo = $this->get_userinfo_ldap($username, $attributes);

        return (object)$userinfo;
    }

    /**
     * Reads userinformation from ldap and return it in array()
     *
     * Function should return all information available.
     *
     * @param string $username username (with system magic quotes)
     *
     * @return mixed array with no magic quotes or false on error
     */
    private function get_userinfo_ldap($username, $attrmap ) {
        $ldapconnection = $this->ldap_connect();

        $result = array();
        $search_attribs = array();

        foreach ($attrmap as $key => $values) {
            if (!is_array($values)) {
                $values = array($values);
            }
            foreach ($values as $value) {
                if (!in_array($value, $search_attribs)) {
                    array_push($search_attribs, $value);
                }
            }
        }

        $user_dn = $this->ldap_find_userdn($ldapconnection, $username);

        if (!$user_info_result = ldap_read($ldapconnection, $user_dn, 'objectClass=*', $search_attribs)) {
            return false; // error!
        }
        $user_entry = $this->ldap_get_entries($ldapconnection, $user_info_result);
        if (empty($user_entry)) {
            return false; // entry not found
        }

        foreach ($attrmap as $key => $values) {
            if (!is_array($values)) {
                $values = array($values);
            }
            $ldapval = NULL;
            foreach ($values as $value) {
                if ($value == 'dn') {
                    $result[$key] = $user_dn;
                }
                if (!array_key_exists($value, $user_entry[0])) {
                    continue; // wrong data mapping!
                }
                if (is_array($user_entry[0][$value])) {
                    $newval = $user_entry[0][$value][0];
                }
                else {
                    $newval = $user_entry[0][$value];
                }
                if (!empty($newval)) { // favour ldap entries that are set
                    $ldapval = $newval;
                }
            }
            if (!is_null($ldapval)) {
                $result[$key] = $ldapval;
            }
        }

        @ldap_close($ldapconnection);
        return $result;
    }

    /**
     * Return entries from ldap
     *
     * Returns values like ldap_get_entries but is binary compatible and return 
     * all attributes as array
     *
     * @return array ldap-entries
     */
    private function ldap_get_entries($conn, $searchresult) {
        $i = 0;
        $fresult=array();
        $entry = ldap_first_entry($conn, $searchresult);
        do {
            $attributes = @ldap_get_attributes($conn, $entry);
            for ($j = 0; $j<$attributes['count']; $j++) {
                $values = ldap_get_values_len($conn, $entry,$attributes[$j]);
                if (is_array($values)) {
                    $fresult[$i][$attributes[$j]] = $values;
                }
                else {
                    $fresult[$i][$attributes[$j]] = array($values);
                }
            }
            $i++;
        }
        while ($entry = @ldap_next_entry($conn, $entry));
        // We're done
        return $fresult;
    }

}

/**
 * Plugin configuration class
 */
class PluginAuthLdap extends PluginAuth {

    private static $default_config = array(
        'host_url'          => '',
        'contexts'          => '',
        'user_type'         => 'default',
        'user_attribute'    => '',
        'search_sub'        => 'yes',
        'bind_dn'           => '',
        'bind_pw'           => '',
        'version'           => 3,
        'starttls'          => 0,
        'updateuserinfoonlogin' => 0,
        'weautocreateusers' => 1,
        'firstnamefield'    => '',
        'surnamefield'      => '',
        'emailfield'        => ''
        );

    public static function has_config() {
        return false;
    }

    public static function get_config_options() {
        return array();
    }

    public static function has_instance_config() {
        return true;
    }

    public static function is_usable() {
        return extension_loaded('ldap');
    }

    public static function get_instance_config_options($institution, $instance = 0) {
        // list of user_type
        $utopt = array();
        $utopt['edir']       = 'Novell Edirectory';
        $utopt['rfc2307']    = 'posixAccount (rfc2307)';
        $utopt['rfc2307bis'] = 'posixAccount (rfc2307bis)';
        $utopt['samba']      = 'sambaSamAccount (v.3.0.7)';
        $utopt['ad']         = 'MS ActiveDirectory';
        $utopt['default']    = 'default';

        $yesnoopt = array('yes' => 'Yes', 'no' => 'No');

        $versionopt = array('2' => '2', '3' => '3');

        if ($instance > 0) {
            $default = get_record('auth_instance', 'id', $instance);
            if ($default == false) {
                throw new SystemException('Could not find data for auth instance ' . $instance);
            }
            $current_config = get_records_menu('auth_instance_config', 'instance', $instance, '', 'field, value');

            if ($current_config == false) {
                $current_config = array();
            }

            foreach (self::$default_config as $key => $value) {
                if (array_key_exists($key, $current_config)) {
                    self::$default_config[$key] = $current_config[$key];
                }
            }
        } else {
            $default = new stdClass();
            $default->instancename = '';
        }

        $elements = array(
            'instancename' => array(
                'type'  => 'text',
                'title' => get_string('authname', 'auth'),
                'rules' => array(
                    'required' => true,
                ),
                'defaultvalue' => $default->instancename,
            ),
            'instance' => array(
                'type'  => 'hidden',
                'value' => $instance,
            ),
            'institution' => array(
                'type'  => 'hidden',
                'value' => $institution,
            ),
            'authname' => array(
                'type'  => 'hidden',
                'value' => 'ldap',
            ),
            'host_url' => array(
                'type'  => 'text',
                'title' => get_string('hosturl', 'auth.ldap'),
                'rules' => array(
                    'required' => true,
                ),
                'defaultvalue' => self::$default_config['host_url'],
                'help'  => true,
            ),
            'contexts' => array(
                'type'  => 'text',
                'title' => get_string('contexts', 'auth.ldap'),
                'rules' => array(
                    'required' => true,
                ),
                'defaultvalue' => self::$default_config['contexts'],
                'help' => true,
            ),
            'user_type' => array(
                'type'    => 'select',
                'title'   => get_string('usertype', 'auth.ldap'),
                'options' => $utopt,
                'rules'   => array(
                    'required' => true,
                ),
                'defaultvalue' => self::$default_config['user_type'],
                'help'   => true
            ),
            'user_attribute' => array(
                'type'  => 'text',
                'title' => get_string('userattribute', 'auth.ldap'),
                'rules' => array(
                    'required' => true,
                ),
                'defaultvalue' => self::$default_config['user_attribute'],
                'help' => true,
            ),
            'search_sub' => array(
                'type'    => 'select',
                'title'   => get_string('searchsubcontexts', 'auth.ldap'),
                'options' => $yesnoopt,
                'rules'   => array(
                    'required' => true,
                ),
                'defaultvalue' => self::$default_config['search_sub'],
                'help' => true,
            ),
            'bind_dn' => array(
                'type'  => 'text',
                'title' => get_string('distinguishedname', 'auth.ldap'),
                'defaultvalue' => self::$default_config['bind_dn'],
                'help'  => true,
            ),
            'bind_pw' => array(
                'type'  => 'password',
                'title' => get_string('password', 'auth.ldap'),
                'defaultvalue' => self::$default_config['bind_pw'],
                'help'  => true,
            ),
            'version' => array(
                'type'  => 'select',
                'title' => get_string('ldapversion', 'auth.ldap'),
                'options' => $versionopt,
                'rules' => array(
                    'required' => true,
                ),
                'defaultvalue' => self::$default_config['version'],
                'help'  => true,
            ),
            'starttls' => array(
                'type'  => 'checkbox',
                'title' => get_string('starttls', 'auth.ldap'),
                'defaultvalue' => self::$default_config['starttls'],
            ),
            'updateuserinfoonlogin' => array(
                'type'  => 'checkbox',
                'title' => get_string('updateuserinfoonlogin', 'auth.ldap'),
                'description' => get_string('updateuserinfoonloginadnote', 'auth.ldap'),
                'defaultvalue' => self::$default_config['updateuserinfoonlogin'],
                'help'  => true,
            ),
            'weautocreateusers' => array(
                'type'  => 'checkbox',
                'title' => get_string('weautocreateusers', 'auth.ldap'),
                'defaultvalue' => self::$default_config['weautocreateusers'],
                'help'  => true,
            ),
            'firstnamefield' => array(
                'type'  => 'text',
                'title' => get_string('ldapfieldforfirstname', 'auth.ldap'),
                'defaultvalue' => self::$default_config['firstnamefield'],
                'help'  => true,
            ),
            'surnamefield' => array(
                'type'  => 'text',
                'title' => get_string('ldapfieldforsurname', 'auth.ldap'),
                'defaultvalue' => self::$default_config['surnamefield'],
                'help'  => true,
            ),
            'emailfield' => array(
                'type'  => 'text',
                'title' => get_string('ldapfieldforemail', 'auth.ldap'),
                'defaultvalue' => self::$default_config['emailfield'],
                'help' => true,
            ),
        );

        return array(
            'elements' => $elements,
            'renderer' => 'table'
        );
    }

    public static function save_config_options($values, $form) {

        $authinstance = new stdClass();

        if ($values['instance'] > 0) {
            $values['create'] = false;
            $current = get_records_assoc('auth_instance_config', 'instance', $values['instance'], '', 'field, value');
            $authinstance->id = $values['instance'];
        }
        else {
            $values['create'] = true;

            // Get the auth instance with the highest priority number (which is
            // the instance with the lowest priority).
            // TODO: rethink 'priority' as a fieldname... it's backwards!!
            $lastinstance = get_records_array('auth_instance', 'institution', $values['institution'], 'priority DESC', '*', '0', '1');

            if ($lastinstance == false) {
                $authinstance->priority = 0;
            }
            else {
                $authinstance->priority = $lastinstance[0]->priority + 1;
            }
        }

        $authinstance->instancename = $values['instancename'];
        $authinstance->institution  = $values['institution'];
        $authinstance->authname     = $values['authname'];

        if ($values['create']) {
            $values['instance'] = insert_record('auth_instance', $authinstance, 'id', true);
        }
        else {
            update_record('auth_instance', $authinstance, array('id' => $values['instance']));
        }

        if (empty($current)) {
            $current = array();
        }

        self::$default_config =   array('host_url' => $values['host_url'],
                                        'contexts' => $values['contexts'],
                                        'user_type' => $values['user_type'],
                                        'user_attribute' => $values['user_attribute'],
                                        'search_sub' => $values['search_sub'],
                                        'bind_dn' => $values['bind_dn'],
                                        'bind_pw' => $values['bind_pw'],
                                        'version' => $values['version'],
                                        'starttls' => $values['starttls'],
                                        'updateuserinfoonlogin' => $values['updateuserinfoonlogin'],
                                        'weautocreateusers' => $values['weautocreateusers'],
                                        'firstnamefield' => $values['firstnamefield'],
                                        'surnamefield' => $values['surnamefield'],
                                        'emailfield' => $values['emailfield']
                                        );

        foreach(self::$default_config as $field => $value) {
            $record = new stdClass();
            $record->instance = $values['instance'];
            $record->field    = $field;
            $record->value    = $value;

            if ($values['create'] || !array_key_exists($field, $current)) {
                insert_record('auth_instance_config', $record);
            }
            else {
                update_record('auth_instance_config', $record, array('instance' => $values['instance'], 'field' => $field));
            }
        }

        return $values;
    }


}
