<?php
/**
 * JSON-based user token self-generation script. (Based on Moodle's
 * /login/token.php script). Because this requires the password to be
 * sent in plaintext, you should only call it over SSL. It's also
 * recommended to send the password, at least, as POST data, so
 * it doesn't accidentally get printed into any server logs.
 *
 * @param string username Mahara username of user requesting token
 * @param string password Plaintext password of user (you should only
 * send this via POST and over SSL)
 * @param string clientname (Optional) Human-readable description of the app
 * Displayed on the user's "authorized apps" list
 * @param string clientenv (Optional) Human-readable description of app's
 * environment (e.g.: Android LG-10). Also displayed on the "authorized apps"
 * list.
 * @param string clientguid (Optional) A globally unique identifier for this
 * client. Can be used to make sure this client doesn't trample on the tokens
 * used by other instances of the same client.
 * @returns object On success, a JSON object with a "token" field. On error,
 * a JSON object with an "error" field and additional fields describing the
 * error.
 *
 * @package    mahara
 * @subpackage webservice
 * @author     Dongsheng Cai <dongsheng@moodle.com>
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  2011 Dongsheng Cai <dongsheng@moodle.com>
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

define('INTERNAL', 1);
define('JSON', 1);
define('NOSESSKEY', 1);
define('PUBLIC', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('docroot') . 'webservice/lib.php');
safe_require('module', 'mobileapi');

// you must use HTTPS as token based auth is a hazzard without it
if (!is_https() && get_config('productionmode')) {
    header("HTTP/1.0 403 Forbidden - HTTPS must be used");
    die;
}

if (!PluginModuleMobileapi::is_service_ready()) {
    throw new WebserviceException(
        'featuredisabled',
        // In production mode we don't want to give too many configuration details
        // to not-yet-authorised users.
        ($CFG->productionmode ? '' : 'The site administrator needs to go to'
            . ' "Extensions -> Plugin administration -> module/mobileapi" and enable '
            . ' the mobileapi module.'),
        501
    );
}

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: false');

$username = param_variable('username');
$password = param_variable('password');

// Which service we'll generate a token for
// TODO: turn this into a system available to other plugins?
// For now I'll hard-code it to only work with this one service.
$serviceshortname = 'maharamobile'; //param_variable('service');
$servicecomponent = 'module/mobileapi'; //param_variable('component');

// Information to describe the client requesting the token
// (To help users understand the token management screen.)
$clientname = param_variable('clientname', '');
$clientenv = param_variable('clientenv', '');
$clientguid = param_variable('clientguid', '');

// Check for max login attempts so we can give a specific message about that.
$logintries = (int) get_field(
    'usr',
    'logintries',
    'username',
    $username
);
if ($logintries >= MAXLOGINTRIES) {
    throw new WebserviceException(
        'toomanyloginfailures',
        get_string('toomanytries', 'auth'),
        403
    );
}
if (!$USER->login($username, $password)) {
    throw new WebserviceException(
        'invalidlogin',
        get_string('loginfailed', 'mahara'),
        403
    );
}

try {
    // This can either die or throw an AccessTotallyDeniedException
    // and/or maybe even return false!
    $result = ensure_user_account_is_active();
    $e = false;
}
catch (AccessTotallyDeniedException $e) {
    $result = false;
}
if (!$result) {
    throw new WebserviceException(
        'accountinactive',
        ($e ? $e->getMessage() : ''),
        403
    );
}

if ($USER->get('passwordchange')) {
    throw new WebserviceException(
        'passwordchangerequired',
        'The user needs to reset their password. They must log in to the site through a web browser to do this.',
        403
    );
}

// Process the token request. (Will throw an exception if the user doesn't
// have access; just let that kill the request if so.)
$token = webservice_user_token_selfservice($serviceshortname, $servicecomponent, $clientname, $clientenv, $clientguid);

$usertoken = new stdClass();
$usertoken->token = $token;
echo json_encode($usertoken);
