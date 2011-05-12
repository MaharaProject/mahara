<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage auth-internal
 * @author     Piers Harding <piers@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

//$string['defaultidpidentity'] = 'Default IdP Identity Service';
$string['defaultinstitution'] = 'Default institution';
$string['description'] = 'Authenticate against a SAML 2.0 IdP service';
$string['errorbadinstitution'] = 'Institution for connecting user not resolved';
$string['errorretryexceeded'] = 'Maximum number of retries exceeded (%s) - there must be a problem with the Identity Service';
$string['errnosamluser'] = 'No User found';
$string['errorbadlib'] = 'SimpleSAMLPHP lib directory %s is not correct.';
$string['errorbadconfig'] = 'SimpleSAMLPHP config directory %s is in correct.';
$string['errorbadcombo'] = 'You can only choose user auto creation if you have not selected remoteuser';
$string['errormissinguserattributes'] = 'You seem to be authenticated but we did not receive the required user attributes. Please check that your Identity Provider releases these SSO fields for First Name, Surname, and Email to the Service Provider Mahara is running on or inform the webmaster of this server.';
//$string['idpidentity'] = 'IdP Identity Service';
$string['institutionattribute'] = 'Institution attribute (contains "%s")';
$string['institutionvalue'] = 'Institution value to check against attribute';
$string['institutionregex'] = 'Do partial string match with institution shortname';
$string['notusable'] = 'Please install the SimpleSAMLPHP SP libraries';
$string['samlfieldforemail'] = 'SSO field for Email';
$string['samlfieldforfirstname'] = 'SSO field for First Name';
$string['samlfieldforsurname'] = 'SSO field for Surname';
$string['title'] = 'SAML';
$string['updateuserinfoonlogin'] = 'Update user details on login';
$string['userattribute'] = 'User attribute';
$string['simplesamlphplib'] = 'SimpleSAMLPHP lib directory';
$string['simplesamlphpconfig'] = 'SimpleSAMLPHP config directory';
$string['weautocreateusers'] = 'We auto-create users';
$string['remoteuser'] = 'Match username attribute to Remote username';
