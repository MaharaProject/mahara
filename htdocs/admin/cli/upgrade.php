<?php

/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Andrew Nicols
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2011 Andrew Nicols <andrew.nicols@luns.net.uk>
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('INSTALLER', 1);
define('CLI', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require(get_config('libroot') . 'cli.php');
require(get_config('libroot') . 'upgrade.php');
require(get_config('docroot') . 'local/install.php');

$cli = get_cli();

$options = array();

$settings = new stdClass();
$settings->options = $options;
$settings->info = get_string('cliupgraderdescription', 'admin');

$cli->setup($settings);

// Check whether Mahara is installed yet
if (!table_exists(new XMLDBTable('config'))) {
    cli::cli_exit(get_string('maharanotinstalled', 'admin'), false);
}

// Check whether we need to do anything
$upgrades = check_upgrades();
if (empty($upgrades['settings']['toupgradecount'])) {
    cli::cli_exit(get_string('noupgrades', 'admin'), false);
}

// Check for issues which would pose problems during upgrade
ensure_upgrade_sanity();

// Actually perform the upgrade
log_info(get_string('cliupgradingmahara', 'admin'));
foreach ($upgrades as $name => $data) {
    // Check to make sure the plugin hasn't already been update out-of-sequence
    if ($name != 'settings' && $newdata = check_upgrades($name)) {
        upgrade_mahara(array($name => $newdata));
    }
}
