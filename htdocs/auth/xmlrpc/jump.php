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

require(dirname(dirname(dirname(__FILE__))).'/init.php');
require_once(get_config('docroot') .'api/xmlrpc/client.php');
require_once(get_config('docroot') .'auth/xmlrpc/lib.php');
require_once(get_config('libroot') .'institution.php');

$remotewwwroot = param_variable('wr');
$instanceid    = param_variable('ins');
$wantsurl      = param_variable('wantsurl');

if (!get_config('enablenetworking')) {
    throw new XmlrpcClientException('Sorry - networking has been disabled for this Mahara site.');
}

$peer = new Peer();
$peer->findByWwwroot($remotewwwroot);
$url = $remotewwwroot.$peer->application->ssolandurl;

$providers = get_service_providers($USER->authinstance);
$approved  = false;

$url = start_jump_session($peer, $instanceid, $wantsurl);

if (empty($url)) {
    throw new XmlrpcClientException('DEBUG: Jump session was not started correctly or blank URL returned.'); // TODO: errors
}
redirect($url);

?>
