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
// For upgrades on dev branches, increment the version by one. On main, use the date.

$config->version = 2021080219;
$config->series = '21.10';
$config->release = '21.10.4testing';
$config->minupgradefrom = 2017031605;
$config->minupgraderelease = '18.10.0 (release tag 18.10.0_RELEASE)';
