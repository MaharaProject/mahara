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
 * CLI tool to manage Behat integration in Mahara
 *
 * Like Moodle, This tool uses 
 * $CFG->behat_dataroot for $CFG->dataroot
 * and $CFG->behat_dbprefix for $CFG->dbprefix
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('CLI', 1);
define('INSTALLER', 1);
define('BEHAT_UTIL', 1);

// No access from web!
isset($_SERVER['REMOTE_ADDR']) && die('Can not run this script from web.');

// Mahara libs
// Loading mahara init.
require(dirname(dirname(dirname(dirname(__DIR__)))) . '/init.php');
require_once(get_config('docroot') . '/lib/upgrade.php');
require_once(get_config('docroot') . '/local/install.php');
require_once(get_config('docroot') . '/lib/cli.php');
require_once(get_config('docroot') . '/lib/file.php');
// Behat utilities.
require_once(get_config('docroot') . '/testing/classes/TestLock.php');
require_once(get_config('docroot') . '/testing/frameworks/behat/lib.php');
require_once(get_config('docroot') . '/testing/frameworks/behat/classes/util.php');
require_once(get_config('docroot') . '/testing/frameworks/behat/classes/BehatCommand.php');


$cli = get_cli();

$options = array();

$options['install'] = new stdClass();
$options['install']->shortoptions = array('i');
$options['install']->description = 'Installs the test environment for acceptance tests';
$options['install']->required = false;
$options['install']->defaultvalue = false;

$options['drop'] = new stdClass();
$options['drop']->shortoptions = array('u');
$options['drop']->description = 'Drops the database tables and the dataroot contents';
$options['drop']->required = false;
$options['drop']->defaultvalue = false;

$options['enable'] = new stdClass();
$options['enable']->shortoptions = array('e');
$options['enable']->description = 'Enables test environment and updates tests list';
$options['enable']->required = false;
$options['enable']->defaultvalue = false;

$options['disable'] = new stdClass();
$options['disable']->shortoptions = array('d');
$options['disable']->description = 'Disables test environment';
$options['disable']->required = false;
$options['disable']->defaultvalue = false;

$options['diag'] = new stdClass();
$options['diag']->description = 'Get behat test environment status code';
$options['diag']->required = false;
$options['diag']->defaultvalue = false;

$options['config'] = new stdClass();
$options['config']->shortoptions = array('c');
$options['config']->description = 'Get behat YML config path';
$options['config']->required = false;
$options['config']->defaultvalue = false;

$settings = new stdClass();
$settings->options = $options;
$settings->info = 'CLI tool to manage Behat integration in Mahara';

$cli->setup($settings);

try {
    if ($cli->get_cli_param('install')) {
        BehatTestingUtil::install_site();
        cli::cli_exit("\nAcceptance test site is installed\n");
    }
    else if ($cli->get_cli_param('drop')) {
        TestLock::acquire('behat');
        BehatTestingUtil::drop_site();
        cli::cli_exit("\nAcceptance tests site dropped\n");
    }
    else if ($cli->get_cli_param('enable')) {
        BehatTestingUtil::start_test_mode();
        $runtestscommand = BehatCommand::get_behat_command(true) .
            ' --config ' . BehatConfigManager::get_behat_cli_config_filepath();
        cli::cli_exit("\nAcceptance tests environment enabled on $CFG->behat_wwwroot, to run the tests use:\n " . $runtestscommand . "\n");
    }
    else if ($cli->get_cli_param('disable')) {
        BehatTestingUtil::stop_test_mode();
        cli::cli_exit("\nAcceptance test site is disabled\n");
    }
    else if ($cli->get_cli_param('diag')) {
        $code = BehatTestingUtil::get_behat_status();
        exit($code);
    }
    else if ($cli->get_cli_param('config')) {
        $code = BehatTestingUtil::get_behat_status();
        if ($code == 0) {
            echo BehatTestingUtil::get_behat_config_path();
        }
        exit($code);
    }
}
catch (Exception $e) {
    cli::cli_exit($e->getMessage(), true);
}

exit(0);
