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

// @todo <nigel> Set up error handling. For now, use trigger_error. I will
// update all calls as necessary once error handling is finalised.

$CFG = new StdClass;
$CFG->docroot = dirname(__FILE__).'/';

// Figure out our include path
if (!empty($_SERVER['MAHARA_LIBDIR'])) {
    $CFG->libroot = $_SERVER['MAHARA_LIBDIR'];
}
else {
    $CFG->libroot = dirname(__FILE__) . '/lib/';
}
set_include_path('.' . PATH_SEPARATOR . $CFG->libroot);

// Set up error handling
require 'errors.php';

if (!is_readable($CFG->docroot . 'config.php')) {
    trigger_error('Not installed! Please create config.php from config-dist.php');
}

require('config.php');
$CFG = (object)array_merge((array)$cfg, (array)$CFG);

require('mahara.php');
require('dml.php');
ensure_sanity();

// Database access functions
require('adodb/adodb-exceptions.inc.php');
require('adodb/adodb.inc.php');

try {
    // ADODB does not provide the raw driver error message if the connection
    // fails for some reason, so we use output buffering to catch whatever
    // the error is instead.
    ob_start();
    
    $db = &ADONewConnection($CFG->dbtype);
    if (!empty($CFG->dbport)) {
        $CFG->dbhost .= ':'.$CFG->dbport;
    }
    if (!empty($CFG->dbpersist)) {    // Use persistent connection (default)
        $dbconnected = $db->PConnect($CFG->dbhost,$CFG->dbuser,$CFG->dbpass,$CFG->dbname);
    } 
    else {                                                     // Use single connection
        $dbconnected = $db->Connect($CFG->dbhost,$CFG->dbuser,$CFG->dbpass,$CFG->dbname);
    }
    
    $db->SetFetchMode(ADODB_FETCH_ASSOC);
    configure_dbconnection();

    ob_end_clean();
}
catch (Exception $e) {
    $errormessage = ob_get_contents();
    ob_end_clean();
    // @todo <nigel|penny>: At this point the raw error message can be munged from
    // $errormessage, while the $e object holds some other information (like backtrace,
    // which can be parsed with adodb_backtrace($e->gettrace());). At this point a
    // nice message should be displayed explaining the problem etc. etc.
    echo $e;
    echo $errormessage;
    die;
}

?>
