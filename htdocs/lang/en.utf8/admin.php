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

$string['adminoptionsauthenticationtitle'] = 'AdminOptionsAuthentication';
$string['adminoptionsauthenticationdescription'] = '<p>List of installed authentication methods. Internal is used by default, if an
institution uses another authentication type then they will be listed beside it.</p>

<p>Did you want to <a href="institution.php">change the type of authentication for an institution</a>?</p>';
$string['authnoconfigurationoptions'] = 'No configuration options are available for this authentication type';
$string['release'] = 'Release %s (%s)';
$string['component'] = 'Component or plugin';
$string['fromversion'] = 'From version';
$string['toversion'] =  'To version';
$string['notinstalled'] = 'Not installed';
$string['nothingtoupgrade'] = 'Nothing to upgrade';
$string['upgradeloading'] = 'Loading...';
$string['upgradesuccess'] = 'Successfully upgraded to version ';
$string['upgradefailure'] = 'Failed to upgrade!';
$string['noupgrades'] = 'Nothing to upgrade! You are fully up to date!';

// Admin navigation menu
$string['usermanagement'] = 'Manage users';
$string['siteoptions']    = 'Site options';
$string['pageeditor']     = 'Site pages';
$string['menueditor']     = 'Site menu';
$string['files']          = 'Files';
$string['adminhome']      = 'Admin home';

// Site options
$string['allowpublicviews'] = 'Allow public views';
$string['allowpublicviewsdescription'] = 'If set to yes, views are accessable by the public.  If set to no, only logged in users will be able to look at views';
$string['artefactviewinactivitytime'] = 'Artefact view inactivity time';
$string['artefactviewinactivitytimedescription'] = 'The time after which an inactive view or artefact will be moved to the InactiveContent area';
$string['contactaddress'] = 'Contact address';
$string['contactaddressdescription'] = 'The email address to which messages from the Contact Us form will be sent';
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
$string['adminfile']     = 'Admin file';
$string['externallink']  = 'External link';
$string['type']          = 'Type';
$string['name']          = 'Name';
$string['noadminfiles']  = 'No admin files';
$string['linkedto']      = 'Linked to';
$string['editmenus']     = 'Edit menus';
$string['menuitemsaved'] = 'Menu item saved';
$string['savingmenuitem'] = 'Saving menu item';
$string['loggedinmenu']  = 'Logged in menu';
$string['loggedoutmenu'] = 'Logged out menu';

// Site content
$string['about']               = 'About';
$string['discardpageedits']    = 'Discard your changes to this page?';
$string['editsitecontent']     = 'Edit site content';
$string['home']                = 'Home';
$string['loggedouthome']       = 'Logged out Home';
$string['pagecontents']        = 'Text to appear on the page';
$string['pagename']            = 'Page name';
$string['pagesaved']           = 'Page saved';
$string['pagetext']            = 'Page text';
$string['privacy']             = 'Privacy statement';
$string['savechanges']         = 'Save changes';
$string['sitecontentnotfound'] = '%s text not available';
$string['termsandconditions']  = 'Terms and conditions';
$string['uploadcopyright']     = 'Upload copyright statement';

// Upload CSV stuff
$string['csvfile'] = 'CSV File';
$string['csvfiledescription'] = 'The file containing users to add';
$string['uploadcsverrorinvalidemail'] = 'Error on line %s of your file: The e-mail address for this user is not in correct form';
$string['uploadcsverrorinvalidpassword'] = 'Error on line %s of your file: The password for this user is not in correct form';
$string['uploadcsverrorinvalidusername'] = 'Error on line %s of your file: The username for this user is not in correct form';
$string['uploadcsverrorincorrectfieldcount'] = 'Line %s of the file does not have the correct number of fields';
$string['uploadcsvfile'] = 'Upload CSV File';
$string['uploadcsvfiledescription'] = 'You may use this facility to upload new users via a <acronym title="Comma Separated Values">CSV</acronym> file. Each record in the file must have a username, e-mail address and password.';



?>
