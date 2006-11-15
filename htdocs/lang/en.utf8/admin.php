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

// Upload CSV stuff
$string['csvfile'] = 'CSV File';
$string['csvfiledescription'] = 'The file containing users to add';
$string['uploadcsverrorinvalidemail'] = 'Error on line %s of your file: The e-mail address for this user is not in correct form';
$string['uploadcsverrorinvalidpassword'] = 'Error on line %s of your file: The password for this user is not in correct form';
$string['uploadcsverrorinvalidusername'] = 'Error on line %s of your file: The username for this user is not in correct form';
$string['uploadcsverrorincorrectfieldcount'] = 'Line %s of the file does not have the correct number of fields';
$string['uploadcsvfile'] = 'Upload CSV File';
?>
