<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage core
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require('template.php');

$template_name = param_variable('template');

$parsed_template = template_locate($template_name);

if(empty($parsed_template)) {
    // @todo what exception should be thrown here
    throw new Exception("Couldn't find template '$template_name'");
}

if(!isset($parsed_template['css'])) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

// @todo send sensible headers here (allow browser caching, and 304 support)

header('Content-type: text/css');
echo file_get_contents($parsed_template['css']);
