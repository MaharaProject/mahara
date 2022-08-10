<?php

/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Andrew Nicols
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
$options['force'] = (object) array(
    'shortoptions' => array('f'),
    'description' => get_string('cli_upgrade_force', 'admin'),
    'required' => false,
    'defaultvalue' => false,
);
$settings = new stdClass();
$settings->options = $options;
$settings->info = get_string('cli_upgrade_description', 'admin');

$cli->setup($settings);

$force = $cli->get_cli_param('force');
// Check whether Mahara is installed yet
if (!table_exists(new XMLDBTable('config'))) {
    $cli->cli_exit(get_string('maharanotinstalled', 'admin'), false);
}

// Check whether we need to do anything
$upgrades = check_upgrades();
if (empty($upgrades['settings']['toupgradecount'])) {
    $cli->cli_exit(get_string('noupgrades', 'admin'), false);
}

log_info('---------- begin upgrade ' . date('r', time()) . ' ----------');

// Check for issues which would pose problems during upgrade
ensure_upgrade_sanity();

if (get_field('config', 'value', 'field', '_upgrade')) {
    if ($force) {
        // delete the old flag
        delete_records('config', 'field', '_upgrade');
    }
    else {
        $cli->cli_exit(get_string('cli_upgrade_flag', 'admin'), false);
    }
}
// set the flag for this run
insert_record('config', (object) array('field' => '_upgrade', 'value' => time()));

// Clear all caches
clear_all_caches();

// Actually perform the upgrade
log_info(get_string('cli_upgrade_title', 'admin'));
foreach ($upgrades as $name => $data) {
    // Check to make sure the plugin hasn't already been update out-of-sequence
    if ($name != 'settings' && $newdata = check_upgrades($name)) {
        upgrade_mahara(array($name => $newdata));
    }
}
// upgrade completed so remove any upgrade holds
delete_records('config', 'field', '_upgrade');
log_info('---------- upgrade finished ' . date('r', time()) . ' ----------');
