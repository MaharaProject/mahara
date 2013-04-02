<?php
/**
 * Created by JetBrains PhpStorm.
 * User: root
 * Date: 14/12/11
 * Time: 14:12
 * To change this template use File | Settings | File Templates.
 */


/**
 * LDAP authentication plugin.
 * extended to fetch LDAP groups and to be 'group aware'
 */
class GAAuthLdap extends AuthLdap {

	/**
	 * avoid infinite loop with nested groups in 'funny' directories
	 * @var array
	 */
	var $anti_recursion_array;

	/**
	 * Constructor.
	 */

	public function __construct($instanceid) {
		global $CFG;
		//fetch all instances data
		parent::__construct($instanceid);
		//TODO must be in some setting screen Currently in config.php
		$this->config['group_attribute'] = !empty($CFG->ldap_group_attribute) ? $CFG->ldap_group_attribute : 'cn';
		$this->config['group_class'] = strtolower(!empty($CFG->ldap_group_class) ? $CFG->ldap_group_class : 'groupOfUniqueNames');

		//argh phpldap convert uniqueMember to lowercase array keys when returning the list of members  ...
		$this->config['memberattribute'] = strtolower(!empty($CFG->ldap_member_attribute) ? $CFG->ldap_member_attribute : 'uniquemember');
		$this->config['memberattribute_isdn'] = !empty($CFG->ldap_member_attribute_isdn) ? $CFG->ldap_member_attribute_isdn : 1;
		// new setting
		$this->config['process_nested_groups']=!empty($CFG->ldap_process_nested_groups )?$CFG->ldap_process_nested_groups :false;
		/**
		 * cache for found groups dn
		 * used for nested groups processing
		 */
		$this->config['groups_dn_cache']=array();
		$this->anti_recursion_array=array();
		
		// restricted list of values to use in synchying Mahara's groups with some LDAP attribute
		if (!empty($CFG->group_synching_ldap_attribute_values))
			$this->config->group_synching_ldap_attribute_values=
			  explode(',',$CFG->group_synching_ldap_attribute_values);
		else	
		    $this->config->group_synching_ldap_attribute_values=array();
		

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
	 * for debugging purpose
	 * @return array  current config for printing
	 */
	public function get_config() {
		return $this->config;
	}

	/**
	 * return all groups declared in LDAP
	 * DOES NOT SUPPORT PAGED RESULTS if more than a 1000 (AD)
	 * @return string[]
	 */

	public function ldap_get_grouplist($filter = "*") {
		/// returns all groups from ldap servers

		global $CFG;

		// print_string('connectingldap', 'auth_ldap');
		$ldapconnection = $this->ldap_connect();

		$fresult = array();

		if ($filter == "*") {
			$filter = "(&(" . $this->config['group_attribute'] . "=*)(objectclass=" . $this->config['group_class'] . "))";
		}

		$contexts = explode(';', $this->config['contexts']);

		foreach ($contexts as $context) {
			$context = trim($context);
			if (empty ($context)) {
				continue;
			}

			if ($this->config['search_sub'] == 'yes') {
				//use ldap_search to find first group from subtree
				$ldap_result = ldap_search($ldapconnection, $context, $filter, array(
				$this->config['group_attribute']
				));
			} else {
				//search only in this context
				$ldap_result = ldap_list($ldapconnection, $context, $filter, array(
				$this->config['group_attribute']
				));
			}

			$groups = ldap_get_entries($ldapconnection, $ldap_result);

			//add found groups to list
			for ($i = 0; $i < count($groups) - 1; $i++) {
				$group_cn=($groups[$i][$this->config['group_attribute']][0]);
				array_push($fresult, $group_cn );

			 // keep the dn/cn in cache for later processing of nested groups
				if ($this->config['process_nested_groups']) {
					$group_dn=$groups[$i]['dn'];
					$this->config['groups_dn_cache'][$group_dn]=$group_cn;
				}


			}
		}
		@ldap_close($ldapconnection);
		return $fresult;
	}

	/**
	 * serach for group members on a openLDAP directory
	 * return string[] array of usernames
	 */

	private function ldap_get_group_members_rfc($group) {
		global $CFG;

		$ret = array();
		$ldapconnection = $this->ldap_connect();

		if (function_exists('textlib_get_instance')) {
			$textlib = textlib_get_instance();
			$group = $textlib->convert($group, 'utf-8', $this->config['ldapencoding']);
		}
		if ($CFG->debug_ldap_groupes) {
			moodle_print_object("connexion ldap: ", $ldapconnection);
		}
		if (!$ldapconnection) {
			return $ret;
		}

		$queryg = "(&({$this->config['group_attribute']}=" . trim($group) . ")(objectClass={$this->config['group_class']}))";
		if ($CFG->debug_ldap_groupes) {
			moodle_print_object("queryg: ", $queryg);
		}

		$contexts = explode(';', $this->config['contexts']);
		if (!empty ($this->config['create_context'])) {
			array_push($contexts, $this->config['create_context']);
		}

		foreach ($contexts as $context) {
			$context = trim($context);
			if (empty ($context)) {
				continue;
			}

			if ($this->config['search_sub'] == 'yes') {
				$resultg = ldap_search($ldapconnection, $context, $queryg);
			} else {
				$resultg = ldap_list($ldapconnection, $context, $queryg);
			}

			if (!empty ($resultg) AND ldap_count_entries($ldapconnection, $resultg)) {
				$groupe = ldap_get_entries($ldapconnection, $resultg);
				if ($CFG->debug_ldap_groupes) {
					moodle_print_object("groupe: ", $groupe);
				}

				for ($g = 0; $g < (sizeof($groupe[0][$this->config['memberattribute']]) - 1); $g++) {

					$membre = trim($groupe[0][$this->config['memberattribute']][$g]);
					if ($membre != "") {
						if ($this->config['memberattribute_isdn']) {
							//rev 1.2 nested groups
							if ($this->config['process_nested_groups'] && ($group_cn=$this->is_ldap_group($membre))) {
								if ($CFG->debug_ldap_groupes){
									moodle_print_object("processing nested group ", $membre);
								}
								// in case of funny directory where groups are member of groups
								if (array_key_exists($membre,$this->anti_recursion_array)) {
									if ($CFG->debug_ldap_groupes){
										moodle_print_object("infinite loop detected skipping", $membre);
									}
									unset($this->anti_recursion_array[$membre]);
									continue;
								}

								//recursive call
								$this->anti_recursion_array[$membre]=1;
								$tmp=$this->ldap_get_group_members_rfc ($group_cn);
								unset($this->anti_recursion_array[$membre]);
								$ret=array_merge($ret,$tmp);
							} else {
								$membre = $this->get_account_bydn($this->config['memberattribute'], $membre);
								if ($membre) {
									$ret[] = $membre;
								}
							}
						}
					}
				}
			}
		}

		if ($CFG->debug_ldap_groupes) {
			moodle_print_object("retour get_g_m ", $ret);
		}
		@ldap_close($ldapconnection);
		return $ret;
	}

	/**
	 * specific serach for active Directory  problems if more than 999 members
	 * recherche paginée voir http://forums.sun.com/thread.jspa?threadID=578347
	 */

	private function ldap_get_group_members_ad($group) {
		global $CFG;

		$ret = array();
		$ldapconnection = $this->ldap_connect();
		if ($CFG->debug_ldap_groupes) {
			moodle_print_object("connexion ldap: ", $ldapconnection);
		}
		if (!$ldapconnection) {
			return $ret;
		}

		if (function_exists('textlib_get_instance')) {
			$textlib = textlib_get_instance();
			$group = $textlib->convert($group, 'utf-8', $this->config['ldapencoding']);
		}

		$queryg = "(&({$this->config['group_attribute']}=" . trim($group) . ")(objectClass={$this->config['group_class']}))";
		if ($CFG->debug_ldap_groupes) {
			moodle_print_object("queryg: ", $queryg);
		}

		$size = 999;


		$contexts = explode(';', $this->config['contexts']);
		if (!empty ($this->config['create_context'])) {
			array_push($contexts, $this->config['create_context']);
		}

		foreach ($contexts as $context) {
			$context = trim($context);
			if (empty ($context)) {
				continue;
			}
			$start = 0;
			$end = $size;
			$fini = false;

			while (!$fini) {
				//recherche paginée par paquet de 1000
				$attribut = $this->config['memberattribute'] . ";range=" . $start . '-' . $end;

				if ($this->config['search_sub'] == 'yes') {
					$resultg = ldap_search($ldapconnection, $context, $queryg, array(
					$attribut
					));
				} else {
					$resultg = ldap_list($ldapconnection, $context, $queryg, array(
					$attribut
					));
				}

				if (!empty ($resultg) AND ldap_count_entries($ldapconnection, $resultg)) {
					$groupe = ldap_get_entries($ldapconnection, $resultg);
					if ($CFG->debug_ldap_groupes) {
						moodle_print_object("groupe: ", $groupe);
					}

					// a la derniere passe, AD renvoie member;Range=numero-* !!!
					if (empty ($groupe[0][$attribut])) {
						$attribut = $this->config['memberattribute'] . ";range=" . $start . '-*';
						$fini = true;
					}

					for ($g = 0; $g < (sizeof($groupe[0][$attribut]) - 1); $g++) {
						$membre = trim($groupe[0][$this->config['memberattribute']][$g]);
						if ($membre != "") {
							if ($this->config['memberattribute_isdn']) {
								//rev 1.2 nested groups
								if ($this->config['process_nested_groups'] && ($group_cn=$this->is_ldap_group($membre))) {
									if ($CFG->debug_ldap_groupes){
										moodle_print_object("processing nested group ", $membre);
									}
									// in case of funny directory where groups are member of groups
									if (array_key_exists($membre,$this->anti_recursion_array)) {
										if ($CFG->debug_ldap_groupes){
											moodle_print_object("infinite loop detected skipping", $membre);
										}
										unset($this->anti_recursion_array[$membre]);
										continue;
									}
									//recursive call
									$this->anti_recursion_array[$membre]=1;
									$tmp=$this->ldap_get_group_members_ad ($group_cn);
									unset($this->anti_recursion_array[$membre]);
									$ret=array_merge($ret,$tmp);
								} else {
									$membre = $this->get_account_bydn($this->config['memberattribute'], $membre);
									if ($membre) {
										$ret[] = $membre;
									}
								}
							}
						}

					}
				} else {
					$fini = true;
				}
				$start = $start + $size;
				$end = $end + $size;
			}
		}
		if ($CFG->debug_ldap_groupes) {
			moodle_print_object("retour get_g_m ", $ret);
		}
		@ldap_close($ldapconnection);
		return $ret;
	}

	/**
	 * should return a Mahara account from its LDAP dn
	 * split the $dn and if naming attribute = Mahara user_attribute returns it
	 * otherwise perform a LDAP search
	 * @uses $CFG->no_speedup_ldap to force a LDAP search
	 * @uses $CFG->debug_ldap_groupes to be talkative
	 * @param string $dnid  something like member (not used)
	 * @param string $dn    uid=jdoe,ou=people,dc=... or cn=john doe,ou=people,dc=...
	 * @return string Mahara username or false
	 */
	private function get_account_bydn($dnid, $dn) {
		global $CFG;
		if ($this->config['memberattribute_isdn']) {
			$dn_tmp1 = explode(",", $dn);
			if (count($dn_tmp1) > 1) {
				// normalement le premier élément est soir cn=..., soit uid=...
				//try a shortcut if the naming attribute is the same
				//unless forced by a 'debug' configuration flag
				$dn_tmp2 = explode("=", trim($dn_tmp1[0]));

				if (empty($CFG->no_speedup_ldap) && $dn_tmp2[0] == $this->config['user_attribute']) {//celui de la config
					return $dn_tmp2[1];
				}
				else {
					// case when user's DN is NOT xx=maharausername,ou=xxxx,dc=yyyy
					// quite common with AD where DN is cn=user fullname,ou=xxxx
					// we must do another LDAP search to retrieve Mahara username from LDAP
					// since we call ldap_get_users, we do not support groups whithin group
					// (usually added as cn=groupxxxx,ou=....)

					if ($CFG->debug_ldap_groupes) {
						moodle_print_object("$dn attribut trouvé {$this->config['user_attribute']} different de ", $this->config['user_attribute'], '');
					}
					$filter= $dn_tmp2[0].'='.$this->filter_addslashes($dn_tmp2[1]);
					$matchings=$this->ldap_get_users($filter);
					// return the FIRST entry found
					if (empty($matchings)) {
						if ($CFG->debug_ldap_groupes) {
							moodle_print_object('not found','');
						}
						return false;
					}
					if (count($matchings)>1) {
						if ($CFG->debug_ldap_groupes) {
							mmodle_print_object('error more than one found for ',$count($matchings));
						}
						return false;
					}
					if ($CFG->debug_ldap_groupes) {
						moodle_print_object('found ',$matchings[0]);
					}
					return $matchings[0];
				}

			} else {
				return $dn;
			}

		} else {
			return $dn;
		}
	}


	/**
	 * search the group cn in group names cache
	 * this is definitively faster than searching AGAIN LDAP for this dn with class=group...
	 * @param string $dn  the group DN
	 * @return string the group CN or false
	 */
	private function is_ldap_group($dn) {
		if (empty($this->config['process_nested_groups'])) {
			return false; // not supported by config
		}
		return !empty($this->config['groups_dn_cache'][$dn])? $this->config['groups_dn_cache'][$dn]:false ;
	}

	/**
	 * rev 1012 traitement de l'execption avec active directory pour des groupes >1000 membres
	 * voir http://forums.sun.com/thread.jspa?threadID=578347
	 *
	 * @return string[] an array of username indexed by Moodle's userid
	 */
	public function ldap_get_group_members($groupe) {
		global $DB;
		if ($this->config['user_type'] == "ad") {
			$members = $this->ldap_get_group_members_ad($groupe);
		}
		else
		{
			$members = $this->ldap_get_group_members_rfc($groupe);
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
		if ($CFG->debug_ldap_groupes) {
			moodle_print_object("connexion ldap: ", $ldapconnection);
		}
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
		if ($CFG->debug_ldap_groupes) {
			moodle_print_object("filter users ldap: ", $filter);
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
				$ldap_result = ldap_list($ldapconnection, $filter, array($this->config['user_attribute']));
			}

			if ($entry = ldap_first_entry($ldapconnection, $ldap_result)) {
				do {
					$value = ldap_get_values_len($ldapconnection, $entry, $this->config['user_attribute']);
					$value = $value[0];
					array_push($ret, $value);

				} while ($entry = ldap_next_entry($ldapconnection, $entry));
			}
			unset($ldap_result); // free mem

		}


		@ldap_close($ldapconnection);
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

	public function ldap_get_users_scalable($tablename,$columname='username', $extrafilter = '') {
		global $CFG;

		execute_sql ('TRUNCATE TABLE '.$tablename);

		$ldapconnection = $this->ldap_connect();
		if ($CFG->debug_ldap_groupes) {
			moodle_print_object("connexion ldap: ", $ldapconnection);
		}
		if (!$ldapconnection) {
			return false;
		}

		$filter = "(" . $this->config['user_attribute'] . "=*)";
		if (!empty($this->config['objectclass'])) {
			$filter .= "&(" . $this->config['objectclass'] . "))";
		}
		if ($extrafilter) {
			$filter = "(&$filter($extrafilter))";
		}
		if ($CFG->debug_ldap_groupes) {
			moodle_print_object("filter users ldap: ", $filter);
		}

		// get all contexts and look for first matching user
		$ldap_contexts = explode(";", $this->config['contexts']);

		$nbadded=0;

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
				$ldap_result = ldap_list($ldapconnection, $filter, array($this->config['user_attribute']));
			}

			if ($entry = ldap_first_entry($ldapconnection, $ldap_result)) {
				do {
					$value = ldap_get_values_len($ldapconnection, $entry, $this->config['user_attribute']);
					$value = $value[0];
					//array_push($ret, $value);
					insert_record($tablename,array($columname=>addslashes($value)),false,false);
					$nbadded ++;

				} while ($entry = ldap_next_entry($ldapconnection, $entry));
			}
			unset($ldap_result); // free mem

		}


		@ldap_close($ldapconnection);
		return $nbadded;


	}
	
