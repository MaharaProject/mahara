<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage lang
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$string['administration'] = 'Administration';

// Installer
$string['release'] = 'Release %s (%s)';
$string['copyright'] = 'Copyright &copy; 2006 onwards, Catalyst IT Ltd';
$string['agreelicense'] = 'I agree';
$string['component'] = 'Component or plugin';
$string['continue'] = 'Continue';
$string['coredata'] = 'core data';
$string['coredatasuccess'] = 'Successfully installed core data';
$string['fromversion'] = 'From version';
$string['information'] = 'Information';
$string['installsuccess'] = 'Successfully installed version ';
$string['toversion'] =  'To version';
$string['notinstalled'] = 'Not installed';
$string['nothingtoupgrade'] = 'Nothing to upgrade';
$string['performinginstallsandupgrades'] = 'Performing installs and upgrades...';
$string['runupgrade'] = 'Run upgrade';
$string['successfullyinstalled'] = 'Successfully installed Mahara!';
$string['thefollowingupgradesareready'] = 'The following upgrades are ready:';
$string['upgradeloading'] = 'Loading...';
$string['upgrades'] = 'Upgrades';
$string['upgradesuccess'] = 'Successfully upgraded';
$string['upgradesuccesstoversion'] = 'Successfully upgraded to version ';
$string['upgradefailure'] = 'Failed to upgrade!';
$string['noupgrades'] = 'Nothing to upgrade! You are fully up to date!';
$string['youcanupgrade'] = 'You can upgrade Mahara from %s (%s) to %s (%s)!';
$string['Plugin'] = 'Plugin';

// Admin navigation menu
$string['adminhome']      = 'Admin home';
$string['configsite']  = 'Configure Site';
$string['configusers'] = 'Manage Users';
$string['configextensions']   = 'Administer Extensions';
$string['manageinstitutions'] = 'Manage Institutions';

// Admin homepage strings
$string['siteoptions']    = 'Site options';
$string['siteoptionsdescription'] = 'Configure basic site options such as the name, language and theme';
$string['sitepages']     = 'Site pages';
$string['sitepagesdescription'] = 'Edit the core site content';
$string['sitemenu'] = 'Site menu';
$string['sitemenudescription'] = 'Manage the links and files within the Main Menus';
$string['adminfiles']          = 'Admin Files';
$string['adminfilesdescription'] = 'Upload and administer files that can be put in the menus';
$string['networking']          = 'Networking';
$string['networkingdescription'] = 'Configure networking for Mahara';

$string['staffusers'] = 'Staff Users';
$string['staffusersdescription'] = 'Assign users Staff permissions';
$string['adminusers'] = 'Admin Users';
$string['adminusersdescription'] = 'Assign Site Administrator access rights';
$string['institutions']   = 'Institutions';
$string['institutiondetails']   = 'Institution Details';
$string['institutionauth']   = 'Institution Authorities';
$string['institutionsdescription'] = 'Install and manage installed institutions';
$string['adminnotifications'] = 'Admin Notifications';
$string['adminnotificationsdescription'] = 'Overview of how administrators receive system notifications';
$string['uploadcsv'] = 'Add Users by CSV';
$string['uploadcsvdescription'] = 'Upload a CSV file containing new users';
$string['usersearch'] = 'User Search';
$string['usersearchdescription'] = 'Search all users and perform administrative actions on them';
$string['usersearchinstructions'] = 'You can search for users by clicking on the initials of their first and last names, or by entering a name in the search box.  You can also enter an email address in the search box if you would like to search email addresses.';
$string['institutionmembersdescription'] = 'Associate users with institutions';
$string['institutionstaffdescription'] = 'Assign users Staff permissions';
$string['institutionadminsdescription'] = 'Assign Institution Administrator access rights';
$string['pluginadmin'] = 'Plugin Administration';
$string['pluginadmindescription'] = 'Install and configure plugins';

