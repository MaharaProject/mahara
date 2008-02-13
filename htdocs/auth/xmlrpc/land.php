<?php

/**
 * Authentication Plugin: Moodle Network Authentication
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @package mahara
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 * This file incorporates work covered by the following copyright and
 * permission notice:
 *
 *    Moodle - Modular Object-Oriented Dynamic Learning Environment
 *             http://moodle.com
 *
 *    Copyright (C) 2001-3001 Martin Dougiamas        http://dougiamas.com
 *
 *    This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details:
 *
 *             http://www.gnu.org/copyleft/gpl.html
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

// If the user is already logged in as someone, log them out. That way, if 
// XMLRPC authentication fails, the system isn't left looking stupid as it 
// reports that the user couldn't log in while they actually are.
if ($USER->is_logged_in()) {
    $USER->logout();
}
$SESSION->set('messages', array());

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
    throw new XmlrpcUserNotFoundException(get_string('errnoxmlrcpinstances','auth').htmlentities($remotewwwroot, ENT_QUOTES, 'UTF-8'));
} else {
    throw new XmlrpcUserNotFoundException(get_string('errnoxmlrpcuser','auth'));
}
?>
