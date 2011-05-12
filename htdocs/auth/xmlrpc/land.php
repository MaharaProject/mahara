<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @package    mahara
 * @subpackage auth-xmlrpc
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
    throw new AccessTotallyDeniedException(get_string('networkingdisabledonthissite', 'auth.xmlrpc'));
}

require_once(get_config('docroot') .'api/xmlrpc/client.php');
require_once(get_config('docroot') .'auth/xmlrpc/lib.php');
require_once(get_config('libroot') .'institution.php');

$token         = param_variable('token');
$remotewwwroot = param_variable('idp');
$wantsurl      = param_variable('wantsurl', '/');
$remoteurl     = param_boolean('remoteurl');

$len = strlen($remotewwwroot);
if ($len < 1 || $len > 255) {
    throw new ParameterException(get_string('errnoxmlrpcwwwroot','auth', $remotewwwroot));
}

$instances = auth_get_auth_instances_for_wwwroot($remotewwwroot);

if (empty($instances)) {
    throw new ParameterException(get_string('errnoauthinstances','auth', $remotewwwroot));
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
        if (!$instance->suspended) {
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
        else {
          $sitename = get_config('sitename');
          throw new AccessTotallyDeniedException(get_string('accesstotallydenied_institutionsuspended', 'mahara', $instance->displayname, $sitename));
        }
    }
}

if ($res == true) {
    // Everything's ok - we have an authenticated User object
    // confirm the MNET session
    // redirect
    if ($remoteurl) {
        redirect($remotewwwroot . $wantsurl);
    }
    redirect(get_config('wwwroot') . $wantsurl);
    // Redirect exits
}

if ($rpcconfigured === false) {
    throw new XmlrpcUserNotFoundException(get_string('errnoxmlrpcinstances','auth', $remotewwwroot));
} else {
    throw new XmlrpcUserNotFoundException(get_string('errnoxmlrpcuser','auth'));
}
