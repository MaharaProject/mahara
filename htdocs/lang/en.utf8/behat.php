<?php
/**
 *
 * @package    mahara
 * @subpackage lang/behat
 * @author     Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

// lang strings for behat
$string['errorbehatcommand'] = 'Error running Behat CLI command. Try running "{$a} --help" manually from CLI to find out more about the problem.';
$string['errorcomposer'] = 'Composer dependencies are not installed.';
$string['errordataroot'] = '$CFG->behat_dataroot is not set or is invalid.';
$string['errorsetconfig'] = '$CFG->behat_dataroot, $CFG->behat_dbprefix and $CFG->behat_wwwroot need to be set in config.php.';
$string['erroruniqueconfig'] = '$CFG->behat_dataroot, $CFG->behat_dbprefix and $CFG->behat_wwwroot values need to be different than $CFG->dataroot, $CFG->dbprefix, $CFG->wwwroot, $CFG->phpunit_dataroot, and $CFG->phpunit_prefix values.';
