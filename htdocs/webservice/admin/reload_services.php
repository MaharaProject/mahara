<?php
/**
 * Reload web services config
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);

// disable the WSDL cache
ini_set("soap.wsdl_cache_enabled", "0");

define('PUBLIC', 1);

// must be run from the command line
if (isset($_SERVER['REMOTE_ADDR']) || isset($_SERVER['GATEWAY_INTERFACE'])) {
    die('Direct access to this script is forbidden.');
}

// disable the WSDL cache
ini_set("soap.wsdl_cache_enabled", "0");

// Catch anything that goes wrong in init.php
ob_start();
    require(dirname(dirname(dirname(__FILE__))) . '/init.php');
    $errors = trim(ob_get_contents());
ob_end_clean();

require_once(get_config('docroot') . 'webservice/lib.php');

// reload/upgrade the web services configuration
external_reload_webservices();

log_info('web service plugins reloaded');