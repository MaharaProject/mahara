<?php

/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2011 Andrew Nicols <andrew.nicols@luns.net.uk>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage core
 * @author     Andrew Nicols
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
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
if (!$upgrades) {
    cli::cli_exit(get_string('noupgrades', 'admin'), false);
}

// Check for issues which would pose problems during upgrade
ensure_upgrade_sanity();

// Actually perform the upgrade
log_info(get_string('cliupgradingmahara', 'admin'));
upgrade_mahara($upgrades);
