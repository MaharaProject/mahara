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

$options['adminpassword'] = new stdClass();
$options['adminpassword']->examplevalue = 'Password1!';
$options['adminpassword']->shortoptions = array('p');
$options['adminpassword']->description = get_string('cliadminpassword', 'admin');
$options['adminpassword']->required = true;

$options['adminemail'] = new stdClass();
$options['adminemail']->examplevalue    = 'user@example.org';
$options['adminemail']->shortoptions    = array('e');
$options['adminemail']->description     = get_string('cliadminemail', 'admin');
$options['adminemail']->required = true;

$settings = new stdClass();
$settings->options = $options;
$settings->info = get_string('cliinstallerdescription', 'admin');

$cli->setup($settings);

// Check whether we need to do anything
if (table_exists(new XMLDBTable('config'))) {
    cli::cli_exit(get_string('maharainstalled', 'admin'), false);
}

// Check initial password and e-mail address before we install
try {
    $adminpassword  = $cli->get_cli_param('adminpassword');
    $adminemail     = $cli->get_cli_param('adminemail');
}
catch (ParameterException $e) {
    cli::cli_exit($e->getMessage(), true);
}

// Determine what we will install
$upgrades = check_upgrades();
$upgrades['firstcoredata'] = true;
$upgrades['localpreinst'] = true;
$upgrades['lastcoredata'] = true;
$upgrades['localpostinst'] = true;

// Actually perform the installation
log_info(get_string('cliinstallingmahara', 'admin'));
upgrade_mahara($upgrades);

// Set initial password and e-mail address
$userobj = new User();
$userobj = $userobj->find_by_username('admin');
$userobj->email = $adminemail;
$userobj->commit();

// Password changes should be performed by the authfactory
$authobj = AuthFactory::create($userobj->authinstance);
$authobj->change_password($userobj, $adminpassword, true);
