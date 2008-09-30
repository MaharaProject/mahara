<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

$cfg = new StdClass;


// database connection details
// valid values for dbtype are 'postgres8' and 'mysql'
$cfg->dbtype   = 'postgres8';
$cfg->dbhost   = 'localhost';
$cfg->dbport   = null;
$cfg->dbname   = '';
$cfg->dbuser   = '';
$cfg->dbpass   = '';
$cfg->dbprefix = '';

// wwwroot - the web-visible path to your Mahara installation
// Normally, this is automatically detected - if it doesn't work for you
// then try specifying it here
//$cfg->wwwroot = 'http://myhost.com/mahara/';

// dataroot - uploaded files are stored here
// must be writable by the webserver and outside document root.
// Mahara will NOT RUN if this is inside your document root, because
// this is a big security hole.
$cfg->dataroot = '/path/to/uploaddir';

?>
