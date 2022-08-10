#!/usr/bin/env php
<?php
/**
 * Run the cron check for the number of suspended users via the LDAP user sync.
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
require_once(get_config('docroot') . '/module/monitor/type/MonitorType_ldapsuspendedusers.php');

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
$settings->info = get_string('ldapsuspendeduserscheckhelp', 'module.monitor');
$cli->setup($settings);

$results = MonitorType_ldapsuspendedusers::get_ldap_suspendedusers();
$failedinstancesarray = MonitorType_ldapsuspendedusers::extract_instances_with_suspended_users_errors($results);
$totalinstances = count($failedinstancesarray);
if ($totalinstances > 0) {
    $failedinstances = implode(',', $failedinstancesarray);
    $cli->cli_exit(get_string('checkingldapsuspendedusersfail', 'module.monitor', $totalinstances, get_config('sitename'), $failedinstances, $totalinstances), 2);
}

if (!$cli->get_cli_param('okmessagedisabled')) {
    $cli->cli_exit(get_string('checkingldapsuspendedusersssucceed', 'module.monitor', get_config('sitename')), 0);
}

$cli->cli_exit(null, 0);
