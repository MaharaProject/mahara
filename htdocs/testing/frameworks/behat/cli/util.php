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
require_once('upgrade.php');
require_once(get_config('docroot') . 'local/install.php');
require_once('cli.php');
require_once('file.php');
// Behat utilities.
require_once(get_config('docroot') . 'testing/classes/TestLock.php');
require_once(get_config('docroot') . 'testing/frameworks/behat/lib.php');
require_once(get_config('docroot') . 'testing/frameworks/behat/classes/util.php');
require_once(get_config('docroot') . 'testing/frameworks/behat/classes/BehatCommand.php');


$cli = get_cli();

$options = array();

$options['init'] = new stdClass();
$options['init']->description = 'Initialise the test environment for behat tests';
$options['init']->required = false;
$options['init']->defaultvalue = false;

$options['install'] = new stdClass();
$options['install']->shortoptions = array('i');
$options['install']->description = 'Installs the test environment for behat tests';
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

$options['config'] = new stdClass();
$options['config']->shortoptions = array('c');
$options['config']->description = 'Get behat YML config path';
$options['config']->required = false;
$options['config']->defaultvalue = false;

$settings = new stdClass();
$settings->options = $options;
$settings->info = 'CLI tool to manage Behat integration in Mahara';

$cli->setup($settings);


$statuscode = BehatTestingUtil::get_test_env_status();
if ($statuscode == BEHAT_MAHARA_EXITCODE_BADCONFIG_MISSING) {
    behat_error($statuscode, 'Missing required behat settings in config.php:
 $cfg->behat_wwwroot $CFG->behat_dataroot and $CFG->behat_dbprefix.');
    exit($statuscode);
}
if ($statuscode == BEHAT_MAHARA_EXITCODE_BADCONFIG_DUPLICATEWWWROOT) {
    behat_error($statuscode, 'Non unique behat settings $cfg->behat_wwwroot in config.php.
 $cfg->behat_wwwroot must be different from $cfg->wwwroot and $cfg->phpunit_wwwroot.');
    exit($statuscode);
}
if ($statuscode == BEHAT_MAHARA_EXITCODE_BADCONFIG_DUPLICATEDATAROOT) {
    behat_error($statuscode, 'Non unique behat settings $cfg->behat_dataroot in config.php.
 $cfg->behat_dataroot must be different from $cfg->dataroot and $cfg->phpunit_dataroot.');
    exit($statuscode);
}
if ($statuscode == BEHAT_MAHARA_EXITCODE_BADCONFIG_DUPLICATEDBPREFIX) {
    behat_error($statuscode, 'Non unique behat settings $cfg->behat_dbprefix in config.php.
 $cfg->behat_dbprefix must be different from $cfg->dbprefix and $cfg->phpunit_dbprefix.');
    exit($statuscode);
}
if ($statuscode == BEHAT_MAHARA_EXITCODE_BADPERMISSIONS) {
    behat_error($statuscode, '$cfg->behat_dataroot directory can not be created');
    exit($statuscode);
}
if ($statuscode == BEHAT_MAHARA_EXITCODE_NOTWRITABLEDATAROOT) {
    behat_error($statuscode, '$cfg->behat_dataroot must point to an existing writable directory');
    exit($statuscode);
}

if ($statuscode == BEHAT_EXITCODE_CANNOTRUN) {
    behat_error($statuscode, "Can not run the behat command.");
    exit($statuscode);
}

try {
    if ($cli->get_cli_param('init')) {

        // No need to init; already initialized.
        if ($statuscode === 0) {
            cli::cli_exit("The Behat test environment is already installed and active", 0);
        }

        cli::cli_print("Initializing the test site...");
        if ($statuscode === BEHAT_EXITCODE_NOTINSTALLED) {
            // Install behat and dependencies using composer
            testing_install_dependencies();
        }
        else {
            // Update behat and dependencies using composer
            testing_update_dependencies();
        }
        //now composer is updated, apply custom styling to the html report
        set_report_styling();

        BehatConfigManager::update_config_file();
        // Other possible actions we may need to take.
        switch ($statuscode) {
            case BEHAT_MAHARA_EXITCODE_NOTINSTALLED:
                BehatTestingUtil::install_site();
                break;
            case BEHAT_MAHARA_EXITCODE_NOTENABLED:
                break;
            case BEHAT_MAHARA_EXITCODE_OUTOFDATEDB:
                // Test site data is outdated.
                // Drop it
                BehatTestingUtil::drop_site();
                BehatTestingUtil::install_site();
                break;
            default:
                behat_error($statuscode);
                break;
        }

        // Now that all setup should be complete, start up test mode.
        BehatTestingUtil::start_test_mode();
    }
    else if ($cli->get_cli_param('install')) {
        cli::cli_print("Installing the mahara test site...");
        if ($statuscode == BEHAT_MAHARA_EXITCODE_NOTINSTALLED) {
            BehatTestingUtil::install_site();
            cli::cli_exit("\nAcceptance test site is installed\n");
        }
        else {
            cli::cli_exit("Installing failed. The test site has been already installed.\n", $statuscode);
        }
    }
    else if ($cli->get_cli_param('drop')) {
        cli::cli_print("Dropping the mahara test site...");
        if ($statuscode == 0) {
            BehatTestingUtil::drop_site();
            BehatTestingUtil::stop_test_mode();
            cli::cli_exit("\nAcceptance tests site dropped\n");
        }
        else {
            cli::cli_exit("Dropping failed. The test site is not installed\n");
        }
    }
    else if ($cli->get_cli_param('enable')) {
        cli::cli_print("Enabling the mahara test site...");
        if ($statuscode == BEHAT_MAHARA_EXITCODE_NOTENABLED) {
            BehatTestingUtil::start_test_mode();
            $runtestscommand = BehatCommand::get_behat_command(true) .
                ' --config ' . BehatTestingUtil::get_behat_config_path();
            cli::cli_exit("\nAcceptance tests environment enabled on $CFG->behat_wwwroot,\
 to run the tests use:\n " . $runtestscommand . "\n");
        }
        else if ($statuscode == BEHAT_MAHARA_EXITCODE_NOTINSTALLED) {
            cli::cli_exit("Enabling failed. The test site is not installed\n");
        }
        else {
            cli::cli_exit("The test site has been already enabled\n");
        }
    }
    else if ($cli->get_cli_param('disable')) {
        cli::cli_print("Disabling the mahara test site...");
        if ($statuscode == 0) {
            BehatTestingUtil::stop_test_mode();
            cli::cli_exit("\nAcceptance test site is disabled\n");
        }
        else if ($statuscode == BEHAT_MAHARA_EXITCODE_NOTENABLED) {
            cli::cli_exit("\nAcceptance test site was disabled\n");
        }
        else {
            cli::cli_exit("Disabling failed. The test site is not installed\n");
        }
    }
    else if ($cli->get_cli_param('config')) {
        if ($statuscode == BEHAT_MAHARA_EXITCODE_NOTINSTALLED) {
            cli::cli_exit("Can not get the behat config path. The test site is not installed\n");
        }
        else {
            echo BehatTestingUtil::get_behat_config_path();
        }
    }
}
catch (Exception $e) {
    cli::cli_exit($e->getMessage(), true);
}

exit(0);
