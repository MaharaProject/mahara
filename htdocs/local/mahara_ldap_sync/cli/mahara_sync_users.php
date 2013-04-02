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
 * The purpose of this CLI script is to be run as a cron job to synchronize Mahara' users
 * with users defined on a LDAP server
 *
 * This script requires at least a parameter the name of the target institution
 * in which users will be created/updated.
 * An instance of LDAP or CAS auth plugin MUST have been added to this institution
 * for this script to retrieve LDAP parameters
 * It is possible to run this script for several institutions
 *
 * For the synchronisation of group membership , this script MUST be run before
 * the mahara_sync_groups script
 *
 * This script is strongly inspired of synching Moodle's users with LDAP
 */


define('INTERNAL', 1);
define('ADMIN', 1);
define('INSTALLER', 1);
define('CLI', 1);

define ('SUSPENDED_REASON', 'LDAP sync ');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require(get_config('libroot') . 'cli.php');

// must be done before any output
$USER->reanimate(1, 1);


require(get_config('libroot') . 'institution.php');
require(get_config('libroot') . 'group.php');
require_once(get_config('docroot') . 'auth/ldap/lib.php');
require_once(dirname(dirname(__FILE__))) . '/lib.php';


$CFG->debug_ldap_groupes = false;

$cli = get_cli();

$options = array();

$options['institution'] = new stdClass();
$options['institution']->examplevalue = '\'my institution\'';
$options['institution']->shortoptions = array('i');
$options['institution']->description = get_string('institutionname', 'local.ldap');
$options['institution']->required = true;

$options['contexts'] = new stdClass();
$options['contexts']->examplevalue = '\'ou=pc,dc=insa-lyon,dc=fr[;anothercontext]\'';
$options['contexts']->shortoptions = array('c');
$options['contexts']->description = get_string('searchcontexts', 'local.ldap');
$options['contexts']->required = false;

$options['searchsub'] = new stdClass();
$options['searchsub']->examplevalue = '0';
$options['searchsub']->shortoptions = array('s');
$options['searchsub']->description = get_string('searchsubcontexts', 'local.ldap');
$options['searchsub']->required = false;


$options['extrafilterattribute'] = new stdClass();
$options['extrafilterattribute']->examplevalue = 'eduPersonAffiliation=member';
$options['extrafilterattribute']->shortoptions = array('f');
$options['extrafilterattribute']->description = get_string('extrafilterattribute', 'local.ldap');
$options['extrafilterattribute']->required = false;

$options['doupdate'] = new stdClass();
$options['doupdate']->shortoptions = array('u');
$options['doupdate']->description = get_string('doupdate', 'local.ldap');
$options['doupdate']->required = false;

$options['nocreate'] = new stdClass();
$options['nocreate']->shortoptions = array('n');
$options['nocreate']->description = get_string('nocreate', 'local.ldap');
$options['nocreate']->required = false;


$options['dosuspend'] = new stdClass();
$options['dosuspend']->shortoptions = array('p');
$options['dosuspend']->description = get_string('dosuspend', 'local.ldap');
$options['dosuspend']->required = false;

$options['dodelete'] = new stdClass();
$options['dodelete']->shortoptions = array('d');
$options['dodelete']->description = get_string('dodelete', 'local.ldap');
$options['dodelete']->required = false;

$options['dryrun'] = new stdClass();
$options['dryrun']->description = get_string('dryrun', 'local.ldap');
$options['dryrun']->required = false;


$settings = new stdClass();
$settings->options = $options;
$settings->info = get_string('cli_mahara_sync_users', 'local.ldap');

$cli->setup($settings);

// Check initial password and e-mail address before we install
try {
    $institutionname = $cli->get_cli_param('institution');
    $institution = new Institution ($institutionname);
    $extrafilterattribute = $cli->get_cli_param('extrafilterattribute');

    $CFG->debug_ldap_groupes = $cli->get_cli_param('verbose');
    $onlycontexts = $cli->get_cli_param('contexts');
    $searchsub = $cli->get_cli_param('searchsub');

    $doupdate = $cli->get_cli_param('doupdate');
    $nocreate = $cli->get_cli_param('nocreate');
    $dosuspend = $cli->get_cli_param('dosuspend');
    $dodelete = $cli->get_cli_param('dodelete');
    $dryrun = $cli->get_cli_param('dryrun');

    if ($dosuspend && $dodelete) {
        throw new ParameterException (get_string('cannotdeleteandsuspend', 'local.ldap'));
    }

}
// we catch missing parameter and unknown institution
catch (Exception $e) {
    $USER->logout(); // important
    cli::cli_exit($e->getMessage(), true);
}

$cli->cli_print('---------- started at ' . date('r', time()) . ' ----------');

$auths = auth_instance_get_matching_instances($institutionname);