// Site options
$string['allowpublicviews'] = 'Allow public views';
$string['allowpublicviewsdescription'] = 'If set to yes, users will be able to create Views that are accessable to the public rather than only to logged in users';
$string['artefactviewinactivitytime'] = 'Artefact view inactivity time';
$string['artefactviewinactivitytimedescription'] = 'The time after which an inactive view or artefact will be moved to the InactiveContent area';
$string['language'] = 'Language';
$string['pathtoclam'] = 'Path to clam';
$string['pathtoclamdescription'] = 'The filesystem path to clamscan or clamdscan';
$string['pathtofile'] = 'Path to file';
$string['pathtofiledescription'] = 'Filesystem path to the \'file\' program';
$string['sessionlifetime'] = 'Session lifetime';
$string['sessionlifetimedescription'] = 'Time in minutes after which an inactive logged in user will be automatically logged out';
$string['setsiteoptionsfailed'] = 'Failed setting the %s option';
$string['sitedefault'] = 'Site Default';
$string['sitelanguagedescription'] = 'The default language for the site';
$string['sitename'] = 'Site name';
$string['sitenamedescription'] = ' ';
$string['siteoptions'] = 'Site options';
$string['siteoptionsset'] = 'Site options have been updated';
$string['sitethemedescription'] = ' ';
$string['theme'] = 'Theme';
$string['updatesiteoptions'] = 'Update site options';
$string['usersallowedmultipleinstitutions'] = 'Users allowed multiple institutions';
$string['usersallowedmultipleinstitutionsdescription'] = 'If checked, users can be members of several institutions at the same time';
$string['viruschecking'] = 'Virus checking';
$string['viruscheckingdescription'] = 'If checked, virus checking will be enabled for all uploaded files using ClamAV';
$string['searchplugin'] = 'Search plugin';
$string['searchplugindescription'] = 'Search plugin to use';

// Networking options
$string['networkingextensionsmissing'] = 'Sorry, you cannot configure Mahara networking because your PHP installation is missing one or more required extensions:';
$string['publickey'] = 'Public key';
$string['publickeydescription'] = 'This public key is automatically generated, and rotated every 28 days.';
$string['publickeyexpires'] = 'Public key expires';
$string['enablenetworkingdescription'] = 'Allow your Mahara server to communicate with servers running Moodle and other applications.';
$string['enablenetworking'] = 'Enable networking';
$string['networkingenabled'] = 'Networking has been enabled. ';
$string['networkingdisabled'] = 'Networking has been disabled. ';
$string['networkingunchanged'] = 'Network settings were not changed';
$string['promiscuousmode'] = 'Auto-register all hosts';
$string['promiscuousmodedisabled'] = 'Auto-register has been disabled. ';
$string['promiscuousmodeenabled'] = 'Auto-register has been enabled. ';
$string['promiscuousmodedescription'] = 'Create an institution record for any host that connects to you, and allow its users to log on to Mahara';
$string['wwwroot'] = 'WWW Root';
$string['whatisnetworking'] = 'What is Networking?';
$string['whatnetworkingis'] = 'Mahara\'s networking features allow it to communicate with Mahara or Moodle sites running on the same or another machine. If networking is enabled, you can use it to configure single-sign-on for users who log in at either Moodle or Mahara.';

// Admin menu editor
//$string['menueditor']    = 'Menu editor';
$string['adminfile']           = 'Admin file';
$string['adminpublicdirname']  = 'public';  // Name of the directory in which to store public admin files
$string['adminpublicdirdescription'] = 'Files accessible by logged out users';
$string['badmenuitemtype']     = 'Unknown menu item type';
$string['confirmdeletemenuitem'] = 'Do you really want to delete this item?';
$string['externallink']        = 'External link';
$string['type']                = 'Type';
$string['name']                = 'Name';
$string['noadminfiles']        = 'No admin files available';
$string['linkedto']            = 'Linked to';
$string['editmenus']           = 'Edit menus';
$string['menuitemsaved']       = 'Menu item saved';
$string['savingmenuitem']      = 'Saving menu item';
$string['menuitemsloaded']     = 'Menu items loaded';
$string['deletingmenuitem']    = 'Deleting menu item';
$string['deletefailed']        = 'Failed deleting menu item';
$string['menuitemdeleted']     = 'Menu item deleted';
$string['loadingmenuitems']    = 'Loading menu items';
$string['loadmenuitemsfailed'] = 'Failed to load menu items';
$string['loggedinmenu']        = 'Logged in menu';
$string['loggedoutmenu']       = 'Public menu';
$string['public']              = 'public';

// Site content
$string['about']               = 'About';
$string['discardpageedits']    = 'Discard your changes to this page?';
$string['editsitecontent']     = 'Edit site content';
$string['home']                = 'Home';
$string['loadingpagecontent']  = 'Loading site page content';
$string['loadsitepagefailed']  = 'Failed to load site page';
$string['loggedouthome']       = 'Logged out home';
$string['pagecontents']        = ' ';
$string['pagename']            = 'Page name';
$string['pagesaved']           = 'Page saved';
$string['pagetext']            = 'Page text';
$string['privacy']             = 'Privacy Statement';
$string['savechanges']         = 'Save changes';
$string['savefailed']          = 'Save failed';
$string['sitepageloaded']      = 'Site page loaded';
$string['termsandconditions']  = 'Terms and Conditions';
$string['uploadcopyright']     = 'Upload Copyright Statement';

