<?php

/**
 * @author Martin Dougiamas
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moodle multiauth
 *
 * Authentication Plugin: Moodle Network Authentication
 *
 * Multiple host authentication support for Moodle Network.
 *
 * 2006-11-01  File created.
 */

define('INTERNAL', 1);
define('PUBLIC', 1);



require(dirname(dirname(dirname(__FILE__))).'/init.php');

// If networking is turned off, it's safer to die immediately
if (!get_config('enablenetworking')) {
    $protocol = strtoupper($_SERVER['SERVER_PROTOCOL']);
    if ($protocol != 'HTTP/1.1') {
        $protocol = 'HTTP/1.0';
    }
    header($protocol.' 403 Forbidden');
    exit;
}

require_once(get_config('docroot') .'api/xmlrpc/client.php');
require_once(get_config('docroot') .'auth/xmlrpc/lib.php');
require_once(get_config('libroot') .'institution.php');

$token         = param_variable('token');
$remotewwwroot = param_variable('idp');
$wantsurl      = param_variable('wantsurl', '/');

$institution = new Institution();

try {
    $institution->findByWwwroot($remotewwwroot);
} catch (ParamOutOfRangeException $e) {
    throw new ParameterException(get_string('errnoxmlrcpwwwroot','auth'). htmlentities($remotewwwroot, ENT_QUOTES, 'UTF-8'));
}

$instances = auth_get_auth_instances_for_wwwroot($remotewwwroot);

if (empty($instances)) {
    throw new ParameterException(get_string('errnoauthinstances','auth'). htmlentities($remotewwwroot, ENT_QUOTES, 'UTF-8'));
}

$rpcconfigured = false;

$res = false;
foreach($instances as $instance) {
    if ($instance->authname == 'xmlrpc') {
        $rpcconfigured = true;
        try {
            $auth = new AuthXmlrpc($instance->id);
            $res = $auth->request_user_authorise($token, $remotewwwroot);
        } catch (AccessDeniedException $e) {
            continue;
            // we don't care - a future plugin might accept the user
        }
        catch (Exception $e) {
            log_info($e);
            continue;
        }
        if ($res == true) {
            break;
        }
    }
}

if ($res == true) {
    // Everything's ok - we have an authenticated User object
    // confirm the MNET session
    // redirect
    redirect(get_config('wwwroot') . $wantsurl);
    // Redirect exits
}

if ($rpcconfigured === false) {
    throw new UserNotFoundException(get_string('errnoxmlrcpinstances','auth').htmlentities($remotewwwroot, ENT_QUOTES, 'UTF-8'));
} else {
    throw new UserNotFoundException(get_string('errnoxmlrcpuser','auth'));
}
?>