if ($CFG->debug_ldap_groupes) {
    moodle_print_object("auths candidates : ", $auths);
}

if (count($auths) == 0) {
    $cli->cli_exit(get_string('cli_mahara_nomatchingauths', 'local.ldap'));
}

execute_sql('CREATE TEMPORARY TABLE extusers (extusername VARCHAR(64), PRIMARY KEY (extusername))', false);

// it is unlikely that there is mre than one LDAP per institution
foreach ($auths as $auth) {
    $instance = new  GAAuthLdap($auth->id);

    // override defaut contexts values for the auth plugin
    if ($onlycontexts) {
        $instance->set_config('contexts', $onlycontexts);
    }

    // OVERRRIDING searchsub contexts for this auth plugin
    if ($searchsub !== false) {
        $instance->set_config('search_sub', $searchsub ? 'yes' : 'no');
    }

    $instanceconfig = $instance->get_config();
    if ($CFG->debug_ldap_groupes) {
        moodle_print_object("config. LDAP : ", $instanceconfig);
    }

    // fetch ldap users having the filter attribute on (caution maybe mutlivalued
    // do it on a scalable version by keeping the LDAP users names in a temporary table
    $nbldapusers = $instance->ldap_get_users_scalable('extusers', 'extusername', $extrafilterattribute);
    $cli->cli_print('LDAP users found : ' . $nbldapusers);

    if ($nbldapusers <99 ) {  //sécurité avec cipcauth
        $USER->logout();
        $cli->cli_exit(get_string('cli_mahara_noldapusersfound', 'local.ldap'));
    }

    try {
        $nbupdated = $nbcreated = $nbsuspended = $nbdeleted = $nbignored = $nbpresents = $nbunsuspended = $nberrors= 0;

        // Define ldap attributes in user update
        $ldapattributes = array();
        $ldapattributes['firstname'] = $instanceconfig['firstnamefield'];
        $ldapattributes['lastname'] = $instanceconfig['surnamefield'];
        $ldapattributes['email'] = $instanceconfig['emailfield'];
        $ldapattributes['studentid'] = $instanceconfig['studentidfield'];
        $ldapattributes['preferredname'] = $instanceconfig['preferrednamefield'];

        // Match database and ldap entries and update in database if required
        $fieldstoimport = array_keys($ldapattributes);

        // fields to fetch from usr table for existing users  (try to save memory
        $fieldstofetch = array_keys($ldapattributes);
        $fieldstofetch = array_merge($fieldstofetch, array('id', 'username', 'suspendedreason'));
        $fieldstofetch = implode(',', $fieldstofetch);


        if ($CFG->debug_ldap_groupes) {
            moodle_print_object("LDAP attributes : ", $ldapattributes);
        }


        // we fetch only Mahara users of this institution concerned by this authinstance (either cas or ldap)
        // and get also their suspended status since we may have to unsuspend them
        // this search cannot be done by a call to get_institutional_admin_search_results
        // that does not support searching by auth instance id and do not return suspended status
        // and is not suitable for a massive number of users


        // users to update (known both in LDAP and Mahara usr table

        $sql = " SELECT $fieldstofetch FROM extusers E ,usr U
WHERE E.extusername= U.username and deleted=0  and U.authinstance=? order by U.username";

        $rs = get_recordset_sql($sql, array($auth->id));
        $cli->cli_print($rs->RecordCount() . ' users known to Mahara ');
        while ($record = $rs->FetchRow()) {
            $nbpresents++;
            $ldapusername = $record['username'];
            if ($doupdate) {
                $cli->cli_print('updating user ' . $ldapusername);
                // Retrieve information of user from LDAP
                $ldapdetails = $instance->get_user_info($ldapusername, $ldapattributes);
                // this method returns an object and we want an array below
                $ldapdetails = (array)$ldapdetails;

                foreach ($fieldstoimport as $field) {
                	if (isset( $ldapdetails[$field] )) { // some LDAP values missing ?
                    	$sanitizer = "sanitize_$field";
                    	$ldapdetails[$field] = $sanitizer($ldapdetails[$field]);
                    	if (!empty($ldapdetails[$field]) && ($record[$field] != $ldapdetails[$field])) {
                        	if (!$dryrun) {
                            	set_profile_field($record['id'], $field, $ldapdetails[$field]);
                        	}
                    	}
                	} else {  // signal the error
                		$cli->cli_print ('user '.$ldapusername. ' has no LDAP value for '.$field);
                		$nberrors++;
                	}
                }
                //we also must update the student id in table usr_institution
                //this call consumes ~1400 bytes that are not returned to pool ?

                if (!$dryrun) {
                	if (isset( $ldapdetails['studentid'])) { // caution may be missing ?
                    	set_field('usr_institution', 'studentid', $ldapdetails['studentid'], 'usr',
                        	$record['id'], 'institution', $institutionname);
                	}    
                }


                //  pp_error_log ('maj compte II',$user);

                unset($ldapdetails);
               
                $nbupdated++;
            } else {
                $cli->cli_print('no update for ' . $ldapusername);
            }
            
           // print_r($record);
            
            //unsuspend if was suspended by me at a previous run
            if (!empty($record['suspendedreason']) && strstr($record['suspendedreason'],SUSPENDED_REASON) !== false) {
                $cli->cli_print('unsuspending user ' . $ldapusername);

                if (!$dryrun) {
                    unsuspend_user($record['id']);
                }
                $nbunsuspended++;
            }

        }

        //users to delete /suspend

        $sql = " SELECT $fieldstofetch FROM usr U LEFT JOIN extusers E ON E.extusername = U.username
WHERE U.authinstance =?   AND deleted  =0  AND E.extusername IS NULL
ORDER BY U.username ASC ";

        $rs = get_recordset_sql($sql, array($auth->id));
        $cli->cli_print($rs->RecordCount() . ' users no anymore in LDAP ');
        while ($record = $rs->FetchRow()) {

            if ($dosuspend) {
                if (!$record['suspendedreason']) { //if not already suspended for any reason ( me or some manual operation)
                    $cli->cli_print('suspending user ' . $record['username']);
                    if (!$dryrun) {
                        suspend_user($record['id'], SUSPENDED_REASON . ' ' .time().' ('. format_date(time()).')');
                    }
                    $nbsuspended++;

                } else {
            		$cli->cli_print('user '.$record['username']. ' already suspended by '.$record['suspendedreason']);
                }
            } else {
                if ($dodelete) {
                    $cli->cli_print('deleting user ' . $record['username']);
                    if (!$dryrun) {
                        delete_user($record['id']);
                    }
                    $nbdeleted++;

                } else {
                    // nothing to do
                    $cli->cli_print('ignoring user ' . $record['username']);
                    $nbignored++;
                }
            }

        }

        // users to create
        // we edo it at the end since to is very memory consuming
        // and often die with memory exhaution . Why ?


        $sql = 'SELECT E.extusername FROM extusers E LEFT JOIN usr U ON E.extusername = U.username
WHERE U.id IS NULL       ORDER BY  E.extusername';

        $rs = get_recordset_sql($sql);
        $cli->cli_print($rs->RecordCount() . ' LDAP users unknown to Mahara  ');
        while ($record = $rs->FetchRow()) {
        	$ldapusername = $record['extusername'];
        	if (!$nocreate) {
        		$cli->cli_print('creating user ' . $ldapusername);
        		// Retrieve information of user from LDAP
        		$ldapdetails = $instance->get_user_info($ldapusername, $ldapattributes);
        		$ldapdetails->username = $ldapusername; //not returned by LDAP
        		$ldapdetails->authinstance = $auth->id;
        		if ($CFG->debug_ldap_groupes) {
        			moodle_print_object("creation de ", $ldapdetails);
        		}
                //check for used email
        		if (($d1=get_record('usr', 'email', $ldapdetails->email)) 
        		    || ($d2=get_record('artefact_internal_profile_email', 'email', $ldapdetails->email))) {
        		    if (empty($d1)) {
        		    	$d1=get_record('usr','id',$d2->owner);
        		    }
        		    if ($CFG->debug_ldap_groupes) {
        		    	moodle_print_object("collision email ",$d1);
        		    }	
        			$cli->cli_print(get_string('emailalreadytaken', 'auth.internal') .' '. $d1->username . ' '.$ldapdetails->email);
        			$nberrors ++;
        		} else {
        			// consumes also a lot of memory not returned to poll
        			if (!$dryrun) {
        				create_user($ldapdetails, array(), $institutionname);
        			}
        			$nbcreated++;
        		}
        		
        		unset ($ldapdetails);

        	} else {
        		$cli->cli_print('ignoring LDAP user not in Mahara ' . $ldapusername);
        	}
            // if ($nbcreated > 500) break;
        }


    }
    catch (Exception $e) {
        //likely an out of memory error
        $cli->cli_print("LDAP users : $nbpresents updated : $nbupdated unsuspended : $nbunsuspended created : $nbcreated suspended : $nbsuspended deleted : $nbdeleted ignored : $nbignored errors : $nberrors");
        $USER->logout(); // important
        cli::cli_exit($e->getMessage(), true);

    }
}

$cli->cli_print("LDAP users : $nbpresents updated : $nbupdated unsuspended : $nbunsuspended created : $nbcreated suspended : $nbsuspended deleted : $nbdeleted ignored : $nbignored errors :$nberrors");

$USER->logout(); // important
cli::cli_exit('---------- ended at ' . date('r', time()) . ' ----------', true);




