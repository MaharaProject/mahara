<?php
/**
 *
 * @package    mahara
 * @subpackage auth-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['title'] = 'LDAP';
$string['ldapconfig'] = 'LDAP configuration';
$string['description'] = 'Authenticate against an LDAP server';
$string['notusable'] = 'Please install the PHP LDAP extension';

$string['attributename'] = 'Name of the LDAP attribute used to sync groups based on its values (required and must respect case)';
$string['cannotdeleteandsuspend']= 'Cannot specify -d and -s at the same time.';
$string['cli_info_sync_groups']='This command line PHP script will attempt to synchronize an institution list of groups with an LDAP directory.
Missing groups will be created and named as \'institution name : LDAP group name\'.';
$string['cli_info_sync_groups_attribute']='This command line PHP script will attempt to synchronize an institution list of groups with an LDAP directory
based on the different values of an LDAP attribute.
Missing groups will be created and named as \'institution name : LDAP attribute value\'';
$string['cli_info_sync_users']='This command line PHP script will attempt to synchronize an institution list of Mahara accounts with an LDAP directory.';
$string['contexts'] = 'Contexts';
$string['distinguishedname'] = 'Distinguished name';
$string['dodelete']= 'Delete accounts not anymore in LDAP' ;
$string['dosuspend']= 'Suspend accounts not anymore in LDAP';
$string['doupdate']= 'Update existing accounts with LDAP data (long)';
$string['dryrun']= 'Dummy execution. Do not perform any database operations';
$string['excludelist']='Exclude LDAP groups matching these regular expressions in their names';
$string['extrafilterattribute']='Additional LDAP filter to restrict user searching';
$string['grouptype']='Type of Mahara group to create; default is "standard"';
$string['hosturl'] = 'Host URL';
$string['includelist']='Process only LDAP groups matching these regular expressions in their names';
$string['institutionname'] = 'Name of the institution to process (required)';
$string['ldapfieldforpreferredname'] = 'LDAP field for display name';
$string['ldapfieldforemail'] = 'LDAP field for email';
$string['ldapfieldforfirstname'] = 'LDAP field for first name';
$string['ldapfieldforsurname'] = 'LDAP field for surname';
$string['ldapfieldforstudentid'] = 'LDAP field for student ID';
$string['ldapversion'] = 'LDAP version';
$string['loginlink'] = 'Allow users to link their own account';
$string['nocreate']= 'Do not create new accounts';
$string['nocreatemissinggroups']='Do not create LDAP groups if they are not already set up in the institution.';
$string['nomatchingauths']='No LDAP authentication plugin found for this institution';
$string['starttls'] = 'TLS encryption';
$string['password'] = 'Password';
$string['searchcontexts']= 'Restrict searching in these contexts (override values set in authentication plugin)';
$string['searchsubcontexts'] = 'Search subcontexts';
$string['searchsubcontextscliparam']='Search (1) or not (0) in sub contexts (override values set in authentication plugin)';
$string['syncgroupsautocreate'] = 'Auto-create missing groups';
$string['syncgroupsbyclass'] = 'Sync groups stored as LDAP objects';
$string['syncgroupsbyuserfield'] = 'Sync groups stored as user attributes';
$string['syncgroupscontexts'] = 'Sync groups in these contexts only';
$string['syncgroupscontextsdesc'] = 'Leave blank to default to user authentication contexts';
$string['syncgroupscron'] = 'Sync groups automatically via cron job';
$string['syncgroupsexcludelist'] = 'Exclude LDAP groups with these names';
$string['syncgroupsgroupattribute'] = 'Group attribute';
$string['syncgroupsgroupclass'] = 'Group class';
$string['syncgroupsgrouptype'] = 'Role types in auto-created groups';
$string['syncgroupsincludelist'] = 'Include only LDAP groups with these names';
$string['syncgroupsmemberattribute'] = 'Group member attribute';
$string['syncgroupsmemberattributeisdn'] = 'Member attribute is a dn?';
$string['syncgroupsnestedgroups'] = 'Process nested group';
$string['syncgroupssettings'] = 'Group sync';
$string['syncgroupsuserattribute'] = 'User attribute group name is stored in';
$string['syncgroupsusergroupnames'] = 'Only these group names';
$string['syncgroupsusergroupnamesdesc'] = 'Leave empty to accept any value. Separate group names by comma.';
$string['syncuserscreate'] = 'Auto-create users in cron';
$string['syncuserscron'] = 'Sync users automatically via cron job';
$string['syncusersextrafilterattribute'] = 'Additional LDAP filter for sync';
$string['syncuserssettings'] = 'User sync';
$string['syncusersupdate'] = 'Update user info in cron';
$string['syncusersgonefromldap'] = 'If a user is no longer present in LDAP';
$string['syncusersgonefromldapdonothing'] = 'Do nothing';
$string['syncusersgonefromldapsuspend'] = 'Suspend user\'s account';
$string['syncusersgonefromldapdelete'] = 'Delete user\'s account and all content';
$string['userattribute'] = 'User attribute';
$string['usertype'] = 'User type';
$string['weautocreateusers'] = 'We auto-create users';
$string['updateuserinfoonlogin'] = 'Update user info on login';

$string['cannotconnect'] = 'Cannot connect to any LDAP hosts';
