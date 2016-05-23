<?php
/**
 *
 * @package    mahara
 * @subpackage auth-saml
 * @author     Piers Harding <piers@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
global $CFG, $USER, $SESSION;
require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('docroot') . 'auth/saml/lib.php');
require_once(get_config('libroot') . 'institution.php');

// check that the plugin is active
if (get_field('auth_installed', 'active', 'name', 'saml') != 1) {
    redirect();
}

if (!extension_loaded('mcrypt')) {
    throw new AuthInstanceException(get_string('errornomcrypt', 'auth.saml'));
}

if (!file_exists(get_config('docroot') . 'auth/saml/extlib/simplesamlphp/vendor/autoload.php')) {
    throw new AuthInstanceException(get_string('errorbadlib', 'auth.saml', get_config('docroot') . 'auth/saml/extlib/simplesamlphp/vendor/autoload.php'));
}
require_once(get_config('docroot') . 'auth/saml/extlib/simplesamlphp/vendor/autoload.php');
require_once(get_config('docroot') . 'auth/saml/extlib/_autoload.php');
SimpleSAML_Configuration::init(get_config('docroot') . 'auth/saml/config');

require('../extlib/simplesamlphp/modules/saml/www/disco.php');
