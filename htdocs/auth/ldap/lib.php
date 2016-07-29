<?php
/**
 *
 * @package    mahara
 * @subpackage auth-ldap
 * @author     Howard Miller <howard.miller@udcf.gla.ac.uk>
 * @author     Patrick Pollet <pp@patrickpollet.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  (C) 2008 Howard Miller <howard.miller@udcf.gla.ac.uk>
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) portions from Moodle, (C) Martin Dougiamas http://dougiamas.com
 * @copyright  (C) 2011 INSA de Lyon France
 */

defined('INTERNAL') || die();
require_once(get_config('docroot') . 'auth/lib.php');

define ('AUTH_LDAP_SUSPENDED_REASON', 'auth/ldap sync');

/**
 * Authenticates users with Lightweight Directory Access Protocol
 */
class AuthLdap extends Auth {

    /**
     * avoid infinite loop with nested groups in 'funny' directories
     * @var array
     */
    private $anti_recursion_array;
    private $connection = false;

    public function __construct($id = null) {
        $this->connection = false;

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
        $this->config['studentidfield'] = '';
        $this->config['preferrednamefield'] = '';

        $this->config['syncgroupsgroupattribute'] = 'cn';
        $this->config['syncgroupsgroupclass'] = 'groupOfUniqueNames';
        //argh phpldap convert uniqueMember to lowercase array keys when returning the list of members  ...
        $this->config['syncgroupsmemberattribute'] = strtolower('uniqueMember');
        $this->config['syncgroupsmemberattributeisdn'] = 1;
        $this->config['syncgroupsnestedgroups'] = false;
        $this->config['syncgroupsusergroupnames'] = array();

        /**
         * cache for found groups dn
         * used for nested groups processing
         */
        $this->config['groups_dn_cache'] = array();
        $this->anti_recursion_array = array();

        if (!empty($id)) {
            $this->init($id);
        }
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

        // phpldap converts uniqueMember to lowercase array keys when returning list of members
        if (isset($this->config['syncgroupsmemberattribute'])) {
            $this->config['syncgroupsmemberattribute'] = strtolower($this->config['syncgroupsmemberattribute']);
        }

        // These fields are comma-separated values. We'll convert them into arrays now.
        $lists = array(
                'syncgroupsexcludelist',
                'syncgroupsincludelist',
                'syncgroupscontext',
                'syncgroupsusergroupnames'
        );
        foreach ($lists as $listkey) {
            if (isset($this->config[$listkey]) && !is_array($this->config[$listkey]) && $this->config[$listkey] !== '') {
                $this->config[$listkey] = preg_split('/\s*,\s*/', trim($this->config[$listkey]));
            }
        }

        return $this->ready;
    }

    /**
     * Attempt to authenticate user
     *
     * @param string $user     The user record to authenticate with
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
                $this->ldap_close($ldapconnection);
                return false;
            }

            // Try to bind with current username and password
            $ldap_login = @ldap_bind($ldapconnection, $ldap_user_dn, $password);
            $this->ldap_close($ldapconnection);
            if ($ldap_login) {
                if ($user->id && $update) {
                    // Define ldap attributes
                    $ldapattributes = array();
                    $ldapattributes['firstname'] = $this->config['firstnamefield'];
                    $ldapattributes['lastname'] = $this->config['surnamefield'];
                    $ldapattributes['email'] = $this->config['emailfield'];
                    $ldapattributes['studentid'] = $this->config['studentidfield'];
                    $ldapattributes['preferredname'] = $this->config['preferrednamefield'];

                    // Retrieve information of user from LDAP
                    $ldapdetails = $this->get_userinfo_ldap($username, $ldapattributes);

                    // Match database and ldap entries and update in database if required
                    $fieldstoimport = array_keys($ldapattributes);
                    foreach ($fieldstoimport as $field) {
                        if (!isset($ldapdetails[$field])) {
                            continue;
                        }
                        $sanitizer = "sanitize_$field";
                        $ldapdetails[$field] = $sanitizer($ldapdetails[$field]);
                        if (!empty($ldapdetails[$field]) && ($user->$field != $ldapdetails[$field])) {
                            $user->$field = $ldapdetails[$field];
                            set_profile_field($user->id, $field, $ldapdetails[$field]);
                            if (('studentid' == $field) && ('mahara' != $this->institution)) {
                                // studentid is specific for the institution, so store it there too.
                                $dataobject = array(
                                    'usr' => $user->id,
                                    'institution' => $this->institution,
                                    'ctime' => db_format_timestamp(time()),
                                    'studentid' => $user->studentid,
                                );
                                $whereobject = $dataobject;
                                unset($whereobject['ctime']);
                                unset($whereobject['studentid']);
                                ensure_record_exists('usr_institution', $whereobject, $dataobject);
                                unset($dataobject);
                                unset($whereobject);
                            }
                        }
                    }
                }
                return true;
            }
        }
        else {
            $this->ldap_close($ldapconnection);
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

            // If ldap_connect's first argument has a protocol (ldap://, ldaps://, ldapi://, etc)
            // in front of it, it ignores the second argument.
            if (preg_match('#^[a-z]+://#i', $server)) {
                $connresult = ldap_connect($server);
            }
            // ... but if ldap_connect's first argument doesn't have a protocol, the port number
            // needs to be sent separately, as the second argument
            else {
                $lastcolon = strrpos($server, ":");
                if ($lastcolon !== FALSE && preg_match('/^([0-9]+)$/', $port = substr($server, $lastcolon + 1))) {
                    $server = substr($server, 0, $lastcolon);
                    $connresult = ldap_connect($server, $port);
                }
                else {
                    $connresult = ldap_connect($server);
                }
            }
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

    private function ldap_close($connection) {
        return @ldap_unbind($connection);
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
            if (!$ldap_result) {
                return false;
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
        $attributes['lastname'] = $this->config['surnamefield' ];
        $attributes['email'] = $this->config['emailfield'];
        $attributes['studentid'] = $this->config['studentidfield'];
        $attributes['preferredname'] = $this->config['preferrednamefield'];

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
                // ldap_get_entries() makes the ldap attributes lowercase
                $value = strtolower($value);
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

        $this->ldap_close($ldapconnection);
        return $result;
    }

    /**
     * Return entries from ldap
     *
     * Returns values like ldap_get_entries but is binary compatible and return
     * all attributes as array
     *
     * Turns all the LDAP attributes to lowercase in order to make things non-case-sensitive
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
                    $fresult[$i][strtolower($attributes[$j])] = $values;
                }
                else {
                    $fresult[$i][strtolower($attributes[$j])] = array($values);
                }
            }
            $i++;
        }
        while ($entry = @ldap_next_entry($conn, $entry));
        // We're done
        return $fresult;
    }

    /**
     * this class allows to change default config option read from the database
     * @param $key
     * @param $value
     */
    public function set_config($key, $value) {
        $this->config[$key] = $value;
    }

    /**
     * @param string value The config item to return, or NULL to return the whole config array
     * @return array|mixed
     */
    public function get_config($value = null) {
        if ($value == null) {
            return $this->config;
        }
        else {
            if (array_key_exists($value, $this->config)) {
                return $this->config[$value];
            }
            else {
                return null;
            }
        }
    }

