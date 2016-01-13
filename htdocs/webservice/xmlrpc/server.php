<?php
/**
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * XML-RPC web service entry point. The authentication is done via tokens.
 *
 * @package   webservice
 * @copyright 2009 Moodle Pty Ltd (http://moodle.com)
 * @copyright  Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Piers Harding
 */

/**
 * This is the universal server API enpoint for XML-RPC based calls - no matter
 * what the authentication type offered
 */

// disable external entities
libxml_disable_entity_loader(true);

define('INTERNAL', 1);
define('PUBLIC', 1);
define('XMLRPC', 1);
define('TITLE', '');

// Make sure OPcache does not strip comments, we need them for Zend!
if (ini_get('opcache.enable') and strtolower(ini_get('opcache.enable')) !== 'off') {
    if (!ini_get('opcache.save_comments') or strtolower(ini_get('opcache.save_comments')) === 'off') {
        ini_set('opcache.enable', 0);
    }
    else {
        ini_set('opcache.load_comments', 1);
    }
}

// Catch anything that goes wrong in init.php
ob_start();
    require(dirname(dirname(dirname(__FILE__))) . '/init.php');
    $errors = trim(ob_get_contents());
ob_end_clean();
require_once(get_config('docroot') . 'webservice/xmlrpc/locallib.php');

if (!webservice_protocol_is_enabled('xmlrpc')) {
    debugging('The server died because the web services or the XMLRPC protocol are not enable',
        DEBUG_DEVELOPER);
    header("HTTP/1.0 404 Not Found");
    die;
}

// you must use HTTPS as token based auth is a hazzard without it
if (!is_https() && get_config('productionmode')) {
    header("HTTP/1.0 403 Forbidden - HTTPS must be used");
    die;
}

// make a guess as to what the auth method is - this gets refined later
if (param_variable('wsusername', null) || param_variable('wspassword', null)) {
    $authmethod = WEBSERVICE_AUTHMETHOD_USERNAME;
}
else {
    $authmethod = WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN;
}

// run the dispatcher
$server = new webservice_xmlrpc_server($authmethod);
$server->run();
die;
