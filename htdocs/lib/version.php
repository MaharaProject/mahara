<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$config = new stdClass();

// See https://wiki.mahara.org/index.php/Developer_Area/Version_Numbering_Policy
// For upgrades on stable branches, increment the version by one.  On master, use the date.
$config->version = 2013100800;
$config->release = '1.8rc2dev';
$config->minupgradefrom = 2008040200;
$config->minupgraderelease = '1.0.0 (release tag 1.0.0_RELEASE)';
$config->disablelogin = true;