    /**
     * return all groups declared in LDAP
     * DOES NOT SUPPORT PAGED RESULTS if more than 1000 (AD)
     * @param string filter
     * @return array of strings
     */
    private function ldap_get_grouplist($filter = "*", $searchsub) {
        /// returns all groups from ldap servers
        global $CFG;

        // print_string('connectingldap', 'auth_ldap');
        $ldapconnection = $this->ldap_connect();

        $fresult = array();

        if ($filter == "*") {
            $filter = "(&(" . $this->config['syncgroupsgroupattribute'] . "=*)(objectclass=" . $this->config['syncgroupsgroupclass'] . "))";
        }

        $contexts = $this->get_group_contexts();

        foreach ($contexts as $context) {
            $context = trim($context);
            if (empty ($context)) {
                continue;
            }

            if ($searchsub == 'yes') {
                //use ldap_search to find first group from subtree
                $ldap_result = ldap_search($ldapconnection, $context, $filter, array(
                        $this->config['syncgroupsgroupattribute']
                ));
            }
            else {
                //search only in this context
                $ldap_result = ldap_list($ldapconnection, $context, $filter, array(
                        $this->config['syncgroupsgroupattribute']
                ));
            }

            if (!$ldap_result) {
                log_warn("ldap_list() errored out!");
                continue;
            }

            $groups = ldap_get_entries($ldapconnection, $ldap_result);

            //add found groups to list
            for ($i = 0; $i < count($groups) - 1; $i++) {
                // ldap_get_entries() converts the array keys in the result to lowercase
                $group_cn = ($groups[$i][strtolower($this->config['syncgroupsgroupattribute'])][0]);
                array_push($fresult, $group_cn );

                // keep the dn/cn in cache for later processing of nested groups
                if ($this->config['syncgroupsnestedgroups']) {
                    $group_dn = $groups[$i]['dn'];
                    $this->config['groups_dn_cache'][$group_dn] = $group_cn;
                }
            }
        }
        $this->ldap_close($ldapconnection);
        return $fresult;
    }

    /**
     * Search for group members on an OpenLDAP directory
     * @param string $group
     * @return multitype:|multitype:Ambigous <string, boolean, string, unknown>
     */
    private function ldap_get_group_members_rfc($groupfilter) {
        global $CFG;

        $ret = array();
        $ldapconnection = $this->ldap_connect();

        if (!$ldapconnection) {
            return $ret;
        }

        $queryg = "(&({$this->config['syncgroupsgroupattribute']}=" . $this->filter_addslashes(trim($groupfilter)) . ")(objectClass={$this->config['syncgroupsgroupclass']}))";
        $contexts = $this->get_group_contexts();

        foreach ($contexts as $context) {
            $context = trim($context);
            if (empty ($context)) {
                continue;
            }

            if ($this->config['syncgroupssearchsub'] == 'yes') {
                $resultg = ldap_search($ldapconnection, $context, $queryg);
            }
            else {
                $resultg = ldap_list($ldapconnection, $context, $queryg);
            }

            if ($resultg !== false && ldap_count_entries($ldapconnection, $resultg)) {
                $group = ldap_get_entries($ldapconnection, $resultg);

                for ($g = 0; $g < (count($group[0][strtolower($this->config['syncgroupsmemberattribute'])]) - 1); $g++) {

                    $member = trim($group[0][strtolower($this->config['syncgroupsmemberattribute'])][$g]);
                    if (empty($member)) {
                        continue;
                    }
                    if (!$this->config['syncgroupsmemberattributeisdn']) {
                        // member attribute is the username, not a DN
                        $ret[] = $member;
                    }
                    else {
                        // member attribute is a DN.
                        // Check to see if the member is actually a nested group
                        if ($this->config['syncgroupsnestedgroups'] && ($group_cn = $this->is_ldap_group($member))) {
                            // in case of funny directory where groups are member of groups
                            if (array_key_exists($member, $this->anti_recursion_array)) {
                                unset($this->anti_recursion_array[$member]);
                                continue;
                            }

                            //recursive call
                            // TODO: Better to set the recursion end point by passing this in as a parameter...
                            $this->anti_recursion_array[$member] = 1;
                            $tmp = $this->ldap_get_group_members_rfc($group_cn);
                            unset($this->anti_recursion_array[$member]);
                            $ret = array_merge($ret,$tmp);
                        }
                        // The "member" node is a member DN. Extract the username and return it.
                        else {
                            $member = $this->get_username_from_dn($member);
                            if ($member) {
                                $ret[] = $member;
                            }
                        }
                    }
                }
            }
        }

        $this->ldap_close($ldapconnection);
        return $ret;
    }

    /**
     * Specific search for Active Directory problems if more than 999 members
     * TODO: Reduce redundancy between this and ldap_get_group_members_rfc
     * @param string $group
     * @return multitype:|multitype:Ambigous <string, boolean, string, unknown>
     */
    private function ldap_get_group_members_ad($groupfilter) {
        global $CFG;

        $ret = array();
        $ldapconnection = $this->ldap_connect();
        if (!$ldapconnection) {
            return $ret;
        }

        $queryg = "(&({$this->config['syncgroupsgroupattribute']}=" . $this->filter_addslashes(trim($groupfilter)) . ")(objectClass={$this->config['syncgroupsgroupclass']}))";

        // The paging increment
        $size = 999;

        $contexts = $this->get_group_contexts();

        foreach ($contexts as $context) {
            $context = trim($context);
            if (empty ($context)) {
                continue;
            }
            $start = 0;
            $end = $size;
            $fini = false;

            while (!$fini) {
                //page the search by increments of $size
                $attribut = $this->config['syncgroupsmemberattribute'] . ";range=" . $start . '-' . $end;

                if ($this->config[$this->config['syncgroupssearchsub']] == 'yes') {
                    $resultg = ldap_search($ldapconnection, $context, $queryg, array($attribut));
                }
                else {
                    $resultg = ldap_list($ldapconnection, $context, $queryg, array($attribut));
                }

                if ($resultg !== false && ldap_count_entries($ldapconnection, $resultg)) {
                    $groupe = ldap_get_entries($ldapconnection, $resultg);

                    // On the final page, AD returns "member;range=number-*" !!!
                    if (empty($groupe[0][strtolower($attribut)])) {
                        $attribut = $this->config['syncgroupsmemberattribute'] . ";range=" . $start . '-*';
                        $fini = true;
                    }

                    for ($g = 0; $g < (count($groupe[0][strtolower($attribut)]) - 1); $g++) {
                        $membre = trim($groupe[0][strtolower($this->config['syncgroupsmemberattribute'])][$g]);
                        if (empty($membre)) {
                            continue;
                        }
                        if (!$this->config['syncgroupsmemberattributeisdn']) {
                            $ret[] = $membre;
                        }
                        else {
                            //rev 1.2 nested groups
                            if ($this->config['syncgroupsnestedgroups'] && ($group_cn = $this->is_ldap_group($membre))) {
                                // in case of funny directory where groups are member of groups
                                if (array_key_exists($membre,$this->anti_recursion_array)) {
                                    unset($this->anti_recursion_array[$membre]);
                                    continue;
                                }
                                //recursive call
                                $this->anti_recursion_array[$membre] = 1;
                                $tmp = $this->ldap_get_group_members_ad ($group_cn);
                                unset($this->anti_recursion_array[$membre]);
                                $ret = array_merge($ret,$tmp);
                            }
                            else {
                                $membre = $this->get_username_from_dn($membre);
                                if ($membre) {
                                    $ret[] = $membre;
                                }
                            }
                        }
                    }
                }
                else {
                    $fini = true;
                }
                $start = $start + $size;
                $end = $end + $size;
            }
        }
        $this->ldap_close($ldapconnection);
        return $ret;
    }

