<?php
/**
 * JSON-based script to provide webservice consumers with information about this
 * site. This script should only provide public information (i.e. information
 * that could be obtained by screenscraping.)
 *
 * Returns a JSON-encoded object with the following fields:
 *
 * - maharaversion (bool) The Mahara major release number, e.g. 15.04
 * - wsenabled (bool) Whether web services (incoming requests) are enabled
 * - wsprotocols (array) A list of the protocols enabled for incoming requests
 * - mobileapienabled (bool) Whether or not the Mobile API is available
 * - mobileapiversion (int) The API number for the "maharamobile" service group.
 * - logintypes (array) The login types enabled on the site
 * -- "basic" = standard login form; can just use webservice/token.php
 * -- "sso" = SSO button or other special login form; may need webservice/tokenframe.php
 * -- "manual" = Users should be able to paste a token string into the app
 *
 * The response can also be modified by the "local_webservice_info" function,
 * if declared.
 *
 * @package    mahara
 * @subpackage webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

define('INTERNAL', 1);
define('JSON', 1);
define('NOSESSKEY', 1);
define('PUBLIC', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('docroot') . 'webservice/lib.php');
require_once(get_config('docroot') . 'module/mobileapi/lib.php');

$response = [];
$response['maharaversion'] = get_config('series');
$response['wwwroot'] = get_config('wwwroot');
$response['sitename'] = get_config('sitename');

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: false');


if (!$CFG->webservice_provider_enabled) {
    $response['wsenabled'] = false;
    echo json_encode((object)$response);
    exit();
}

$response['wsenabled'] = true;
$response['wsprotocols'] = [];
foreach (array('soap', 'xmlrpc', 'rest', 'oauth') as $proto) {
    if (get_config('webservice_provider_' . $proto . '_enabled')) {
        $response['wsprotocols'][] = $proto;
    }
}

/**
 * TODO: Come up with a generic way for other webservices to indicate they should expose
 * their API versions.
 */
$response['mobileapienabled'] = (bool) PluginModuleMobileapi::is_service_ready();
$response['mobileapiversion'] = get_field('external_services', 'apiversion', 'shortname', 'maharamobile', 'component', 'module/mobileapi');
$response['logintypes'] = [];
require_once(get_config('docroot') . 'auth/lib.php');
$authplugins = auth_get_enabled_auth_plugins();
$sso = false;
$basic = false;
foreach ($authplugins as $plugin) {
    $classname = 'PluginAuth' . ucfirst(strtolower($plugin));
    $pluginelements = call_static_method($classname, 'login_form_elements');
    if (!empty($pluginelements)) {
        $sso = true;
    }
    if (call_static_method($classname, 'need_basic_login_form')) {
        $basic = true;
    }
}
if ($basic) {
    $response['logintypes'][] = 'basic';
}
if ($sso) {
    $response['logintypes'][] = 'sso';
}
if (get_config_plugin('module', 'mobileapi', 'manualtokens')) {
    $response['logintypes'][] = 'manual';
}

if (function_exists('local_webservice_info')) {
    local_webservice_info($response);
}

echo json_encode((object)$response);