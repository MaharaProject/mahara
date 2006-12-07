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
$string['component'] = 'Component or plugin';
$string['continue'] = 'Continue';
$string['coredata'] = 'core data';
$string['coredatasuccess'] = 'Successfully installed core data';
$string['fromversion'] = 'From version';
$string['installsuccess'] = 'Successfully installed version ';
$string['toversion'] =  'To version';
$string['notinstalled'] = 'Not installed';
$string['nothingtoupgrade'] = 'Nothing to upgrade';
$string['successfullyinstalled'] = 'Successfully installed Mahara!';
$string['upgradeloading'] = 'Loading...';
$string['upgradesuccess'] = 'Successfully upgraded to version ';
$string['upgradefailure'] = 'Failed to upgrade!';
$string['noupgrades'] = 'Nothing to upgrade! You are fully up to date!';
$string['fixtemplatescontinue'] = 'Try fixing this and continuing here (templates were not installed)';

// Admin navigation menu
$string['adminhome']      = 'Admin home';
$string['configsite']  = 'Configure Site';
$string['configusers'] = 'Manage Users';
$string['configextensions']   = 'Administer Extensions';

// Admin homepage strings
$string['siteoptions']    = 'Site options';
$string['siteoptionsdescription'] = 'Configure basic site options such as the name, language and theme';
$string['sitepages']     = 'Site pages';
$string['sitepagesdescription'] = 'Edit the text of the basic pages';
$string['sitemenu'] = 'Site menu';
$string['sitemenudescription'] = 'Manage the links and file that appear in the menus';
$string['adminfiles']          = 'Admin Files';
$string['adminfilesdescription'] = 'Upload and administer files that can be put in the menus (note: not implemented yet)';

$string['suspendedusers'] = 'Suspended Users';
$string['suspendedusersdescription'] = 'Suspend or unsuspend users from logging in to the site (note: not implemented yet)';
$string['staffusers'] = 'Staff Users';
$string['staffusersdescription'] = 'Choose which users can have staff permissions';
$string['adminusers'] = 'Admin Users';
$string['adminusersdescription'] = 'Choose which users are administrators for the site';
$string['institutions']   = 'Institutions';
$string['institutionsdescription'] = 'Install and manage installed institutions';
$string['adminnotifications'] = 'Admin Notifications';
$string['adminnotificationsdescription'] = 'Configure how administrators receive system notifications (note: not implemented yet)';
$string['uploadcsv'] = 'Add Users by CSV';
$string['uploadcsvdescription'] = 'Upload a CSV file containing new users';

$string['pluginadmin'] = 'Plugin Administration';
$string['pluginadmindescription'] = 'Install and configure plugins';
$string['templatesadmin'] = 'Configure View Templates';
$string['templatesadmindescription'] = 'View installed templates to check their validity';

// Site options
$string['allowpublicviews'] = 'Allow public views';
$string['allowpublicviewsdescription'] = 'If set to yes, views are accessable by the public.  If set to no, only logged in users will be able to look at views';
$string['artefactviewinactivitytime'] = 'Artefact view inactivity time';
$string['artefactviewinactivitytimedescription'] = 'The time after which an inactive view or artefact will be moved to the InactiveContent area';
$string['language'] = 'Language';
$string['pathtoclam'] = 'Path to clam';
$string['pathtoclamdescription'] = 'The filesystem path to clamscan or clamdscan';
$string['sessionlifetime'] = 'Session lifetime';
$string['sessionlifetimedescription'] = 'Time in minutes after which an inactive logged in user will be automatically logged out';
$string['setsiteoptionsfailed'] = 'Failed setting the %s option';
$string['sitelanguagedescription'] = 'The default language for the site';
$string['sitename'] = 'Site name';
$string['sitenamedescription'] = 'The overall name of the site';
$string['siteoptions'] = 'Site options';
$string['siteoptionsset'] = 'Site options have been updated';
$string['sitethemedescription'] = 'The theme for the site';
$string['theme'] = 'Theme';
$string['updatesiteoptions'] = 'Update site options';
$string['viruschecking'] = 'Virus checking';
$string['viruscheckingdescription'] = 'If checked, virus checking will be enabled for all uploaded files using ClamAV';

