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

$options['sitename'] = new stdClass();
$options['sitename']->examplevalue = 'Mahara site';
$options['sitename']->shortoptions = array('n');
$options['sitename']->description  = get_string('clisitename', 'admin');
$options['sitename']->required     = false;

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

// Ensure that the dummy admin email is changed
update_record('artefact_internal_profile_email', array('email' => $adminemail), array('owner' => $userobj->id));
update_record('artefact', array('title' => $adminemail), array('owner' => $userobj->id, 'artefacttype' => 'email'));

// Password changes should be performed by the authfactory
$authobj = AuthFactory::create($userobj->authinstance);
$authobj->change_password($userobj, $adminpassword, true);

// Set site name
if ($sitename = $cli->get_cli_param('sitename')) {
    if (!set_config('sitename', $sitename)) {
        cli::cli_exit(get_string('cliupdatesitenamefailed', 'admin'), true);
    }
}
