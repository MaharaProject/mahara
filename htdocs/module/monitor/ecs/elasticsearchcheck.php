<?php
/**
 * Run the elastic search check cli to check if elastic search is still processing.
 *
 * @package    mahara
 * @subpackage module-monitor
 * @author     Ghada El-Zoghbi (ghada@catalyst-au.net)
 * @author     Fergus Whyte (fergusw@catalyst.net.nz)
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 * This is run as a CLI. It conforms to the Nagios plugin standard.
 *
 * See also:
 *  - http://nagios.sourceforge.net/docs/3_0/pluginapi.html
 *  - https://nagios-plugins.org/doc/guidelines.html#PLUGOUTPUT
 */

define('PUBLIC', 1);
define('INTERNAL',  1);

$MAHARA_ROOT = dirname(dirname(dirname(dirname(__FILE__)))) . '/';

if (!isset($argv) && !empty($_GET)) {
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
require_once(get_config('docroot') . '/module/monitor/type/MonitorType_elasticsearch.php');

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
$settings->info = get_string('elasticsearchcheckhelp', 'module.monitor');
$cli->setup($settings);

$failedqueuesize = MonitorType_elasticsearch::get_failed_queue_size();
if ($failedqueuesize['value'] != 0) {
    $cli->cli_exit(get_string('checkingelasticsearchfailedrecords', 'module.monitor'), 2);
}

$isqueueold = MonitorType_elasticsearch::is_queue_older_than();
if ($isqueueold['status'] == true) {
    $hours = MonitorType_elasticsearch::get_hours_to_consider_elasticsearch_record_old();
    $cli->cli_exit(get_string('checkingelasticsearcholdunprocesessedfail', 'module.monitor', $hours, $hours), 2);
}

if (!$cli->get_cli_param('okmessagedisabled')) {
    $cli->cli_exit(get_string('checkingelasticsearchsucceed', 'module.monitor'), 0);
}

$cli->cli_exit(null, 0);
