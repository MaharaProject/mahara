<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2012 Catalyst IT
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
 * @subpackage auth-browserid
 * @author     Francois Marier <francois@catalyst.net.nz>
 */

defined('INTERNAL') || die();

$string['browserid'] = 'Persona';
$string['title'] = 'Persona';
$string['description'] = 'Authenticate using Persona';
$string['notusable'] = 'Please install the PHP cURL extension and check the connection to the Persona verifier';

$string['badassertion'] = 'The given Persona assertion is not valid: %s';
$string['badverification'] = 'Mahara did not receive valid JSON output from the Persona verifier.';
$string['login'] = 'Persona';
$string['register'] = 'Register with Persona';
$string['missingassertion'] = 'Persona did not return an alpha-numeric assertion.';

$string['emailalreadyclaimed'] = "Another user account has already claimed the email address '%s'.";
$string['emailclaimedasusername'] = "Another user account has already claimed the email address '%s' as a username.";
$string['browseridnotenabled'] = "The Persona authentication plugin is not enabled in any active institution.";
$string['emailnotfound'] = "A user account with an email address of '%s' was not found in any of the institutions where Persona is enabled.";
