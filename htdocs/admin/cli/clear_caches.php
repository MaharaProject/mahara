<?php

/**
 * Clear all mahara caches.
 *
 * @package    mahara
 * @subpackage core
 * @author     Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('CLI', 1);
define('INTERNAL', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require(get_config('libroot') . 'cli.php');
require(get_config('libroot') . 'upgrade.php');

$cli = get_cli();

$options = array();

$settings = new stdClass();
$settings->options = $options;
$settings->info = get_string('cliclearcachesdescription', 'admin');

$cli->setup($settings);

log_info(get_string('cliclearingcaches', 'admin'));
$result = clear_all_caches();

if ($result) {
    log_info(get_string('clearingcachessucceed', 'admin'));
}