    /**
     * should return a Mahara account from its LDAP dn
     * split the $dn and if naming attribute = Mahara user_attribute returns it
     * otherwise perform a LDAP search
     * @param string $dn    uid=jdoe,ou=people,dc=... or cn=john doe,ou=people,dc=...
     * @return string Mahara username or false
     */
    private function get_username_from_dn($dn) {
        global $CFG;
        $dn_tmp1 = explode(",", $dn);
        if (count($dn_tmp1) > 1) {
            // Normally the first element is cn=..., or uid=...
            // try a shortcut if the naming attribute is the same
            // unless forced by a 'debug' configuration flag
            $dn_tmp2 = explode("=", trim($dn_tmp1[0], 2));

            if ($dn_tmp2[0] == $this->config['user_attribute']) {
                return $dn_tmp2[1];
            }
            else {
                // case when user's DN is NOT xx=maharausername,ou=xxxx,dc=yyyy
                // quite common with AD where DN is cn=user fullname,ou=xxxx
                // we must do another LDAP search to retrieve Mahara username from LDAP
                // since we call ldap_get_users, we do not support groups whithin group
                // (usually added as cn=groupxxxx,ou=....)

                $filter = $dn_tmp2[0] . '=' . $this->filter_addslashes($dn_tmp2[1]);
                $matchings = $this->ldap_get_users($filter);
                // return the FIRST entry found
                if (empty($matchings)) {
                    return false;
                }
                if (count($matchings) > 1) {
                    return false;
                }
                return $matchings[0];
            }

        }
        else {
            // If there was only one element returned from explode, then obviously the dn
            // was bad
            return false;
        }
    }

    /**
     * search the group cn in group names cache
     * this is definitively faster than searching AGAIN LDAP for this dn with class=group...
     * @param string $dn  the group DN
     * @return string the group CN or false
     */
    private function is_ldap_group($dn) {
        if (empty($this->config['syncgroupsnestedgroups'])) {
            return false; // not supported by config
        }
        return !empty($this->config['groups_dn_cache'][$dn]) ? $this->config['groups_dn_cache'][$dn] : false;
    }

    /**
     * We treate Active Directory groups slightly differently because it returns
     * a non-standard response if there are more 1000 members or more.
     *
     * @param string $group
     * @return array an array of username indexed by Mahara user id
     */
    public function ldap_get_group_members($group) {
        global $DB;
        if ($this->config['user_type'] == "ad") {
            $members = $this->ldap_get_group_members_ad($group);
        }
        else {
            $members = $this->ldap_get_group_members_rfc($group);
        }

        return $members;
    }

    /**
     * returns an array of usernames from al LDAP directory
     * DO NOT USE ANYMORE for synching users Not scalable
     * used for synching Mahara's groups with some LDAP attribute
     * searching patameters are defined in configuration
     * @param string $extrafilter  if present returns only users having some values in some LDAP attribute
     * @return array  of strings
     */
    public function ldap_get_users($extrafilter = '') {
        global $CFG;

        $ret = array();
        $ldapconnection = $this->ldap_connect();
        if (!$ldapconnection) {
            return $ret;
        }

        $filter = "(" . $this->config['user_attribute'] . "=*)";
        if (!empty($this->config['objectclass'])) {
            $filter .= "&(" . $this->config['objectclass'] . "))";
        }
        if ($extrafilter) {
            $filter = "(&$filter($extrafilter))";
        }

        // get all contexts and look for first matching user
        $ldap_contexts = explode(";", $this->config['contexts']);

        foreach ($ldap_contexts as $context) {
            $context = trim($context);
            if (empty($context)) {
                continue;
            }

            if ($this->config['search_sub'] == 'yes') {
                // use ldap_search to find first user from subtree
                $ldap_result = ldap_search($ldapconnection, $context, $filter, array($this->config['user_attribute']));

            }
            else {
                // search only in this context
                $ldap_result = ldap_list($ldapconnection, $context, $filter, array($this->config['user_attribute']));
            }
            if ($ldap_result === false) {
                continue;
            }

            if ($entry = ldap_first_entry($ldapconnection, $ldap_result)) {
                do {
                    $value = ldap_get_values_len($ldapconnection, $entry, $this->config['user_attribute']);
                    $value = $value[0];
                    array_push($ret, $value);

                } while ($entry = ldap_next_entry($ldapconnection, $entry));
            }
            ldap_free_result($ldap_result); // free mem

        }
        $this->ldap_close($ldapconnection);
        return $ret;
    }

    /**
     * fill a database table with usernames from al LDAP directory
     * searching parameters are defined in configuration
     * DOES NOT SUPPORT PAGED RESULTS if more than a 1000 (AD)
     * @param string tablename
     * @param string columnname
     * @param string $extrafilter  if present returns only users having some values in some LDAP attribute
     * @return integer (nb of records added) or false in case of error
     */
    private function ldap_get_users_scalable($tablename, $columnname='username', $extrafilter = '') {
        global $CFG;

        execute_sql('TRUNCATE TABLE ' . $tablename);

        $ldapconnection = $this->ldap_connect();
        if (!$ldapconnection) {
            log_warn("can't connect the LDAP server.\n");
            return false;
        }

        $filter = "(" . $this->config['user_attribute'] . "=*)";
        if (!empty($this->config['objectclass'])) {
            $filter .= "&(" . $this->config['objectclass'] . "))";
        }
        if ($extrafilter) {
            $filter = "(&$filter($extrafilter))";
        }

        // get all contexts and look for first matching user
        $ldap_contexts = explode(";", $this->config['contexts']);
        $ldapuserfields = $this->get_ldap_user_fields();
        $fieldstoimport = array_values($ldapuserfields);
        $fieldstoimport[] = $this->config['user_attribute'];
        // Lowercase all the fields to avoid case mismatch issues
        $ldapuserfields = array_map('strtolower', $ldapuserfields);
        log_info('retrieving these fields: ' . implode(',', $fieldstoimport) . "\n");

        $nbadded = 0;

        foreach ($ldap_contexts as $context) {
            $context = trim($context);
            if (empty($context)) {
                continue;
            }

            if ($this->config['search_sub'] == 'yes') {
                // use ldap_search to find first user from subtree
                $ldap_result = ldap_search($ldapconnection, $context, $filter, $fieldstoimport);
            }
            else {
                // search only in this context
                $ldap_result = ldap_list($ldapconnection, $context, $filter, $fieldstoimport);
            }
            if ($ldap_result === false) {
                // Do not continue.
                // Otherwise, the sync will see that 0 users
                // should be synced -> and we can incorrectly delete
                // or suspend users.
                log_warn("can't contact the LDAP server.\n");
                ldap_free_result($ldap_result); // free mem
                $this->ldap_close($ldapconnection);
                return false;
            }

            if ($entry = ldap_first_entry($ldapconnection, $ldap_result)) {
                do {
                    $value = ldap_get_values_len($ldapconnection, $entry, $this->config['user_attribute']);
                    $value = $value[0];
                    // let's convert all keys to lowercase, to avoid case sensitivity issues
                    $ldaprec = array_change_key_case(ldap_get_attributes($ldapconnection, $entry));
                    $todb = new stdClass();
                    $todb->$columnname = $value;
                    foreach ($ldapuserfields as $dbfield=>$ldapfield) {
                        if (array_key_exists($ldapfield, $ldaprec)) {
                            $todb->$dbfield = $ldaprec[$ldapfield][0];
                        }
                        else {
                            log_warn("Ldap record contained no {$ldapfield} field to map to DB {$dbfield}");
                        }
                    }

                    insert_record(
                        $tablename,
                        $todb,
                        false,
                        false
                    );
                    $nbadded++;
                    if ($nbadded % 100 == 0) {
                        echo '.';
                    }
                } while ($entry = ldap_next_entry($ldapconnection, $entry));
                echo "\n";
            }
            ldap_free_result($ldap_result); // free mem

        }
        $this->ldap_close($ldapconnection);
        return $nbadded;
    }

