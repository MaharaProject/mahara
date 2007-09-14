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

// IMAP
$string['host'] = 'Hostname or address';
$string['wwwroot'] = 'WWW root';

$string['port'] = 'Port number'; 
$string['protocol'] = 'Protocol';
$string['changepasswordurl'] = 'Password-change URL';
$string['cannotremove']  = "We can't remove this auth plugin, as it's the only \\nplugin that exists for this institution.";
$string['cannotremoveinuse']  = "We can't remove this auth plugin, as it's being used by some users.\nYou must update their records before you can remove this plugin.";
$string['saveinstitutiondetailsfirst'] = 'Please save the institution details before configuring authentication plugins.';

$string['editauthority'] = 'Edit an Authority';
$string['addauthority']  = 'Add an Authority';

$string['updateuserinfoonlogin'] = 'Update user info on login';
$string['updateuserinfoonlogindescription'] = 'Retrieve user info from the remote server and update your local user record each time the user logs in.';
$string['xmlrpcserverurl'] = 'XML-RPC Server URL';
$string['ipaddress'] = 'IP Address';
$string['shortname'] = 'Short name for your site';
$string['name'] = 'Site name';
$string['nodataforinstance'] = 'Could not find data for auth instance ';
$string['authname'] = 'Authority name';
$string['weautocreateusers'] = 'We auto-create users';
$string['theyautocreateusers'] = 'They auto-create users';
$string['parent'] = 'Parent authority';
$string['wessoout'] = 'We SSO out';
$string['theyssoin'] = 'They SSO in';
$string['application'] = 'Application';
$string['cantretrievekey'] = 'An error occurred while retrieving the public key from the remote server.<br>Please ensure that the Application and WWW Root fields are correct, and that networking is enabled on the remote host.';
?>
