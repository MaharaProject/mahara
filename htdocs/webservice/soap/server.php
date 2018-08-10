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
 * SOAP web service entry point. The authentication is done via tokens.
 *
 * @package   webservice
 * @copyright 2009 Moodle Pty Ltd (http://moodle.com)
 * @copyright  Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Piers Harding
 */

/**
 * This is the universal server API enpoint for SOAP based calls - no matter
 * what the authentication type offered
 */

// Catch anything that goes wrong in init.php
define('INTERNAL', 1);
define('PUBLIC', 1);
define('SOAP', 1);
define('TITLE', '');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . 'webservice/soap/locallib.php');
require_once(get_config('docroot') . 'webservice/soap/classes/wsdl.php');

if (!webservice_protocol_is_enabled('soap')) {
    debugging('The server died because the web services or the SOAP protocol are not enable',
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
if (!param_variable('wstoken', null) || param_variable('wsservice', null)) {
    $authmethod = WEBSERVICE_AUTHMETHOD_USERNAME;
}
else {
    $authmethod = WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN;
}

// run the dispatcher
$server = new webservice_soap_server($authmethod);
$server->run();
die;
