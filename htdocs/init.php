<?php
/**
 * Copyright 2006,2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
 *
 * This file is part of maraha.
 *
 * maraha is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.

 * maraha is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with maraha; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// set up basic error handling here


unset($CFG);
$CFG = new StdClass;
$CFG->docroot = dirname(__FILE__);

// figure out our include path
$CFG->libroot = dirname(dirname(__FILE__)).'/lib/';
if (array_key_exists('MAHARA_LIBDIR',$_SERVER) && !empty($_SERVER['MAHARA_LIBDIR'])) {
    $CFG->libroot = $_SERVER['MAHARA_LIBDIR'];
}

set_include_path('.'.PATH_SEPARATOR.$CFG->libroot);

if (!file_exists($CFG->libroot.'config.php') || !is_readable($CFG->libroot.'config.php')) {
    trigger_error("Not installed! Please create config.php from config-dist.php");
}

require('config.php');
$CFG = (object)array_merge((array)$cfg,(array)$CFG);

require('mahara.php');
ensure_sanity();

require('adodb/adodb.inc.php'); // Database access functions
$db = &ADONewConnection($CFG->dbtype);

ob_start();
if (!empty($CFG->dbport)) {
    $CFG->dbhost .= ':'.$CFG->dbport;
}
if (!isset($CFG->dbpersist) or !empty($CFG->dbpersist)) {    // Use persistent connection (default)
    $dbconnected = $db->PConnect($CFG->dbhost,$CFG->dbuser,$CFG->dbpass,$CFG->dbname);
} else {                                                     // Use single connection
    $dbconnected = $db->Connect($CFG->dbhost,$CFG->dbuser,$CFG->dbpass,$CFG->dbname);
}
if (empty($dbconnected)) {
    $errormessage = ob_get_contents();
    ob_end_clean();
    trigger_error(get_string('dbconnfailed','error',$errormessage));
}
ob_end_clean();


?>