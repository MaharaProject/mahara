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

define('INTERNAL',  1);
define('PUBLIC', 1);

$MAHARA_ROOT = dirname(dirname(dirname(dirname(__FILE__)))) . '/';

if (!isset($argv) && $_GET && !empty($_GET)) {
    // We need to turn URL params into argv params
    $argv = array(0 => '');
    $i = 1;
    foreach ($_GET as $k => $v) {
        // But we don't want the urlsecret as we will check that later
        if ($k == 'urlsecret') {
            continue;
        }
        // Turn param keys into cli flags
        if (strlen($k) == 1) {
            $k = '-' . $k;
        }
        else {
            $k = '--' . $k;
        }
        // Add param value to cli flag
        if (!empty($v)) {
            $k = $k . '=' . $v;
        }
        $argv[$i] = $k;
        $i++;
    }
}

require($MAHARA_ROOT . '/init.php');
require(get_config('libroot') . 'cli.php');
require_once(get_config('docroot') . '/module/monitor/lib.php');
require_once(get_config('docroot') . '/module/monitor/type/MonitorType_ldaplookup.php');

$cli = get_cli();
if (!PluginModuleMonitor::is_active()) {
    $cli->cli_exit(get_string('monitormodulenotactive', 'module.monitor'), 2);
}
// Check that if we are hitting a URL via browser then we either need
// to have the urlsecret present or be on a whitelisted IP
if ($message = PluginModuleMonitor::check_monitor_access()) {
    $cli->cli_exit($message, 2);
}

$options = array();
$options['okmessagedisabled'] = new stdClass();
$options['okmessagedisabled']->description = get_string('okmessagedisabled', 'module.monitor');
$options['okmessagedisabled']->required = false;

$settings = new stdClass();
$settings->options = $options;
$settings->info = get_string('ldapcheckhelp', 'module.monitor');
$cli->setup($settings);

$instances = @MonitorType_ldaplookup::get_ldap_instances();
$failedinstancesarray = MonitorType_ldaplookup::extract_failed_instances($instances);
$totalinstances = count($failedinstancesarray);
if ($totalinstances > 0) {
    $failedinstances = implode(',', $failedinstancesarray);

    $cli->cli_exit(get_string('checkingldapinstancesfail', 'module.monitor', $totalinstances, get_config('sitename'), $failedinstances, $totalinstances), 2);
}

if (!$cli->get_cli_param('okmessagedisabled')) {
    $cli->cli_exit(get_string('checkingldapinstancessucceed', 'module.monitor', get_config('sitename')), 0);
}

$cli->cli_exit(null, 0);
