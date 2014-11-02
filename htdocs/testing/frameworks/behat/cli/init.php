<?php
/**
 *
 * @package    mahara
 * @subpackage test/behat
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from Moodle Behat, 2013 David Monllaó
 *
 */

/**
 * CLI script to set up the behat test environment for Mahara.
 *
 * - install behat and dependencies
 * - creates a fresh database
 * - reset the dataroot
 * - updates gherkin scenarios from the selenium test suite
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('INSTALLER', 1);
define('CLI', 1);

// No access from web!
isset($_SERVER['REMOTE_ADDR']) && die('Can not run this script from web.');

// Basic behat functions.
require_once(dirname(__DIR__) . '/lib.php');

$cwd = getcwd();
// Changing the cwd to 'testing/frameworks/behat/cli'.
chdir(__DIR__);

$output = array();
exec("php util.php --diag", $output, $code);

switch ($code) {
    case 0:
        echo "The Behat test environment has been already installed and enabled\n";
        break;
    case BEHAT_EXITCODE_INSTALL:
    case BEHAT_EXITCODE_COMPOSER:
        testing_update_composer_dependencies();
        // Behat and dependencies are installed and we need to install the test site.
        passthru("php util.php --install", $code);
        if ($code != 0) {
            exit($code);
        }
        break;
    case BEHAT_EXITCODE_REINSTALL:
        testing_update_composer_dependencies();
        // Test site data is outdated.
        passthru("php util.php --drop", $code);
        if ($code != 0) {
            exit($code);
        }
        passthru("php util.php --install", $code);
        if ($code != 0) {
            exit($code);
        }
        break;
    default:
        // Generic error, we just output it.
        echo implode("\n", $output)."\n";
        exit($code);
        break;
}

// Enable testing mode according to config.php vars.
passthru("php util.php --enable", $code);
if ($code != 0) {
    echo ('Enabling Behat test environment failed.');
    exit($code);
}

chdir($cwd);
exit(0);