// Upload CSV
$string['csvfile'] = 'CSV File';
$string['uploadcsvinstitution'] = 'The institution and authentication method for the new users';
$string['uploadcsvconfigureauthplugin'] = 'You must configure an authentication plugin before you can add users by CSV';
$string['csvfiledescription'] = 'The file containing users to add';
$string['uploadcsverrorinvalidfieldname'] = 'The field name "%s" is invalid';
$string['uploadcsverrorrequiredfieldnotspecified'] = 'A required field "%s" has not been specified in the format line';
$string['uploadcsverrornorecords'] = 'The file appears to contain no records (although the header is fine)';
$string['uploadcsverrorunspecifiedproblem'] = 'The records in your CSV file could not be inserted for some reason. If your file is in the correct format then this is a bug and you should <a href="https://eduforge.org/tracker/?func=add&group_id=176&atid=739">create a bug report</a>, attaching the CSV file (remember to blank out passwords!) and, if possible, the error log file';
$string['uploadcsverrorinvalidemail'] = 'Error on line %s of your file: The e-mail address for this user is not in correct form';
$string['uploadcsverrorincorrectnumberoffields'] = 'Error on line %s of your file: This line does not have the correct number of fields';
$string['uploadcsverrorinvalidpassword'] = 'Error on line %s of your file: The password for this user is not in correct form';
$string['uploadcsverrorinvalidusername'] = 'Error on line %s of your file: The username for this user is not in correct form';
$string['uploadcsverrormandatoryfieldnotspecified'] = 'Line %s of the file does not have the required "%s" field';
$string['uploadcsverroruseralreadyexists'] = 'Line %s of the file specifies the username "%s" that already exists';
$string['uploadcsverroremailaddresstaken'] = 'Line %s of the file specifies the e-mail address "%s" that is already taken by another user';
$string['uploadcsvpagedescription'] = '<p>You may use this facility to upload new users via a <acronym title="Comma Separated Values">CSV</acronym> file.</p>
   
<p>The first row of your CSV file should specify the format of your CSV data. For example, it should look like this:</p>

<pre>username,password,email,firstname,lastname,studentid</pre>

<p>This row must include the <tt>username</tt>, <tt>password</tt>, <tt>email</tt>, <tt>firstname</tt> and <tt>lastname</tt> fields always, and then any fields that are both mandatory and locked for the institution you are uploading the users for. You can <a href="%s">configure the mandatory fields</a> for all institutions, or <a href="%s">configure the locked fields for each institution</a>.</p>

<p>Your CSV file may include any other profile fields as you require. The full list of fields is:</p>

%s';
$string['uploadcsvusersaddedsuccessfully'] = 'The users in the file have been added successfully';
$string['uploadcsvfailedusersexceedmaxallowed'] = 'No users have been added because there are too many users in your file.  The number of users in the institution would have exceeded the maximum number allowed.';

// Admin Users
$string['adminuserspagedescription'] = '<p>Here you can choose which users are administrators for the site. The current administrators are listed on the right, and potential administrators are on the left.</p><p>The system must have at least one administrator.</p>';
$string['institutionadminuserspagedescription'] = 'Here you can choose which users are administrators for the institution. The current administrators are listed on the right, and potential administrators are on the left.';
$string['potentialadmins'] = 'Potential Admins';
$string['currentadmins'] = 'Current Admins';
$string['adminusersupdated'] = 'Admin users updated';

// Staff Users
$string['staffuserspagedescription'] = 'Here you can choose which users are staff for the site. The current staff are on the right, and potential staff are on the left.';
$string['institutionstaffuserspagedescription'] = 'Here you can choose which users are staff for your institution. The current staff are on the right, and potential staff are on the left.';
$string['potentialstaff'] = 'Potential Staff';
$string['currentstaff'] = 'Current Staff';
$string['staffusersupdated'] = 'Staff users updated';

// Admin Notifications

