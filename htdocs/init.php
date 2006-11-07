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
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$CFG = new StdClass;
$CFG->docroot = dirname(__FILE__) . '/';

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
    // @todo Later, this will redirect to the installer script. For now, we
    // just log and exit.
    log_environ('Not installed! Please create config.php from config-dist.php');
    exit;
}

require('config.php');
$CFG = (object)array_merge((array)$cfg, (array)$CFG);

// Fix up paths in $CFG
foreach (array('docroot', 'dataroot') as $path) {
    $CFG->{$path} = (substr($CFG->{$path}, -1) != DIRECTORY_SEPARATOR) ? $CFG->{$path} . DIRECTORY_SEPARATOR : $CFG->{$path};
}
if (!isset($CFG->wwwroot) && isset($_SERVER['HTTP_HOST'])) {
    $proto = (isset($_SERVER['HTTPS'])) ? 'https://' : 'http://';
    $host  =  (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
    $path  = substr(dirname(__FILE__), strlen($_SERVER['DOCUMENT_ROOT']));
    if ($path) {
        $path .= '/';
    }
    $CFG->wwwroot = $proto . $host . '/' . $path;
}
if (!isset($CFG->noreplyaddress) && isset($_SERVER['HTTP_HOST'])) {
    $CFG->noreplyaddress = 'noreply@' .
        ((isset($_SERVER['HTTP_X_FORWARDED_HOST'])) 
         ? $_SERVER['HTTP_X_FORWARDED_HOST'] 
         : $_SERVER['HTTP_HOST']);
    error_log("set to $CFG->noreplyaddress");
}

// xmldb stuff
$CFG->xmldbdisablenextprevchecking = true;
$CFG->xmldbdisablecommentchecking = true;

// core libraries
require('mahara.php');
ensure_sanity();
require('dml.php');
require('ddl.php');
require('constants.php');
require('web.php');
require('activity.php');

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
    ensure_internal_plugins_exist();

    ob_end_clean();
}
catch (Exception $e) {
    $errormessage = ob_get_contents();
    ob_end_clean();
    // @todo <nigel|penny>: At this point the raw error message can be munged from
    // $errormessage, while the $e object holds some other information (like backtrace,
    // which can be parsed with adodb_backtrace($e->gettrace());). At this point a
    // nice message should be displayed explaining the problem etc. etc.
    throw new Exception($errormessage);
}
try {
    load_config();
} 
catch (SQLException $e) {
    if (!defined('INSTALLER')) {
        throw $e;
    }
}

if (!get_config('theme')) { 
    // if it's not set, we're probably not installed, 
    // so set it in $CFG directly rather than the db which doesn't yet exist
    $CFG->theme = 'default'; 
}

$CFG->themeurl = get_config('wwwroot') . 'theme/' . get_config('theme') . '/static/';

// Only do authentication once we know the page theme, so that the login form
// can have the correct theming.
require('auth/lib.php');
$USER = auth_setup();

?>
