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

require(dirname(dirname(dirname(__FILE__))).'/init.php');
require_once(get_config('docroot') .'api/xmlrpc/client.php');
require_once(get_config('docroot') .'auth/xmlrpc/lib.php');
require_once(get_config('docroot') .'include/eLearning/institution.php');

$remotewwwroot = param_variable('wr');
$instanceid    = param_variable('ins');
$wantsurl      = '';

$peer = new Peer();
$peer->findByWwwroot($remotewwwroot);
$url = $remotewwwroot.$peer->application->ssolandurl;

$providers = get_service_providers($USER->authinstance);
$approved  = false;

$url = start_jump_session($peer, $instanceid);

if (empty($url)) {
    throw new Exception('DEBUG: Jump session was not started correctly or blank URL returned.'); // TODO: errors
}
redirect($url);

?>