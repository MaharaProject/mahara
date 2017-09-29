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

// See https://wiki.mahara.org/wiki/Developer_Area/Version_Numbering_Policy
// For upgrades on stable branches, increment the version by one.  On master, use the date.

$config->version = 2017092900;
$config->series = '18.04';
$config->release = '18.04dev';
$config->minupgradefrom = 2015030409;
$config->minupgraderelease = '15.04.0 (release tag 15.04.0_RELEASE)';