	 /**
     * 
     * returns the distinct values of the target LDAP attribute
     * these will be the names of the synched Mahara groups
     * @returns array of string 
     */
    function get_attribute_distinct_values() {
       
      
        global $CFG, $DB;
        // only these groups will be synched 
        if (!empty($this->config->group_synching_ldap_attribute_values )) {
            return $this->config->group_synching_ldap_attribute_values ;
        }
        
        
        //build a filter to fetch all users having something in the target LDAP attribute 
        $filter = '('.$this->config['user_attribute'].'=*)';
    	if (!empty($this->config['objectclass'])) {
			$filter .= "&(" . $this->config['objectclass'] . "))";
		}
        $filter='(&'.$filter.'('.$this->config['group_synching_ldap_attribute_attribute'].'=*))';
        
        if ($CFG->debug_ldap_groupes) {
            moodle_print_object('looking for ',$filter);
        }

        $ldapconnection = $this->ldap_connect();

   		$ldap_contexts = explode(";", $this->config['contexts']);
   		
        $matchings=array();

        foreach ($ldap_contexts as $context) {
            $context = trim($context);
            if (empty($context)) {
                continue;
            }

            if ($this->config['search_sub'] == 'yes') {
                // Use ldap_search to find first user from subtree
                $ldap_result = ldap_search($ldapconnection, $context,
                                           $filter,
                                           array($this->group_synching_ldap_attribute_attribute));
            } else {
                // Search only in this context
                $ldap_result = ldap_list($ldapconnection, $context,
                                         $filter,
                                         array($this->group_synching_ldap_attribute_attribute));
            }

            if(!$ldap_result) {
                continue;
            }

            // this API function returns all attributes as an array 
            // wether they are single or multiple 
            $users = $this->ldap_get_entries($ldapconnection, $ldap_result);
            
            // Add found DISTINCT values to list
           for ($i = 0; $i < count($users); $i++) {
               $count=$users[$i][$this->config['group_synching_ldap_attribute_attribute']]['count'];
               for ($j=0; $j <$count; $j++) {
               	/*
                   $value=  textlib::convert($users[$i][$this->config['group_synching_ldap_attribute_attribute']][$j],
                                $this->config->ldapencoding, 'utf-8');
                */                
               	 $value=  $users[$i][$this->config['group_synching_ldap_attribute_attribute']][$j];
                  if (! in_array ($value, $matchings)) {
                       array_push($matchings,$value);
                  }
               }
           }
        }

        ldap_close($ldapconnection);
        return $matchings;
    }


