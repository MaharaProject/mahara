<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2009 Moodle Pty Ltd (http://moodle.com)
 * Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * REST web service entry point. The authentication is done via tokens.
 *
 * @package   webservice
 * @copyright 2009 Moodle Pty Ltd (http://moodle.com)
 * @copyright  Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Piers Harding
 */

/**
 * This is the universal server API enpoint for REST based calls - no matter
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
    } else {
        ini_set('opcache.load_comments', 1);
    }
}

// Catch anything that goes wrong in init.php
ob_start();
    require(dirname(dirname(dirname(__FILE__))) . '/init.php');
    $errors = trim(ob_get_contents());
ob_end_clean();
require_once(get_config('docroot') . "webservice/rest/locallib.php");

if (!webservice_protocol_is_enabled('rest')) {
    debugging('The server died because the web services or the REST protocol are not enable',
        DEBUG_DEVELOPER);
    header("HTTP/1.0 403 Forbidden");
    die;
}

// you must use HTTPS as token based auth is a hazzard without it
if (!is_https()) {
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
$server = new webservice_rest_server($authmethod);
$server->run();
die;
