<?php

/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Evan Giles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2015 Evan Giles <evan@catalyst-au.net>
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('CLI', 1);
define('INSTALLER', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require(get_config('libroot') . 'cli.php');
require(get_config('libroot') . 'upgrade.php');

$cli = get_cli();

$options = array();
$options['close'] = (object) array(
    'shortoptions' => array('cl', 'c'),
    'description' => get_string('closesite', 'admin'),
);
$options['open'] = (object) array(
    'shortoptions' => array('op', 'o'),
    'description' => get_string('reopensite', 'admin'),
);

$settings = (object) array(
    'info' => get_string('cli_close_site_info', 'admin'),
    'options' => $options,
);
$cli->setup($settings);

$opensite = $cli->get_cli_param('open');
$closesite = $cli->get_cli_param('close');

if ($closesite) {
    set_config('siteclosedbyadmin', 1);
    require_once(get_config('docroot') . 'auth/session.php');
    remove_all_sessions();
    $cli->cli_exit(get_string('cli_close_site_siteclosed', 'admin'));
}
else if ($opensite) {
    set_config('siteclosedbyadmin', 0);
    $cli->cli_exit(get_string('cli_close_site_siteopen', 'admin'));
}

$cli->cli_print_help(true);