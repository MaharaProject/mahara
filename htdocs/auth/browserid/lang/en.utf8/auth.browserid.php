<?php
/**
 *
 * @package    mahara
 * @subpackage auth-browserid
 * @author     Francois Marier <francois@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
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