    /**
     *
     * returns the distinct values of the target LDAP attribute
     * these will be the names of the synched Mahara groups
     * @returns array of string
     */
    public function get_attribute_distinct_values($searchsub) {

        global $CFG, $DB;
        // only these groups will be synched
        if (!empty($this->config['syncgroupsusergroupnames'] )) {
            return $this->config['syncgroupsusergroupnames'] ;
        }

        //build a filter to fetch all users having something in the target LDAP attribute
        $filter = '(' . $this->config['user_attribute'] . '=*)';
        if (!empty($this->config['objectclass'])) {
            $filter .= "&(" . $this->config['objectclass'] . "))";
        }
        $filter = '(&' . $filter . '(' . $this->config['syncgroupsuserattribute'] . '=*))';

        $ldapconnection = $this->ldap_connect();
        $ldap_contexts = explode(";", $this->config['contexts']);
        $matchings = array();

        foreach ($ldap_contexts as $context) {
            $context = trim($context);
            if (empty($context)) {
                continue;
            }

            if ($searchsub == 'yes') {
                // Use ldap_search to find first user from subtree
                $ldap_result = ldap_search($ldapconnection, $context,
                        $filter,
                        array($this->config['syncgroupsuserattribute']));
            }
            else {
                // Search only in this context
                $ldap_result = ldap_list($ldapconnection, $context,
                        $filter,
                        array($this->config['syncgroupsuserattribute']));
            }

            if (!$ldap_result) {
                continue;
            }

            // this API function returns all attributes as an array
            // wether they are single or multiple
            $users = $this->ldap_get_entries($ldapconnection, $ldap_result);

            // Add found DISTINCT values to list
            for ($i = 0; $i < count($users); $i++) {
                $count = $users[$i][strtolower($this->config['syncgroupsuserattribute'])]['count'];
                for ($j = 0; $j < $count; $j++) {
                    $value = $users[$i][strtolower($this->config['syncgroupsuserattribute'])][$j];
                    if (!in_array($value, $matchings)) {
                        array_push($matchings,$value);
                    }
                }
            }
        }

        $this->ldap_close($ldapconnection);
        return $matchings;
    }

    public function get_users_having_attribute_value ($attributevalue) {
        global $CFG, $DB;
        //build a filter

        $filter = $this->config['syncgroupsuserattribute'].'='.
                $this->filter_addslashes($attributevalue);

        // call Moodle ldap_get_userlist that return it as an array with user attributes names
        $matchings = $this->ldap_get_users($filter);
        // return the FIRST entry found
        if (empty($matchings)) {
            return array();
        }

        return $matchings;
    }