    function get_users_having_attribute_value ($attributevalue) {
    	global $CFG, $DB;
    	//build a filter


    	$filter=$this->config['group_synching_ldap_attribute_attribute'].'='.
    	        $this->filter_addslashes($attributevalue);

    	// call Moodle ldap_get_userlist that return it as an array with user attributes names
    	$matchings=$this->ldap_get_users($filter);
    	// return the FIRST entry found
    	if (empty($matchings)) {
    		if ($CFG->debug_ldap_groupes) {
    			moodle_print_object('not found','');
    		}
    		return array();
    	}
     	if ($CFG->debug_ldap_groupes) {
     		moodle_print_object('found ',count($matchings). ' matching users in LDAP');
     	}

        return $matchings;
            
    }
    
}	


/**
 * Returns all authentication instances using the $name method
 *
 */
function auth_instance_get_records($name) {
	$result = get_records_select_array('auth_instance', "authname = '" . $name . "'");
	$result = empty($result) ? array() : $result;
	return $result;
}


function auth_instance_get_matching_instances($institutionname) {
	$final = array();
	$result = array_merge(auth_instance_get_records('cas'), auth_instance_get_records('ldap'));
	foreach ($result as $record) {
		if ($record->institution == $institutionname) {
			$final[] = $record;
		}
	}
	return $final;

}

