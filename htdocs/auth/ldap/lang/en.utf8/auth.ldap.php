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
 * @subpackage auth-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$string['title'] = 'LDAP';
$string['description'] = 'Authenticate against an LDAP server';
$string['notusable'] = 'Please install the PHP LDAP extension';

$string['contexts'] = 'Contexts';
$string['distinguishedname'] = 'Distinguished name';
$string['hosturl'] = 'Host URL';
$string['ldapfieldforemail'] = 'LDAP field for Email';
$string['ldapfieldforfirstname'] = 'LDAP field for First Name';
$string['ldapfieldforsurname'] = 'LDAP field for Surname';
$string['ldapversion'] = 'LDAP version';
$string['starttls'] = 'TLS encryption';
$string['password'] = 'Password';
$string['searchsubcontexts'] = 'Search subcontexts';
$string['userattribute'] = 'User attribute';
$string['usertype'] = 'User type';
$string['weautocreateusers'] = 'We auto-create users';
$string['updateuserinfoonlogin'] = 'Update user info on login';
$string['updateuserinfoonloginadnote'] = 'Note: Enabling this may prevent some MS ActiveDirectory sites/users from subsequent Mahara logins';

$string['cannotconnect'] = 'Cannot connect to any LDAP hosts';
