<?php
/**
 * Created by JetBrains PhpStorm.
 * User: root
 * Date: 05/01/12
 * Time: 15:25
 * To change this template use File | Settings | File Templates.
 */


defined ('INTERNAL') || die();


$string['cli_mahara_sync_users']='This command line PHP script will attempt to synchronize an institution list of Mahara accounts with an LDAP directory';

$string['cli_mahara_sync_groups']='This command line PHP script will attempt to synchronize an institution list of groups with an LDAP directory
Missing groups will be created and named as \'institution name : LDAP group name\'';


$string['cli_mahara_sync_groups_attribute']='This command line PHP script will attempt to synchronize an institution list of groups with an LDAP directory,
based on the different values of a LDAP attribute.
Missing groups will be created and named as \'institution name : LDAP attribute value\'';


$string['institutionname'] = 'Name of the institution to process (required)';

$string['attributename'] = 'Name of the LDAP attribute used to sync Mahara groups based on its values (required and must respect case)';


$string['searchcontexts']= 'Restrict searching in these contexts (override values set in authentication plugin)';
$string['searchsubcontexts']='search (1) or not (0) in sub contexts (override values set in authentication plugin)';

$string['extrafilterattribute']='additional LDAP filter to restrict user searching';

$string['nocreate']= 'do not create new accounts';
$string['nocreatemissinggroups']='do not create LDAP groups if missing in Mahara\'s institution';

$string['doupdate']= 'update existing Mahara accounts with LDAP data (long)';
$string['dodelete']= 'delete Mahara accounts not anymore in LDAP' ;
$string['dosuspend']= 'suspend Mahara accounts not anymore in LDAP';

$string['dryrun']= 'dummy execution. Do not perform any database operations';
$string['cannotdeleteandsuspend']= 'Cannot specify -d and -s at the same time';


$string['includelist']='process only LDAP groups matching these regular expressions in their names';
$string['excludelist']='exclude LDAP groups matching these regular expressions in their names';

$string['grouptype']='type of Mahara group to create, default is standard';

$string['cli_mahara_noldapusersfound']='no accounts found with your LDAP criteria. Server is down ? ';
$string['cli_mahara_nomatchingauths']='no LDAP or CAS authentication plugin found for this institution';

/*
 *
 *
cli_mahara_nomatchingauths
 */