/**
 * caution non scalable of big installations
 * @param $authid
 * @param $fieldstofetch
 * @return array
 */
function auth_instance_get_concerned_users($authid, $fieldstofetch) {
	$result = get_records_select_array('usr', "authinstance = " . $authid, null, false, $fieldstofetch);
	$result = empty($result) ? array() : $result;
	return $result;

}



function ldap_sync_filter_name($name, $includes, $excludes) {
	global $CFG;
	if (!empty($includes)) {
		foreach ($includes as $regexp) {
			if (empty($regexp)) {
				continue;
			}
			if (!filter_var($name, FILTER_VALIDATE_REGEXP, array("options" => array('regexp' => '/' . $regexp . '/')))) {
				if ($CFG->debug_ldap_groupes) {
					print ($name . " skipped because not in include list \n");
				}
				return false;
			}
		}
	}
	if (!empty($excludes)) {
		foreach ($excludes as $regexp) {
			if (empty($regexp)) {
				continue;
			}
			if (filter_var($name, FILTER_VALIDATE_REGEXP, array("options" => array('regexp' => '/' . $regexp . '/')))) {
				if ($CFG->debug_ldap_groupes) {
					print ($name . " skipped because in exclude list \n");
				}
				return false;
			}
		}
	}
	return true;
}


function moodle_print_object($title, $obj) {
	print $title;
	if (is_object($obj) || is_array($obj)) {
		print_r($obj);
	}
	else
	{
		print ($obj . "\n");
	}
}
