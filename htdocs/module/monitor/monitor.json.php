<?php
/**
 * Run the monitoring checks to ensure there are no stuck/locked processes.
 *
 * @package    mahara
 * @subpackage module-monitor
 * @author     Ghada El-Zoghbi (ghada@catalyst-au.net)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . '/module/monitor/lib.php');
if (!PluginModuleMonitor::is_active()) {
    json_reply(true, get_string('monitormodulenotactive', 'module.monitor'));
}
require_once(get_config('docroot') . '/module/monitor/type/MonitorType_processes.php');
require_once(get_config('docroot') . '/module/monitor/type/MonitorType_ldaplookup.php');
require_once(get_config('docroot') . '/module/monitor/type/MonitorType_ldapsuspendedusers.php');


$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$type = param_alpha('type', PluginModuleMonitor::type_default);
$subpages = PluginModuleMonitor::get_list_of_types();
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

$data = array();

if (!in_array($type, $subpages)) {
    $type = PluginModuleMonitor::type_default;
}

switch ($type) {
case PluginModuleMonitor::type_ldaplookup:
    $results = MonitorType_ldaplookup::get_ldap_instances();
    $data = MonitorType_ldaplookup::format_for_display_table($results, $limit, $offset);
    break;
case PluginModuleMonitor::type_ldapsuspendedusers:
    $results = MonitorType_ldapsuspendedusers::get_ldap_suspendedusers();
    $data = MonitorType_ldaplookup::format_for_display_table($results, $limit, $offset);
    break;

case PluginModuleMonitor::type_elasticsearch:
    break;
case PluginModuleMonitor::type_processes:
default:
    $results = MonitorType_processes::get_long_running_cron_processes();
    $data = MonitorType_processes::format_for_display_table($results, $limit, $offset);
    break;
}

json_reply(false, (object) array('message' => false, 'data' => $data));
