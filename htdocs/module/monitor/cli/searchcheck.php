#!/usr/bin/env php
<?php
/**
 * Run the cron check to ensure that all LDAP connections are valid.
 *
 * @package    mahara
 * @subpackage module-monitor
 * @author     Ghada El-Zoghbi (ghada@catalyst-au.net)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 * This is run as a CLI. It conforms to the Nagios plugin standard.
 *
 * See also:
 *  - http://nagios.sourceforge.net/docs/3_0/pluginapi.html
 *  - https://nagios-plugins.org/doc/guidelines.html#PLUGOUTPUT
 */

define('CLI', 1);
define('INTERNAL',  1);

$MAHARA_ROOT = dirname(dirname(dirname(dirname(__FILE__)))) . '/';

require($MAHARA_ROOT . '/init.php');
require(get_config('libroot') . 'cli.php');
require_once(get_config('docroot') . '/module/monitor/lib.php');
require_once(get_config('docroot') . '/module/monitor/type/MonitorType_search.php');

$cli = get_cli();
if (!PluginModuleMonitor::is_active()) {
    $cli->cli_exit(get_string('monitormodulenotactive', 'module.monitor'), 2);
}

$options = array();
$options['okmessagedisabled'] = new stdClass();
$options['okmessagedisabled']->description = get_string('okmessagedisabled', 'module.monitor');
$options['okmessagedisabled']->required = false;

$settings = new stdClass();
$settings->options = $options;
$settings->info = get_string('searchcheckhelp', 'module.monitor');
$cli->setup($settings);

$failedqueuesize = MonitorType_search::get_failed_queue_size();
if ($failedqueuesize['value'] != 0) {
    $status = get_string('clistatuscritical', 'module.monitor');
    $message = MonitorType_search::get_failed_queue_size_message();
    $cli->cli_exit($status . ': ' . $message, 2);
}

$isqueueold = MonitorType_search::is_queue_older_than();
if ($isqueueold['status'] == true) {
    $status = get_string('clistatuscritical', 'module.monitor');
    $message = MonitorType_search::is_queue_older_than_message();
    $cli->cli_exit($status . ': ' . $message, 2);
}

if (!$cli->get_cli_param('okmessagedisabled')) {
    $status = get_string('clistatusok', 'module.monitor');
    $message = MonitorType_search::checking_search_succeeded_message();
    $cli->cli_exit($status . ': ' . $message, 0);
}

$cli->cli_exit(null, 0);