// Suspended Users
$string['deleteusers'] = 'Delete Users';
$string['confirmdeleteusers'] = 'Are you sure you want to delete the selected users?';
$string['exportingnotsupportedyet'] = 'Exporting user profiles is not supported yet';
$string['exportuserprofiles'] = 'Export User Profiles';
$string['nousersselected'] = 'No users selected';
$string['suspenduser'] = 'Suspend User';
$string['suspendedusers'] = 'Suspended Users';
$string['suspensionreason'] = 'Suspension reason';
$string['errorwhilesuspending'] = 'An error occured while trying to suspend';
$string['suspendedusersdescription'] = 'Suspend or reactivate users from using the site';
$string['unsuspendusers'] = 'Unsuspend Users';
$string['usersdeletedsuccessfully'] = 'Users deleted successfully';
$string['usersunsuspendedsuccessfully'] = 'Users unsuspended successfully';
$string['suspendingadmin'] = 'Suspending Admin';
$string['usersuspended'] = 'User suspended';
$string['userunsuspended'] = 'User unsuspended';

// User account settings
$string['accountsettings'] = 'Account settings';
$string['siteaccountsettings'] = 'Site account settings';
$string['resetpassword'] = 'Reset password';
$string['resetpassworddescription'] = 'If you enter text here, it will replace the user\'s current password.';
$string['forcepasswordchange'] = 'Force password change on next login';
$string['forcepasswordchangedescription'] = 'The user will be directed to a change password page the next time they login.';
$string['sitestaff'] = 'Site Staff';
$string['sitestaffdescription'] = 'If checked, the user can create controlled Communities, receive and release submitted views and access key user profile information.';
$string['siteadmins'] = 'Site Admins';
$string['siteadmin'] = 'Site administrator';
$string['siteadmindescription'] = 'Site administrators can to do anything and go anywhere on the site';
$string['accountexpiry'] = 'Account expires';
$string['accountexpirydescription'] = 'Date on which the user\'s login is automatically disabled.';
$string['suspended'] = 'Suspended';
$string['suspendedreason'] = 'Reason for suspension';
$string['suspendedreasondescription'] = 'The text that will be displayed to the user on their next login attempt.';
$string['unsuspenduser'] = 'Unsuspend User';
$string['thisuserissuspended'] = 'This user has been suspended';
$string['suspendedby'] = 'This user has been suspended by %s';
$string['filequota'] = 'File quota (MB)';
$string['filequotadescription'] = 'Total storage available in the user\'s files area.';
$string['confirmremoveuserfrominstitution'] = 'Are you sure you want to remove the user from this institution?';

// Add User
$string['adduser'] = 'Add User';
$string['adduserdescription'] = 'Create a new user';
$string['createuser'] = 'Create User';

// Login as
$string['loginasuser'] = 'Login as %s';
$string['becomeadminagain'] = 'Become %s again';
// Login-as exceptions
$string['loginasdenied'] = 'Attempt to login as another user without permission';
$string['loginastwice'] = 'Attempt to login as another user when already logged in as another user';
$string['loginasrestorenodata'] = 'No user data to restore';

// Institutions
$string['admininstitutions'] = 'Admininster Institutions';
$string['adminauthorities'] = 'Admininster Authorities';
$string['addinstitution'] = 'Add Institution';
$string['authplugin'] = 'Authentication plugin';
$string['defaultaccountinactiveexpire'] = 'Default account inactivity time';
$string['defaultaccountinactiveexpiredescription'] = 'How long a user account will remain active without the user logging in';
$string['defaultaccountinactivewarn'] = 'Warning time for inactivity/expiry';
$string['defaultaccountinactivewarndescription'] = 'The time before user accounts are to expire or become inactive at which a warning message will be sent to them';
$string['defaultaccountlifetime'] = 'Default account lifetime';
$string['defaultaccountlifetimedescription'] = 'How long newly created user accounts will be usable for before they expire';
$string['deleteinstitution'] = 'Delete Institution';
$string['deleteinstitutionconfirm'] = 'Are you really sure you wish to delete this institution?';
$string['institutionaddedsuccessfully'] = 'Institution added successfully. Please configure an authentication plugin for this institution.';
$string['institutiondeletedsuccessfully'] = 'Institution deleted successfully';
$string['institutionname'] = 'Institution name';
$string['institutionnamealreadytaken'] = 'This institution name is already taken';
$string['institutiondisplayname'] = 'Institution display name';
$string['institutionupdatedsuccessfully'] = 'Institution updated successfully';
$string['registrationallowed'] = 'Registration allowed?';
$string['registrationalloweddescription'] = 'Whether users can register for the system with this institution';
$string['defaultmembershipperiod'] = 'Default membership period';
$string['defaultmembershipperioddescription'] = 'How long new members remain associated with the institution';
$string['authenticatedby'] = 'Authentication Method';
$string['authenticatedbydescription'] = '';
$string['remoteusername'] = 'Username for external authentication';
$string['remoteusernamedescription'] = 'If this user is authenticated by an external method and you would like to associate them with a different identity on a remote database, enter their remote username here.';
$string['institutionsettings'] = 'Institution Settings';
$string['changeinstitution'] = 'Change Institution';
$string['institutionstaff'] = 'Institution Staff';
$string['institutionadmins'] = 'Institution Administrators';
$string['institutionadmin'] = 'Institution Admin';
$string['institutionadministrator'] = 'Institution Administrator';
$string['institutionadmindescription'] = 'If checked, the user can administer all users in this institution.';
$string['settingsfor'] = 'Settings for:';
$string['institutionadministration'] = 'Institution Administration';
$string['institutionmembers'] = 'Institution Members';
$string['notadminforinstitution'] = 'You are not an administrator for that institution';
$string['institutionmemberspagedescription'] = 'On this page you can see users who have requested membership of your institution and add them as members.  You can also remove users from your institution, and invite users to join.';
$string['editmembers'] = 'Edit Members';
$string['editstaff'] = 'Edit Staff';
$string['editadmins'] = 'Edit Admins';
$string['membershipexpiry'] = 'Membership expires';
$string['membershipexpirydescription'] = 'Date on which the user will be automatically removed from the institution.';
$string['studentid'] = 'ID Number';
$string['institutionstudentiddescription'] = 'An optional identifier specific to the institution.  This field is not editable by the user.';

