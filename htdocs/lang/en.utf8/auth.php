<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

// IMAP
$string['host'] = 'Hostname or address';
$string['wwwroot'] = 'WWW root';

$string['port'] = 'Port number'; 
$string['protocol'] = 'Protocol';
$string['changepasswordurl'] = 'Password-change URL';
$string['cannotremove']  = "We can't remove this auth plugin, as it's the only \nplugin that exists for this institution.";
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
$string['weimportcontent'] = 'We import content';
$string['weimportcontentdescription'] = '(some applications only)';
$string['theyssoin'] = 'They SSO in';
$string['authloginmsg'] = "Enter a message to display when a user tries to log in via Mahara's login form";
$string['application'] = 'Application';
$string['cantretrievekey'] = 'An error occurred while retrieving the public key from the remote server.<br>Please ensure that the Application and WWW Root fields are correct, and that networking is enabled on the remote host.';
$string['ssodirection'] = 'SSO direction';

$string['errorcertificateinvalidwwwroot'] = 'This certificate claims to be for %s, but you are trying to use it for %s.';
$string['errorcouldnotgeneratenewsslkey'] = 'Could not generate a new SSL key. Are you sure that both openssl and the PHP module for openssl are installed on this machine?';
$string['errnoauthinstances']   = 'We don\'t seem to have any authentication plugin instances configured for the host at %s';
$string['errornotvalidsslcertificate'] = 'This is not a valid SSL Certificate';
$string['errnoxmlrpcinstances'] = 'We don\'t seem to have any XMLRPC authentication plugin instances configured for the host at %s';
$string['errnoxmlrpcwwwroot']   = 'We don\'t have a record for any host at %s';
$string['errnoxmlrpcuser']      = "We were unable to authenticate you at this time. Possible reasons might be:

    * Your SSO session might have expired. Go back to the other application and click the link to sign into Mahara again.
    * You may not be allowed to SSO to Mahara. Please check with your administrator if you think you should be allowed to.";

$string['unabletosigninviasso'] = 'Unable to sign in via SSO';
$string['xmlrpccouldnotlogyouin'] = 'Sorry, could not log you in :(';
$string['xmlrpccouldnotlogyouindetail'] = 'Sorry, we could not log you into Mahara at this time. Please try again shortly, and if the problem persists, contact your administrator';

$string['requiredfields'] = 'Required profile fields';
$string['requiredfieldsset'] = 'Required profile fields set';
$string['noauthpluginconfigoptions'] = 'There are no configuration options associated with this plugin';

$string['hostwwwrootinuse'] = 'WWW root already in use by another institution (%s)';

// Error messages for external authentication usernames
$string['duplicateremoteusername'] = 'This external authentication username is already in use by the user %s. External authentication usernames must be unique within an authentication method.';
$string['duplicateremoteusernameformerror'] = 'External authentication usernames must be unique within an authentication method.';
