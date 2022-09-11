<?php
/**
 *
 * @package    mahara
 * @subpackage test/behat
 * @author     Son Nguyen
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from Moodle Behat, 2013 David Monllaó
 *
 */

/**
 * CLI script try to set up the behat test environment for Mahara.
 *
 * - install behat and dependencies
 * - creates a fresh database
 * - reset the dataroot
 * - updates gherkin scenarios from the selenium test suite
 *
 * Error message will be shown if errors occur
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('INSTALLER', 1);
define('CLI', 1);

// No access from web!
isset($_SERVER['REMOTE_ADDR']) && die('Can not run this script from web.');
passthru('php ' . __DIR__ . DIRECTORY_SEPARATOR . 'util.php --init' . ((isset($argv[1]) && $argv[1] == 'html') ? 'html' : ''), $code);