$string['userstodisplay'] = 'Users to display:';
$string['institutionusersrequesters'] = 'People who have requested institution membership';
$string['institutionusersnonmembers'] = 'People who have not requested membership yet';
$string['institutionusersmembers'] = 'People who are already institution members';

$string['addnewmembers'] = 'Add new members';
$string['addnewmembersdescription'] = '';
$string['usersrequested'] = 'Users who have requested membership';
$string['userstobeadded'] = 'Users to be added as members';
$string['addmembers'] = 'Add members';
$string['inviteuserstojoin'] = 'Invite users to join the institution';
$string['Non-members'] = 'Non-members';
$string['userstobeinvited'] = 'Users to be invited';
$string['inviteusers'] = 'Invite Users';
$string['removeusersfrominstitution'] = 'Remove users from the institution';
$string['currentmembers'] = 'Current Members';
$string['userstoberemoved'] = 'Users to be removed';
$string['removeusers'] = 'Remove Users';

$string['institutionusersupdatedrequesters'] = 'Users added';
$string['institutionusersupdatedmembers'] = 'Users removed';
$string['institutionusersupdatednonmembers'] = 'Invitations sent';

$string['maxuseraccounts'] = 'Maximum User Accounts Allowed';
$string['maxuseraccountsdescription'] = 'The maximum number of user accounts that can be associated with the institution.  If there is no limit, this field should be left blank.';
$string['institutionuserserrortoomanyusers'] = 'The users were not added.  The number of members cannot exceed the maximum allowed for the institution.  You can add fewer users, remove some users from the institution, or ask the site administrator to increase the maximum number of users.';
$string['institutionuserserrortoomanyinvites'] = 'Your invitations were not sent.  The number of existing members plus the number of outstanding invitations cannot exceed the institution\'s maximum number of users.  You can invite fewer users, remove some users from the institution, or ask the site administrator to increase the maximum number of users.';

$string['Members'] = 'Members';
$string['Maximum'] = 'Maximum';
$string['Staff'] = 'Staff';
$string['Admins'] = 'Admins';

$string['noinstitutions'] = 'No Institutions';
$string['noinstitutionsdescription'] = 'If you would like to associate users with an institution, you should create the institution first.';

// Admin User Search
$string['Query'] = 'Query';
$string['Institution'] = 'Institution';
$string['confirm'] = 'confirm';
$string['invitedby'] = 'Invited by';
$string['requestto'] = 'Request to';
$string['useradded'] = 'User added';
$string['invitationsent'] = 'Invitation sent';

// general stuff
$string['notificationssaved'] = 'Notification settings saved';
$string['onlyshowingfirst'] = 'Only showing first';
$string['resultsof'] = 'results of';

$string['installed'] = 'Installed';
$string['errors'] = 'Errors';
$string['install'] = 'Install';
$string['reinstall'] = 'Reinstall';
?>