// Admin menu editor
//$string['menueditor']    = 'Menu editor';
$string['adminfile']           = 'Admin file';
$string['badmenuitemtype']     = 'Unknown menu item type';
$string['externallink']        = 'External link';
$string['type']                = 'Type';
$string['name']                = 'Name';
$string['noadminfiles']        = 'No admin files';
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
$string['loggedoutmenu']       = 'Logged out menu';

// Site content
$string['about']               = 'About';
$string['discardpageedits']    = 'Discard your changes to this page?';
$string['editsitecontent']     = 'Edit site content';
$string['home']                = 'Home';
$string['loadingpagecontent']  = 'Loading site page content';
$string['loadsitepagefailed']  = 'Failed to load site page';
$string['loggedouthome']       = 'Logged out Home';
$string['pagecontents']        = 'Text to appear on the page';
$string['pagename']            = 'Page name';
$string['pagesaved']           = 'Page saved';
$string['pagetext']            = 'Page text';
$string['privacy']             = 'Privacy statement';
$string['savechanges']         = 'Save changes';
$string['savefailed']          = 'Save failed';
$string['sitepageloaded']      = 'Site page loaded';
$string['termsandconditions']  = 'Terms and conditions';
$string['uploadcopyright']     = 'Upload copyright statement';

// Upload CSV
$string['csvfile'] = 'CSV File';
$string['csvfiledescription'] = 'The file containing users to add';
$string['uploadcsverrorinvalidemail'] = 'Error on line %s of your file: The e-mail address for this user is not in correct form';
$string['uploadcsverrorinvalidpassword'] = 'Error on line %s of your file: The password for this user is not in correct form';
$string['uploadcsverrorinvalidusername'] = 'Error on line %s of your file: The username for this user is not in correct form';
$string['uploadcsverrorincorrectfieldcount'] = 'Line %s of the file does not have the correct number of fields';
$string['uploadcsverrormandatoryfieldnotspecified'] = 'Line %s of the file does not have the required "%s" field';
$string['uploadcsverroruseralreadyexists'] = 'Line %s of the file specifies the username "%s" that already exists';
$string['uploadcsvpagedescription'] = 'You may use this facility to upload new users via a <acronym title="Comma Separated Values">CSV</acronym> file. Each record in the file must have a username, e-mail address and password.';
$string['uploadcsvusersaddedsuccessfully'] = 'The users in the file have been added successfully';

// Admin Users
$string['adminuserspagedescription'] = '<p>Here you can choose which users are administrators for the site. The current administrators are on the right, and potential administrators are on the left.</p><p>The system must have at least one administrator, and may have more.</p>';
$string['adminusersupdated'] = 'Admin users updated';

// Staff Users
$string['staffuserspagedescription'] = 'Here you can choose which users are staff for the site. The current staff are on the right, and potential staff are on the left.';

$string['staffusersupdated'] = 'Staff users updated';

// Admin Notifications

// Suspended Users

// Institutions
$string['addinstitution'] = 'Add Institution';
$string['authplugin'] = 'Authentication plugin';
$string['defaultaccountinactiveexpire'] = 'Default account expiry time';
$string['defaultaccountinactiveexpiredescription'] = 'How long a user account will remain active without the user logging in';
$string['defaultaccountinactivewarn'] = 'Default account expire warning time';
$string['defaultaccountinactivewarndescription'] = 'The time before user accounts are to expire at which a warning message will be sent to them';
$string['defaultaccountlifetime'] = 'Default account lifetime';
$string['defaultaccountlifetimedescription'] = 'How long accounts will last for by default';
$string['deleteinstitution'] = 'Delete Institution';
$string['deleteinstitutionconfirm'] = 'Are you really sure you wish to delete this institution?';
$string['institutionaddedsuccessfully'] = 'Institution added successfully';
$string['institutiondeletedsuccessfully'] = 'Institution deleted successfully';
$string['institutionname'] = 'Institution name';
$string['institutiondisplayname'] = 'Institution display name';
$string['institutionupdatedsuccessfully'] = 'Institution updated successfully';
$string['registrationallowed'] = 'Registration allowed?';
$string['registrationalloweddescription'] = 'Whether users can register for the system with this institution';


?>
