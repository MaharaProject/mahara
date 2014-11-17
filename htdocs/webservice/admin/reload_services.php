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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

/**
 * Reload web services config
 *
 * @author     Piers Harding
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package web service
 * @copyright  Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 */
define('INTERNAL', 1);

// disable the WSDL cache
ini_set("soap.wsdl_cache_enabled", "0");

define('PUBLIC', 1);

// must be run from the command line
if (isset($_SERVER['REMOTE_ADDR']) || isset($_SERVER['GATEWAY_INTERFACE'])){
    die('Direct access to this script is forbidden.');
}

// disable the WSDL cache
ini_set("soap.wsdl_cache_enabled", "0");

// Catch anything that goes wrong in init.php
ob_start();
    require(dirname(dirname(dirname(__FILE__))) . '/init.php');
    $errors = trim(ob_get_contents());
ob_end_clean();

require_once(get_config('docroot') . 'webservice/lib.php');

// reload/upgrade the web services configuration
external_reload_webservices();

error_log('web service plugins reloaded');