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
 * @subpackage core
 * @author     Andrew Nicols <andrew.nicols@luns.net.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2009 Lancaster University Network Services Limited
 *                      http://www.luns.net.uk
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('TITLE', '');

require(dirname(dirname(__FILE__)).'/init.php');

$address = $_ENV['RECIPIENT'];

log_debug('---------- started  processing email at ' . date('r', time()) . ' ----------');
log_debug('-- mail from ' . $address );

$email = process_email($address);

log_debug('---------- finished processing email at ' . date('r', time()) . ' ----------');