    /**
     * Attemp to synchronize Users in Mahara with Users in the LDAP server
     *
     * @param boolean $dryrun dummy execution. Do not perform any database operations
     * @return boolean
     */
    public function sync_users($dryrun = false) {
        global $CFG;
        require_once(get_config('docroot') . 'lib/ddl.php');
        require_once(get_config('docroot') . 'lib/institution.php');

        log_info('---------- started usersync for instance ' . $this->instanceid . ' at ' . date('r', time()) . ' ----------');

        // If they haven't activated the cron, return
        if (!$this->get_config('syncuserscron')) {
            log_info('not set to sync users, so exiting');
            return true;
        }

        // Create a temp table to store the users, for better performance
        $temptable = new XMLDBTable('auth_ldap_extusers_temp');
        $temptable->addFieldInfo('extusername', XMLDB_TYPE_CHAR, 64, null, false);
        $temptable->addFieldInfo('firstname', XMLDB_TYPE_TEXT);
        $temptable->addFieldInfo('lastname', XMLDB_TYPE_TEXT);
        $temptable->addFieldInfo('email', XMLDB_TYPE_CHAR, 255);
        $temptable->addFieldInfo('studentid', XMLDB_TYPE_TEXT);
        $temptable->addFieldInfo('preferredname', XMLDB_TYPE_TEXT);
        $temptable->addKeyInfo('extusers', XMLDB_KEY_PRIMARY, array('extusername'));
        $tablecreated = create_temp_table($temptable, false, true);
        if (!$tablecreated) {
            log_warn('Could not create temp table auth_ldap_extusers_temp', false);
            return false;
        }

        $extrafilterattribute = $this->get_config('syncusersextrafilterattribute');
        $doupdate = $this->get_config('syncusersupdate');
        $docreate = $this->get_config('syncuserscreate');
        $tousersgonefromldap = $this->get_config('syncusersgonefromldap');

        $dodelete = false;
        $dosuspend = false;
        switch ($tousersgonefromldap) {
            case 'delete':
                $dodelete = true;
                break;
            case 'suspend':
                $dosuspend = true;
                break;
        }

        if (get_config('auth_ldap_debug_sync_cron')) {
            log_debug("config. LDAP : ");
            var_dump($this->config);
        }

        // fetch ldap users having the filter attribute on (caution maybe mutlivalued
        // do it on a scalable version by keeping the LDAP users names in a temporary table
        $nbldapusers = $this->ldap_get_users_scalable('auth_ldap_extusers_temp', 'extusername', $extrafilterattribute);
        if ($nbldapusers === false) {
            // Failure to connect to LDAP.
            log_info("will not continue with LDAP user sync.\n");
            return false;
        }
        log_info('LDAP users found : ' . $nbldapusers);

        try {
            $nbupdated = $nbcreated = $nbsuspended = $nbdeleted = $nbignored = $nbpresents = $nbunsuspended = $nberrors = 0;

            // Define ldap attributes in user update
            $ldapattributes = $this->get_ldap_user_fields();
            // Match database and ldap entries and update in database if required
            $fieldstoimport = array_keys($ldapattributes);

            // we fetch only Mahara users of this institution concerned by this authinstance (either cas or ldap)
            // and get also their suspended status since we may have to unsuspend them
            // this search cannot be done by a call to get_institutional_admin_search_results
            // that does not support searching by auth instance id and do not return suspended status
            // and is not suitable for a massive number of users

            if (!$doupdate) {
                log_info('user auto-update disabled');
            }
            else {
                // users to update (known both in LDAP and Mahara usr table)
                $sql = "
                    select
                        u.id as id,
                        u.username as username,
                        u.suspendedreason as suspendedreason,
                        u.firstname as dbfirstname,
                        u.lastname as dblastname,
                        u.email as dbemail,
                        u.studentid as dbstudentid,
                        u.preferredname as dbpreferredname,
                        e.firstname as ldapfirstname,
                        e.lastname as ldaplastname,
                        e.email as ldapemail,
                        e.studentid as ldapstudentid,
                        e.preferredname as ldappreferredname
                    from
                        {usr} u
                        inner join {auth_ldap_extusers_temp} e
                            on u.username = e.extusername
                    where
                        u.deleted = 0
                        and u.authinstance = ?
                    order by u.username
                ";

                $rs = get_recordset_sql($sql, array($this->instanceid));
                log_info($rs->RecordCount() . ' users known to Mahara ');
                while ($record = $rs->FetchRow()) {
                    $nbpresents++;
                    $ldapusername = $record['username'];
                    $updated = false;

                    foreach ($fieldstoimport as $field) {
                        $ldapfield = "ldap$field";
                        $dbfield = "db$field";
                        $sanitizer = "sanitize_$field";
                        $record[$ldapfield] = $sanitizer($record[$ldapfield]);
                        if ($record[$ldapfield] != '' && ($record[$dbfield] != $record[$ldapfield])) {
                            $updated = true;
                            if (!$dryrun) {
                                set_profile_field($record['id'], $field, $record[$ldapfield]);
                            }
                        }
                    }
                    if ($updated) {
                        log_debug('updating user ' . $ldapusername);
                    }
                    else {
                        log_debug('no change for user ' . $ldapusername);
                    }

                    if (!$dryrun) {
                        if (!empty($record['ldapstudentid'])) { // caution may be missing ?
                            set_field('usr_institution', 'studentid', $record['ldapstudentid'], 'usr', $record['id'], 'institution', $this->institution);
                        }
                    }

                    unset($ldapdetails);
                    $nbupdated++;

                    //unsuspend if was suspended by me at a previous run
                    if (!empty($record['suspendedreason']) && strstr($record['suspendedreason'], AUTH_LDAP_SUSPENDED_REASON) !== false) {
                        log_info('unsuspending user ' . $ldapusername);

                        if (!$dryrun) {
                            unsuspend_user($record['id']);
                        }
                        $nbunsuspended++;
                    }
                }
            }

            if (!$dosuspend && !$dodelete) {
                log_info('user auto-suspend/delete disabled');
            }
            else {
                //users to delete /suspend
                $sql = "
                    SELECT u.id, u.username, u.suspendedreason
                    FROM
                        {usr} u
                        LEFT JOIN {auth_ldap_extusers_temp} e
                        ON e.extusername = u.username
                    WHERE
                        u.authinstance = ?
                        AND u.deleted = 0
                        AND e.extusername IS NULL
                    ORDER BY u.username ASC";
                $rs = get_recordset_sql($sql, array($this->instanceid));
                log_info($rs->RecordCount() . ' users no longer in LDAP ');

                while ($record = $rs->FetchRow()) {
                    if ($dosuspend) {
                        if (!$record['suspendedreason']) { //if not already suspended for any reason (me or some manual operation)
                            log_info('suspending user ' . $record['username']);
                            if (!$dryrun) {
                                suspend_user($record['id'], AUTH_LDAP_SUSPENDED_REASON . ' ' . time() . ' (' . format_date(time()) . ')');
                            }
                            $nbsuspended++;
                        }
                        else {
                            log_debug('user ' . $record['username'] . ' already suspended by ' . $record['suspendedreason']);
                        }
                    }
                    else if ($dodelete) {
                        log_info('deleting user ' . $record['username']);
                        if (!$dryrun) {
                            delete_user($record['id']);
                        }
                        $nbdeleted++;
                    }
                    else {
                        // nothing to do
                        log_debug('ignoring user ' . $record['username']);
                        $nbignored++;
                    }
                }
            }

            if (!$docreate) {
                log_info('user auto-creation disabled');
            }
            else {
                // users to create
                $sql = '
                        SELECT
                            e.extusername,
                            e.firstname,
                            e.lastname,
                            e.email,
                            e.studentid,
                            e.preferredname
                        FROM
                            {auth_ldap_extusers_temp} e
                            LEFT JOIN {usr} u
                            ON e.extusername = u.username
                        WHERE u.id IS NULL
                        ORDER BY e.extusername';

                $rs = get_recordset_sql($sql);
                log_info($rs->RecordCount() . ' LDAP users unknown to Mahara  ');
                while ($record = $rs->FetchRow()) {
                    $ldapusername = $record['extusername'];
                    log_info('creating user ' . $ldapusername);
                    // Retrieve information of user from LDAP
                    $todb = new stdClass();
                    $todb->username = $ldapusername; //not returned by LDAP
                    $todb->authinstance = $this->instanceid;
                    $todb->password = '';
                    foreach($fieldstoimport as $field) {
                        $todb->$field = $record[$field];
                    }
                    if (get_config('auth_ldap_debug_sync_cron')) {
                        log_debug("creation de ");
                        var_dump($todb);
                    }
                    //check for used email
                    if (
                            ($d1 = get_record('usr', 'email', $todb->email))
                            ||
                            ($d2 = record_exists('artefact_internal_profile_email', 'email', $todb->email))
                    ) {
                        if (empty($d1)) {
                            $d1 = get_record('usr', 'id', $d2->owner);
                        }
                        if (get_config('auth_ldap_debug_sync_cron')) {
                            log_debug("collision email ");
                            var_dump($d1);
                        }
                        log_warn(get_string('emailalreadytaken', 'auth.internal') .' '. $d1->username . ' '.$todb->email);
                        $nberrors ++;
                    }
                    else {
                        if (!$dryrun) {
                            create_user($todb, array(), $this->institution);
                        }
                        $nbcreated++;
                    }
                    unset ($todb);
                }
            }
        }
        catch (Exception $e) {
            log_info("LDAP (users:$nbpresents) (updated:$nbupdated) (unsuspended:$nbunsuspended) (created:$nbcreated) (suspended:$nbsuspended) (deleted:$nbdeleted) (ignored:$nbignored) (errors:$nberrors)");
            throw $e;
        }

        log_info("LDAP (users:$nbpresents) (updated:$nbupdated) (unsuspended:$nbunsuspended) (created:$nbcreated) (suspended:$nbsuspended) (deleted:$nbdeleted) (ignored:$nbignored) (errors:$nberrors)");
        log_info('---------- ended at ' . date('r', time()) . ' ----------');
        return true;
    }

