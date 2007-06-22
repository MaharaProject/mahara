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
define('XMLRPC', 1);



require(dirname(dirname(dirname(__FILE__))).'/init.php');
require_once(get_config('docroot') .'api/xmlrpc/client.php');
require_once(get_config('docroot') .'auth/xmlrpc/lib.php');
require_once(get_config('docroot') .'include/eLearning/institution.php');

$token         = param_variable('token');
$remotewwwroot = param_variable('idp');
$wantsurl      = param_variable('wantsurl', '/');


$institution = new Institution();
$institution->findByWwwroot($remotewwwroot);
$instances = auth_get_auth_instances_for_wwwroot($remotewwwroot);

foreach($instances as $instance) {
    if ($instance->authname == 'xmlrpc') {
        try {
            $auth = new AuthXmlrpc($instance->id);
            $res = $auth->request_user_authorise($token, $remotewwwroot);
        } catch (Exception $e) {
            continue;
            // we don't care
        }
        if ($res instanceof User) {
            break;
        }
    }
}
// confirm the MNET session
// redirect
redirect(get_config('wwwroot') . $wantsurl);

?>
