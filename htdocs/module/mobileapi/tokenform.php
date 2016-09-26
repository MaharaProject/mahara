<?php
/**
  * iframe-based user token self-generation script. (Based on Moodle's
  * /local/mobile/launch.php script).
  *
  * and from there they should be able to log in via standard or SSO auth.
  * Once done, they'll be redirected back to this page, which will place
  * the token into a Javascript variable, that your app can then read.
  *
  * Note this won't (shouldn't) work if addressed directly in a normal
  * web browser, because of CORS restrictions. Webviews, however, are
  * exempt from some of the CORS rules.
  *
  * Because the user will be authenticating, this should only be called
  * over HTTPS.
  *
  * @param string clientname (Optional) Human-readable description of the app
  * Displayed on the user's "authorized apps" list
  * @param string clientenv (Optional) Human-readable description of app's
  * environment (e.g.: Android LG-10). Also displayed on the "authorized apps"
  * list.
  * @param string clientguid (Optional) A globally unique identifier for this
  * client. Can be used to make sure this client doesn't trample on the tokens
  * used by other instances of the same client.
  * @return Declares a JSON array called "mahara_token_response". Client
  *
 * @package    mahara
 * @subpackage webservice
 * @author     Juan Leyva <juan@moodle.com>
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  2014 Juan Leyva <juan@moodle.com>
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

define('INTERNAL', 1);
define('NOSESSKEY', 1);
define('PUBLIC', 1);

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot'). 'webservice/lib.php');
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

// TODO: An identifier to send back to the requestor, like Moodle uses?
//$passport = param_variable('passport');

// Send the user to the login screen; they'll be sent back here when
// they authenticate correctly.
if (!$USER->is_logged_in()) {
    $redirect = get_relative_script_path();
    if (!preg_match('/[&?]login/', $redirect)) {
        // Add "login" query param to URL
        $redirect =
            $redirect
            // Check if there are existing params, or if this is the first one.
            . ((strpos($redirect, '?') !== false) ? '&' : '?')
            . 'login';
    }
    redirect($redirect);
    exit();
}

// Process the token request.
$token = webservice_user_token_selfservice($serviceshortname, $servicecomponent, $clientname, $clientenv, $clientguid);

// TODO: Include passport in response like Moodle does?
//$usertoken->passport = $passport;

// Using smarty_core() instead of smarty() because smarty() computes a lot
// of things we don't need here.
$smarty = smarty_core();
$smarty->assign('STYLESHEETLIST', get_stylesheets_for_current_page(array(), array()));
$smarty->assign('SERIES', get_config('series'));
$smarty->assign('token', $token);
$smarty->display('module:mobileapi:tokenform.tpl');