    /**
     * synchronize Mahara's groups with groups defined on a LDAP server
     *
     * @param boolean $dryrun dummy execution. Do not perform any database operations
     * @return boolean
     */
    function sync_groups($dryrun = false) {
        global $USER;
        log_info('---------- started groupsync auth instance ' . $this->instanceid . ' at ' . date('r', time()) . ' ----------');

        if (!$this->get_config('syncgroupscron')) {
            log_info('Not set to sync groups, so exiting');
            return true;
        }

        // We need to tell the session that we are the admin user, so that we have permission to manipulate groups
        $USER->reanimate(1, 1);

        $syncbyattribute = $this->get_config('syncgroupsbyuserfield') && $this->get_config('syncgroupsgroupattribute');
        $syncbyclass = $this->get_config('syncgroupsbyclass') && $this->get_config('syncgroupsgroupclass')
                && $this->get_config('syncgroupsgroupattribute') && $this->get_config('syncgroupsmemberattribute');

        $excludelist = $this->get_config('syncgroupsexcludelist');
        $includelist = $this->get_config('syncgroupsincludelist');
        $searchsub = $this->get_config('syncgroupssearchsub');
        $grouptype = $this->get_config('syncgroupsgrouptype');
        $groupattribute = $this->get_config('syncgroupsgroupattribute');
        $docreate = $this->get_config('syncgroupsautocreate');

        // If neither one is set, return
        if (!$syncbyattribute && !$syncbyclass) {
            log_info('not set to sync by user attribute or by group objects, so exiting');
            return true;
        }

        if (get_config('auth_ldap_debug_sync_cron')) {
            log_debug("exclusion list : ");
            var_dump($excludelist);
            log_debug("inclusion list : ");
            var_dump($includelist);
        }

        // fetch userids of current members of that institution
        if ($this->institution == 'mahara') {
            $currentmembers = get_records_sql_assoc('select u.username as username, u.id as id from {usr} u where u.deleted=0 and not exists (select 1 from {usr_institution} ui where ui.usr=u.id)', array());
        }
        else {
            $currentmembers = get_records_sql_assoc('select u.username as username, u.id as id from {usr} u inner join {usr_institution} ui on u.id=ui.usr where u.deleted=0 and ui.institution=?', array($this->institution));
        }

        if (get_config('auth_ldap_debug_sync_cron')) {
            log_debug("current members : ".count($currentmembers));
            var_dump($currentmembers);
        }

        if (get_config('auth_ldap_debug_sync_cron')) {
            log_debug("config. LDAP : ");
            var_dump($this->get_config());
        }

        $groups = array();
        if ($syncbyattribute) {
            // get the distinct values of the used attribute by a LDAP search
            // that may be restricted by flags -c or -o
            $groups = array_merge($groups, $this->get_attribute_distinct_values($searchsub));
        }

        if ($syncbyclass) {
            $groups = array_merge($groups, $this->ldap_get_grouplist('*', $searchsub));
        }

        if (get_config('auth_ldap_debug_sync_cron')) {
            log_debug("Found LDAP groups  : ");
            var_dump($groups);
        }

        $nbadded = 0;
        foreach ($groups as $group) {
            $nomatch = false;

            log_debug("Processing group '{$group}'");

            if (!ldap_sync_filter_name($group, $includelist, $excludelist)) {
                continue;
            }

            if (get_config('auth_ldap_debug_sync_cron')) {
                log_debug("processing group  : ");
                var_dump($group);
            }

            $ldapusers = array();
            if ($syncbyattribute) {
                $ldapusers = array_merge($ldapusers, $this->get_users_having_attribute_value($group));
            }

            if ($syncbyclass) {
                $ldapusers = array_merge($ldapusers, $this->ldap_get_group_members($group));
            }

            // test whether this group exists within the institution
            // group.shortname is limited to 255 characters. Unlikely anyone will hit this, but why not?
            $shortname = substr($group, 0, 255);
            if (!$dbgroup = get_record('group', 'shortname', $shortname, 'institution', $this->institution)) {
                if (!$docreate) {
                    log_debug('autocreation is off so skipping Mahara not existing group ' . $group);
                    continue;
                }

                if (count($ldapusers)==0) {
                    log_debug('will not autocreate an empty Mahara group ' . $group);
                    continue;
                }

                try {
                    log_info('creating group ' . $group);
                    // Make sure the name is unique (across all institutions)
                    // group.name only allows 128 characters. In the event of
                    // really long group names, we'll arbitrarily truncate them
                    $basename = $this->institution . ' : ' . $group;
                    $name = substr($basename, 0, 128);
                    $n = 0;
                    while (record_exists('group', 'name', $name)) {
                        $n++;
                        $tail = " $n";
                        $name .= substr($basename, 0, (128-strlen($tail))) . $tail;
                    }
                    $dbgroup = array();
                    $dbgroup['name'] = $name;
                    $dbgroup['institution'] = $this->institution;
                    $dbgroup['shortname'] = $shortname;
                    $dbgroup['grouptype'] = $grouptype; // default standard (change to course)
                    $dbgroup['controlled'] = 1; //definitively
                    $nbadded++;
                    if (!$dryrun) {
                        $groupid = group_create($dbgroup);
                    }
                }
                catch (Exception $ex) {
                    log_warn($ex->getMessage());
                    continue;
                }
            }
            else {
                $groupid = $dbgroup->id;
                log_debug('group exists ' . $group);
            }
            // now it does  exist see what members should be added/removed

            if (get_config('auth_ldap_debug_sync_cron')) {
                log_debug($group . ' : ');
                var_dump($ldapusers);
            }

            // Puts the site's "admin" user into the group as a group admin
            $members = array('1' => 'admin'); //must be set otherwise fatal error group_update_members: no group admins listed for group
            foreach ($ldapusers as $username) {
                if (isset($currentmembers[$username])) {
                    $id = $currentmembers[$username]->id;
                    $members[$id] = 'member';
                }
            }
            if (get_config('auth_ldap_debug_sync_cron')) {
                log_debug('new members list : '.count($members));
                var_dump($members);
            }

            unset($ldapusers); //try to save memory before memory consuming call to API

            $result = $dryrun ? false : group_update_members($groupid, $members);
            if ($result) {
                log_info(" ->   added : {$result['added']} removed : {$result['removed']} updated : {$result['updated']}");
            }
            else {
                log_debug('->  no change for ' . $group);
            }
            unset ($members);
            //break;
        }
        log_info('---------- finished groupsync auth instance ' . $this->instanceid . ' at ' . date('r', time()) . ' ----------');
        return true;
    }

    private function get_ldap_user_fields() {
        $ldapattributes = array();
        $ldapattributes['firstname'] = $this->config['firstnamefield'];
        $ldapattributes['lastname'] = $this->config['surnamefield'];
        $ldapattributes['email'] = $this->config['emailfield'];
        $ldapattributes['studentid'] = $this->config['studentidfield'];
        $ldapattributes['preferredname'] = $this->config['preferrednamefield'];
        foreach($ldapattributes as $k=>$v) {
            if (empty($v)) {
                unset($ldapattributes[$k]);
            }
        }
        return $ldapattributes;
    }

    private function get_group_contexts() {
        if ($this->get_config('syncgroupscontexts')) {
            $onlycontexts = $this->get_config('syncgroupscontexts');
        }
        else {
            $onlycontexts = $this->get_config('contexts');
        }
        return explode(';', $onlycontexts);
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
        'emailfield'        => '',
        'studentidfield'    => '',
        'preferrednamefield' => '',
        'syncuserscron' => false,
        'syncusersupdate' => false,
        'syncuserscreate' => false,
        'syncusersextrafilterattribute' => '',
        'syncusersgonefromldap' => '',
        'syncgroupscron' => false,
        'syncgroupsexcludelist' => '',
        'syncgroupsincludelist' => '',
        'syncgroupscontexts' => '',
        'syncgroupssearchsub' => 'yes',
        'syncgroupsautocreate' => 'no',
        'syncgroupsgrouptype' => 'standard',
        'syncgroupsbyclass' => false,
        'syncgroupsnestedgroups' => false,
        'syncgroupsgroupclass' => 'groupOfUniqueNames',
        'syncgroupsgroupattribute' => 'cn',
        'syncgroupsmemberattribute' => 'uniqueMember',
        'syncgroupsmemberattributeisdn' => false,
        'syncgroupsbyuserfield' => false,
        'syncgroupsuserattribute' => '',
        'syncgroupsusergroupnames' => '',
    );

    public static function get_cron() {
        return array(
            (object)array(
                'callfunction' => 'auth_ldap_sync_cron',
                'minute' => '0',
                'hour' => '0',
            ),
        );
    }


    /**
     * Synchronize users and groups with the LDAP server
     */
    public static function auth_ldap_sync_cron() {
        $auths = get_records_array('auth_instance', 'authname', 'ldap', 'id', 'id');
        if (!$auths) {
            return;
        }
        foreach ($auths as $auth) {
            /* @var $authobj AuthLdap */
            $authobj = AuthFactory::create($auth->id);
            // Each instance will decide for itself whether it should sync users and/or groups
            // User sync needs to be called before group sync in order for new users to wind
            // up in the correct groups
            $authobj->sync_users();
            $authobj->sync_groups();
        }
    }


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
        global $CFG;

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

