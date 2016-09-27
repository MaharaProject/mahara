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
$string['notusable'] = 'Discontinued';

$string['deprecatedmsg'] = "As of 30 November 2016, <a href=\"https://wiki.mozilla.org/Identity/Persona_Shutdown_Guidelines_for_Reliers\">Mozilla is discontinuing the Persona
authentication service</a>. This plugin is a placeholder to aid in migrating existing Persona accounts from Persona to internal authentication.";
$string['nobrowseridinstances'] = 'This site has no Persona auth instances, so no action needs to be taken.';

$string['institutioncolumn'] = 'Institution';
$string['numuserscolumn'] = 'Number of active Persona users';

$string['migratetitle'] = 'Auto-migrate Persona users';
$string['migratedesc'] = 'Automatically move all Persona users to internal auth, and delete all Persona auth instances.';