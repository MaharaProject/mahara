<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage mahara-sync-ldap
 * @author     Patrick Pollet <pp@patrickpollet.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2011 Catalyst IT Ltd http://catalyst.net.nz
 * @copyright  (C) 2011 INSA de Lyon France
 *
 * This file incorporates work covered by the following copyright and
 * permission notice:
 *
 *    Moodle - Modular Object-Oriented Dynamic Learning Environment
 *             http://moodle.com
 *
 *    Copyright (C) 2001-3001 Martin Dougiamas        http://dougiamas.com
 *
 *    This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details:
 *
 *             http://www.gnu.org/copyleft/gpl.html
 */

/**
 * The purpose of this CLI script is to be run as a cron job to synchronize Mahara's groups
 * with users having some attribute set on a LDAP server
 *
 * This script requires at least a single parameter the name of the target institution
 * in which groups will be created/updated.
 * An instance of LDAP or CAS auth plugin MUST have been added to this institution
 * for this script to retrieve LDAP parameters
 * It is possible to run this script for several institutions
 *
 * For the synchronisation of group members , this script MUST be run after
 * the mahara_sync_users script
 *
 * This script is strongly inspired of synching Moodle's cohorts with LDAP groups
 * as described here : http://moodle.org/mod/forum/discuss.php?d=160751
 *
 * Sample cron entry:
 * # 5 minutes past 4am
 * 5 4 * * * $sudo -u www-data /usr/bin/php /var/www/mahara/local/ldap/cli/mahara_sync_groups_attribute.php -i='my institution'
 *
 * Notes:
 *   - run this script on command line without any paramters to get help on all options
 *   - it is required to use root or the the web server accounts when executing PHP CLI scripts
 *   - you need to change the "www-data" to match the apache user account
 *   - use "su" if "sudo" not available
 *   - If you have a large number of groups/users, you may want to raise the memory limits
 *     by passing -d memory_limit=256M
 *   - For debugging & better logging, you are encouraged to use in the command line:
 *     -d log_errors=1 -d error_reporting=E_ALL -d display_errors=0 -d html_errors=0
 *
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('INSTALLER', 1);
define('CLI', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require(get_config('libroot') . 'cli.php');

// must be done before any output
$USER->reanimate(1, 1);


require(get_config('libroot') . 'institution.php');
require(get_config('libroot') . 'group.php');
require(get_config('libroot') . 'searchlib.php');
require_once(get_config('docroot') . 'auth/ldap/lib.php');
require_once(dirname(dirname(__FILE__))) . '/lib.php';



$cli = get_cli();

$options = array();

$options['institution'] = new stdClass();
$options['institution']->examplevalue = '\'my institution\'';
$options['institution']->shortoptions = array('i');
$options['institution']->description = get_string('institutionname', 'local.ldap');
$options['institution']->required = true;

$options['attribute'] = new stdClass();
$options['attribute']->examplevalue = '\'eduPersonAffiliation\'';
$options['attribute']->shortoptions = array('a');
$options['attribute']->description = get_string('attributename', 'local.ldap');
$options['attribute']->required = true;

$options['exclude'] = new stdClass();
$options['exclude']->examplevalue = '\'repository*;cipc-*[;another reg. exp.]\'';
$options['exclude']->shortoptions = array('x');
$options['exclude']->description = get_string('excludelist', 'local.ldap');
$options['exclude']->required = false;

$options['include'] = new stdClass();
$options['include']->examplevalue = '\'repository*;cipc-*[;another reg. exp.]\'';
$options['include']->shortoptions = array('o');
$options['include']->description = get_string('includelist', 'local.ldap');
$options['include']->required = false;

$options['contexts'] = new stdClass();
$options['contexts']->examplevalue = '\'ou=students,ou=pc,dc=insa-lyon,dc=fr[;anothercontext]\'';
$options['contexts']->shortoptions = array('c');
$options['contexts']->description = get_string('searchcontexts', 'local.ldap');
$options['contexts']->required = false;

$options['searchsub'] = new stdClass();
$options['searchsub']->examplevalue = '0';
$options['searchsub']->shortoptions = array('s');
$options['searchsub']->description = get_string('searchsubcontexts', 'local.ldap');
$options['searchsub']->required = false;

$options['grouptype'] = new stdClass();
$options['grouptype']->examplevalue = 'course|standard';
$options['grouptype']->shortoptions = array('t');
$options['grouptype']->description = get_string('grouptype', 'local.ldap');
$options['grouptype']->required = false;

$options['nocreate'] = new stdClass();
$options['nocreate']->shortoptions = array('n');
$options['nocreate']->description = get_string('nocreatemissinggroups', 'local.ldap');
$options['nocreate']->required = false;

$options['dryrun'] = new stdClass();
$options['dryrun']->description = get_string('dryrun', 'local.ldap');
$options['dryrun']->required = false;


$settings = new stdClass();
$settings->options = $options;
$settings->info = get_string('cli_mahara_sync_groups_attribute', 'local.ldap');

$cli->setup($settings);


try {
	//REQUIRED
    $institutionname = $cli->get_cli_param('institution');
    $institution = new Institution ($institutionname);
    $attributename = $cli->get_cli_param('attribute');
    //OPTIONALS
    $excludelist = explode(';', $cli->get_cli_param('exclude'));
    $includelist = explode(';', $cli->get_cli_param('include'));
    $CFG->debug_ldap_groupes = $cli->get_cli_param('verbose');
    $onlycontexts = $cli->get_cli_param('contexts');
    $searchsub = $cli->get_cli_param('searchsub');
    $grouptype = $cli->get_cli_param('grouptype') == 'course' ? 'course' : 'standard';
    $nocreate = $cli->get_cli_param('nocreate');
    $dryrun = $cli->get_cli_param('dryrun');

}
// we catch missing parameter and unknown institution
catch (Exception $e) {
    $USER->logout(); // important
    cli::cli_exit($e->getMessage(), true);
}

$cli->cli_print('---------- started at ' . date('r', time()) . ' ----------');
if ($CFG->debug_ldap_groupes) {
    moodle_print_object("institution : ", $institution);
    moodle_print_object("exclusion list : ", $excludelist);
    moodle_print_object("inclusion list : ", $includelist);

}

$auths = auth_instance_get_matching_instances($institutionname);

if ($CFG->debug_ldap_groupes) {
    moodle_print_object("auths candidates : ", $auths);
}

if (count($auths) == 0) {
    cli::cli_exit(get_string('cli_mahara_nomatchingauths', 'local.ldap'));
}

//fetch current members of that institution

$params = new StdClass;
$params->institution = $institutionname;
$params->member = 1;
$params->query = '';
$params->lastinstitution = null;
$params->requested = null;
$params->invitedby = null;
$limit = 0;
//note that the studentid returned here is always null since it is retrieved
// from the table usr_institution and not from the table user ...
// we don't need it here
$data = get_institutional_admin_search_results($params, $limit);

// map user's id to username for easy retrieving
$currentmembers = array();
foreach ($data['data'] as $datum) {
    $currentmembers [$datum['username']] = $datum['id'];
}
unset ($data); // save memory

if ($CFG->debug_ldap_groupes) {
    moodle_print_object("current members : ".count($currentmembers).' ', $currentmembers);
}

// it is unlikely that there is mre than one LDAP per institution
foreach ($auths as $auth) {
    $instance = new  GAAuthLdap($auth->id);
    
    $instance->set_config('group_synching_ldap_attribute_attribute', $attributename);

    // override defaut contexts values for the auth plugin
    if ($onlycontexts) {
        $instance->set_config('contexts', $onlycontexts);
    }

    // OVERRRIDING searchsub contexts for this auth plugin
    if ($searchsub !== false) {
        $instance->set_config('search_sub', $searchsub ? 'yes' : 'no');
    }
    

    if ($CFG->debug_ldap_groupes) {
        moodle_print_object("config. LDAP : ", $instance->get_config());
    }
    
    
   // get the distinct values of the used attribute by a LDAP search
   // that may be restricted by flags -c or -o   
    $groups=$instance->get_attribute_distinct_values();
    
    if ($CFG->debug_ldap_groupes) {
        moodle_print_object("distinct values found for $attributename ", $groups);
    }
    
    $nbadded = 0;
    foreach ($groups as $group) {
    	// skip if in excludelist or not in the includelist
        if (!ldap_sync_filter_name($group, $includelist, $excludelist)) {
            continue;
        }

        if ($CFG->debug_ldap_groupes) {
            moodle_print_object("processing group  : ", $group);
        }


        // test whether this group exists within the institution
        if (!$dbgroup = get_record('group', 'shortname', $group, 'institution', $institutionname)) {
            if ($nocreate) {
                $cli->cli_print('autocreation is off so skipping Mahara not existing group ' . $group);
                continue;
            }
            
            $ldapusers = $instance->get_users_having_attribute_value($group);
            if (count($ldapusers)==0) {
            	 $cli->cli_print('will not autocreate an empty Mahara group ' . $group);
            	 continue;
            }
            
            
            try {
                $cli->cli_print('creating group ' . $group);
                $dbgroup = array();
                $dbgroup['name'] = $institutionname . ' : ' . $group;
                $dbgroup['institution'] = $institutionname;
                $dbgroup['shortname'] = $group;
                $dbgroup['grouptype'] = $grouptype; // default standard (change to course)
                $dbgroup['controlled'] = 1; //definitively
                $nbadded++;
                if (!$dryrun) {
                    $groupid = group_create($dbgroup);
                }
            }
            catch (Exception $ex) {
                $cli->cli_print($ex->getMessage());
                continue;
            }
        } else {
            $groupid = $dbgroup->id;
            $cli->cli_print('group exists ' . $group);
            $ldapusers = $instance->get_users_having_attribute_value($group);
        }
        // now it does  exist see what members should be added/removed
        $members = array('1' => 'admin'); //must be set otherwise fatal error group_update_members: no group admins listed for group
        foreach ($ldapusers as $username) {
            if (isset($currentmembers[$username])) {
                $id = $currentmembers[$username];
                $members[$id] = 'member';
            }
        }
        if ($CFG->debug_ldap_groupes) {
            moodle_print_object('new members list : '.count($members).' ', $members);
        }

        unset($ldapusers); //try to save memory before memory consuming call to API

        $result = $dryrun ? false : group_update_members($groupid, $members);
        if ($result) {
            $cli->cli_print(" ->   added : {$result['added']} removed : {$result['removed']} updated : {$result['updated']}");
        } else {
            $cli->cli_print('->  no change for ' . $group);
        }
        unset ($members);
        //break;
    }
}

$USER->logout(); // important
cli::cli_exit('---------- ended at ' . date('r', time()) . ' ----------', true);