        require_once($CFG->docroot . 'lib/group.php');
        $grouptypeopt = group_get_grouptype_options(self::$default_config['syncgroupsgrouptype']);

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
                'type'  => 'switchbox',
                'title' => get_string('starttls', 'auth.ldap'),
                'defaultvalue' => self::$default_config['starttls'],
            ),
            'updateuserinfoonlogin' => array(
                'type'         => 'switchbox',
                'title' => get_string('updateuserinfoonlogin', 'auth.ldap'),
                'defaultvalue' => self::$default_config['updateuserinfoonlogin'],
                'help'  => true,
            ),
            'weautocreateusers' => array(
                'type'         => 'switchbox',
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
            'studentidfield' => array(
                'type'  => 'text',
                'title' => get_string('ldapfieldforstudentid', 'auth.ldap'),
                'defaultvalue' => self::$default_config['studentidfield'],
                'help' => true,
            ),
            'preferrednamefield' => array(
                'type'  => 'text',
                'title' => get_string('ldapfieldforpreferredname', 'auth.ldap'),
                'defaultvalue' => self::$default_config['preferrednamefield'],
                'help' => true,
            ),
            'syncuserscronset' => array(
                'type' => 'fieldset',
                'legend' => get_string('syncuserssettings', 'auth.ldap'),
                'class' => 'with-formgroup',
                'collapsible' => true,
                'collapsed' => (!self::$default_config['syncuserscron']),
                'elements' => array(
                    'syncuserscron' => array(
                        'type' => 'switchbox',
                        'title' => get_string('syncuserscron', 'auth.ldap'),
                        'help' => true,
                        'defaultvalue' => self::$default_config['syncuserscron'],
                    ),
                    'syncusersupdate' => array(
                        'type' => 'switchbox',
                        'title' => get_string('syncusersupdate', 'auth.ldap'),
                        'defaultvalue' => self::$default_config['syncusersupdate'],
                    ),
                    'syncuserscreate' => array(
                        'type' => 'switchbox',
                        'title' => get_string('syncuserscreate', 'auth.ldap'),
                        'defaultvalue' => self::$default_config['syncuserscreate'],
                    ),
                    'syncusersextrafilterattribute' => array(
                        'type' => 'text',
                        'title' => get_string('syncusersextrafilterattribute', 'auth.ldap'),
                        'help' => true,
                        'defaultvalue' => self::$default_config['syncusersextrafilterattribute'],
                    ),
                    'syncusersgonefromldap' => array(
                        'type' => 'select',
                        'title' => get_string('syncusersgonefromldap', 'auth.ldap'),
                        'help' => true,
                        'options' => array(
                                '' => get_string('syncusersgonefromldapdonothing', 'auth.ldap'),
                                'suspend' => get_string('syncusersgonefromldapsuspend', 'auth.ldap'),
                                'delete' => get_string('syncusersgonefromldapdelete', 'auth.ldap'),
                        ),
                        'defaultvalue' => self::$default_config['syncusersgonefromldap'],
                    ),
                ),
            ),
            'syncgroupscronset' => array(
                'type' => 'fieldset',
                'class' => 'last',
                'legend' => get_string('syncgroupssettings', 'auth.ldap'),
                'collapsible' => true,
                'collapsed' => (!self::$default_config['syncgroupscron']),
                'elements' => array(
                    'syncgroupscron' => array(
                        'type' => 'switchbox',
                        'title' => get_string('syncgroupscron', 'auth.ldap'),
                        'help' => true,
                        'defaultvalue' => self::$default_config['syncgroupscron'],
                    ),
                    'syncgroupsautocreate' => array(
                        'type' => 'switchbox',
                        'title' => get_string('syncgroupsautocreate', 'auth.ldap'),
                        'defaultvalue' => self::$default_config['syncgroupsautocreate'],
                    ),
                    'syncgroupsgrouptype' => array(
                        'type' => 'select',
                        'title' => get_string('syncgroupsgrouptype', 'auth.ldap'),
                        'options' => $grouptypeopt,
                        'defaultvalue' => self::$default_config['syncgroupsgrouptype'],
                    ),
                    'syncgroupsexcludelist' => array(
                            'type' => 'text',
                            'title' => get_string('syncgroupsexcludelist', 'auth.ldap'),
                            'defaultvalue' => self::$default_config['syncgroupsexcludelist'],
                    ),
                    'syncgroupsincludelist' => array(
                            'type' => 'text',
                            'title' => get_string('syncgroupsincludelist', 'auth.ldap'),
                            'defaultvalue' => self::$default_config['syncgroupsincludelist'],
                    ),

                    // Groups are stored as objects in the LDAP directory
                    'syncgroupshr1' => array(
                        'type' => 'html',
                        'value' => '<hr />',
                    ),
                    'syncgroupsbyclass' => array(
                        'type' => 'switchbox',
                        'title' => get_string('syncgroupsbyclass', 'auth.ldap'),
                        'help' => true,
                        'defaultvalue' => self::$default_config['syncgroupsbyclass'],
                    ),
                    'syncgroupsgroupclass' => array(
                        'type' => 'text',
                        'title' => get_string('syncgroupsgroupclass', 'auth.ldap'),
                        'help' => true,
                        'defaultvalue' => self::$default_config['syncgroupsgroupclass'],
                    ),
                    'syncgroupsgroupattribute' => array(
                        'type' => 'text',
                        'title' => get_string('syncgroupsgroupattribute', 'auth.ldap'),
                        'help' => true,
                        'defaultvalue' => self::$default_config['syncgroupsgroupattribute'],
                    ),
                    'syncgroupsmemberattribute' => array(
                        'type' => 'text',
                        'title' => get_string('syncgroupsmemberattribute', 'auth.ldap'),
                        'help' => true,
                        'defaultvalue' => self::$default_config['syncgroupsmemberattribute'],
                    ),
                    'syncgroupsmemberattributeisdn' => array(
                        'type' => 'switchbox',
                        'title' => get_string('syncgroupsmemberattributeisdn', 'auth.ldap'),
                        'help' => true,
                        'defaultvalue' => self::$default_config['syncgroupsmemberattributeisdn'],
                    ),
                    'syncgroupsnestedgroups' => array(
                        'type' => 'switchbox',
                        'title' => get_string('syncgroupsnestedgroups', 'auth.ldap'),
                        'help' => true,
                        'defaultvalue' => self::$default_config['syncgroupsnestedgroups'],
                    ),
                    'syncgroupscontexts' => array(
                            'type' => 'text',
                            'title' => get_string('syncgroupscontexts', 'auth.ldap'),
                            'description' => get_string('syncgroupscontextsdesc', 'auth.ldap'),
                            'help' => true,
                            'defaultvalue' => self::$default_config['syncgroupscontexts'],
                    ),
                    'syncgroupssearchsub' => array(
                            'type'    => 'select',
                            'title'   => get_string('searchsubcontexts', 'auth.ldap'),
                            'options' => $yesnoopt,
                            'defaultvalue' => self::$default_config['syncgroupssearchsub'],
                    ),

                    // Group is stored in an attribute of the user object
                    'syncgroupshr2' => array(
                        'type' => 'html',
                        'value' => '<hr />',
                    ),
                    'syncgroupsbyuserfield' => array(
                        'type' => 'switchbox',
                        'title' => get_string('syncgroupsbyuserfield', 'auth.ldap'),
                        'help' => true,
                        'defaultvalue' => self::$default_config['syncgroupsbyuserfield'],
                    ),
                    'syncgroupsuserattribute' => array(
                        'type' => 'text',
                        'title' => get_string('syncgroupsuserattribute', 'auth.ldap'),
                        'defaultvalue' => self::$default_config['syncgroupsuserattribute'],
                    ),
                    'syncgroupsusergroupnames' => array(
                        'type' => 'text',
                        'title' => get_string('syncgroupsusergroupnames', 'auth.ldap'),
                        'description' => get_string('syncgroupsusergroupnamesdesc', 'auth.ldap'),
                        'help' => true,
                        'defaultvalue' => self::$default_config['syncgroupsusergroupnames'],
                    ),
                ),
            ),
        );

        return array(
            'elements' => $elements,
            'renderer' => 'div'
        );
    }


    public static function save_instance_config_options($values, Pieform $form) {

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

        foreach ( array_keys(self::$default_config) as $key ) {
            self::$default_config[$key] = $values[$key];
        }

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

/**
 * This command line PHP script will attempt to synchronize an institution list of Mahara accounts with an LDAP directory
 *
 * @param string $institutionname Name of the institution to process
 * @param array $onlycontexts Restrict searching in these contexts (override values set in authentication plugin)
 * @param boolean $searchsub search in subcontexts (override values set in authentication plugin)
 * @param string $extrafilterattribute additional LDAP filter to restrict user searching
 * @param boolean $doupdate update existing Mahara accounts with LDAP data (this may be long-running)
 * @param boolean $docreate create new accounts
 * @param string $tousersgonefromldap What to do with Mahara accounts no longer in LDAP. Should be null, 'delete', or 'suspend'
 * @param boolean $dryrun dummy execution. Do not perform any database operations
 * @return boolean
 */
function auth_ldap_sync_users(
        $institutionname,
        $onlycontexts = null,
        $searchsub = null,
        $extrafilterattribute = null,
        $doupdate = null,
        $docreate = null,
        $tousersgonefromldap = null,
        $dryrun = false
) {
    log_info('---------- started institution user sync for institution "' . $institutionname . '" at ' . date('r', time()) . ' ----------');

    $auths = get_records_select_array('auth_instance', "authname in ('cas', 'ldap') and institution=?", array($institutionname));

    if (get_config('auth_ldap_debug_sync_cron')) {
        log_debug("auths candidates : ");
        var_dump($auths);
    }

    if (count($auths) == 0) {
        log_warn(get_string('nomatchingauths', 'auth.ldap'));
        return false;
    }

    $success = true;
    foreach ($auths as $auth) {
        $instance = new  AuthLdap($auth->id);
        // Override the values stored in the auth_instance (i.e., if this is being called from a standalone cron script)
        $instance->set_config('syncuserscron', true);
        if ($onlycontexts !== null) {
            $instance->set_config('contexts', $onlycontexts);
        }
        if ($searchsub !== null) {
            $instance->set_config('search_sub', $searchsub ? 'yes' : 'no');
        }
        if ($extrafilterattribute !== null) {
            $instance->set_config('syncusersextrafilterattribute', $extrafilterattribute);
        }
        if ($doupdate !== null) {
            $instance->set_config('syncusersupdate', $doupdate);
        }
        if ($docreate !== null) {
            $instance->set_config('syncuserscreate', $docreate);
        }
        if ($tousersgonefromldap !== null) {
            $instance->set_config('syncusersgonefromldap', $tousersgonefromldap);
        }

        $success = $success && $instance->sync_users($dryrun);
    }

    log_info('---------- finished institutino user sync at ' . date('r', time()) . ' ----------');
    return $success;
}


/**
 * synchronize Mahara's groups with groups defined on a LDAP server
 *
 * @param string $institutionname Name of the institution to process
 * @param array $excludelist exclude LDAP groups matching these regular expressions in their names
 * @param array $includelist process only LDAP groups matching these regular expressions in their names
 * @param array $onlycontexts Restrict searching in these contexts (override values set in authentication plugin)
 * @param boolean $searchsub search in subcontexts (override values set in authentication plugin)
 * @param string $grouptype type of Mahara group to create, should be 'standard' or 'course'
 * @param string $groupattribute If this is present, then instead of searching for groups as objects in ldap,
 *     we search for distint values of this attribute in user accounts in LDAP, and create a group for each distinct value.
 * @param boolean $docreate create new accounts
 * @param boolean $dryrun dummy execution. Do not perform any database operations
 * @return boolean
 */
function auth_ldap_sync_groups(
        $institutionname,
        $syncbyclass = false,
        $excludelist = null,
        $includelist = null,
        $onlycontexts = null,
        $searchsub = null,
        $grouptype = null,
        $docreate = null,
        $nestedgroups = null,
        $groupclass = null,
        $groupattribute = null,
        $syncbyattribute = false,
        $userattribute = null,
        $attrgroupnames = null,
        $dryrun = false
) {
    log_info('---------- started institution group sync for "' . $institutionname . '" at ' . date('r', time()) . ' ----------');

    if (get_config('auth_ldap_debug_sync_cron')) {
        log_debug("exclusion list : ");
        var_dump($excludelist);
        log_debug("inclusion list : ");
        var_dump($includelist);
    }

    $auths = get_records_select_array('auth_instance', "authname in ('cas', 'ldap') and institution=?", array($institutionname));

    if (get_config('auth_ldap_debug_sync_cron')) {
        log_debug("auths candidates : ");
        var_dump($auths);
    }

    if (count($auths) == 0) {
        log_warn(get_string('nomatchingauths', 'auth.ldap'));
        return false;
    }

    $result = true;
    foreach ($auths as $auth) {
        $instance = new  AuthLdap($auth->id);
        $instance->set_config('syncgroupscron', true);
        $instance->set_config('syncgroupsbyclass', $syncbyclass);
        $instance->set_config('syncgroupsbyuserfield', $syncbyattribute);
        if ($excludelist !== null) {
            if (!is_array($excludelist)) {
                $excludelist = preg_split('/\s*,\s*/', trim($excludelist));
            }
            $instance->set_config('syncgroupsexcludelist', $excludelist);
        }
        if ($includelist !== null) {
            if (!is_array($includelist)) {
                $includelist = preg_split('/\s*,\s*/', trim($includelist));
            }
            $instance->set_config('syncgroupsincludelist', $includelist);
        }
        if ($onlycontexts !== null) {
            $instance->set_config('syncgroupscontexts', $onlycontexts);
        }
        if ($searchsub !== null) {
            $instance->set_config('syncgroupssearchsub', $searchsub);
        }
        if ($grouptype !== null) {
            $instance->set_config('syncgroupsgrouptype', $grouptype);
        }
        if ($nestedgroups !== null) {
            $instance->set_config('nestedgroups', $nestedgroups);
        }
        if ($groupclass !== null) {
            $instance->set_config('syncgroupsgroupclass', $groupclass);
        }
        if ($groupattribute !== null) {
            $instance->set_config('syncgroupsgroupattribute', $groupattribute);
        }
        if ($docreate !== null) {
            $instance->set_config('syncgroupsautocreate', $docreate);
        }

        $result = $result && $instance->sync_groups($dryrun);
    }
    log_info('---------- finished institution group sync at ' . date('r', time()) . ' ----------');
    return $result;
}

/**
 *
 * Filter an LDAP group name against two arrays of regular expressions
 * @param string  $name
 * @param array of string $includes
 * @param array of string $excludes
 * @return boolean
 * revised 11/02/2013 see https://mahara.org/interaction/forum/topic.php?id=6082&offset=0&limit=10#post25989
 * ported to mahara core 24 Mar 2014
 */

function ldap_sync_filter_name($name, $includes, $excludes) {
    if (!empty($includes)) {
        $found = false;
        foreach ($includes as $regexp) {
            if (empty($regexp)) {
                continue;
            }
            if (filter_var($name, FILTER_VALIDATE_REGEXP, array("options" => array('regexp' => '/' . $regexp . '/')))) {
                $found = true;
                log_debug($name . " processed because in include list \n");
                break;  // match found in include list go check for exclude
            }
        }
        if (!$found) {
            log_debug($name . " skipped because not in include list \n");
            return false;
        }
    }
    if (!empty($excludes)) {
        foreach ($excludes as $regexp) {
            if (empty($regexp)) {
                continue;
            }
            if (filter_var($name, FILTER_VALIDATE_REGEXP, array("options" => array('regexp' => '/' . $regexp . '/')))) {
                log_debug($name . " skipped because in exclude list \n");
                return false;
            }
        }
    }
    return true;
}
