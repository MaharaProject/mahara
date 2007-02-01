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
set_include_path($CFG->libroot . PATH_SEPARATOR . $CFG->libroot . 'pear/');

// Set up error handling
require('errors.php');

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
require('user.php');

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
    if (!$errormessage) {
        $errormessage = $e->getMessage();
    }
    ob_end_clean();
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

// Make sure wwwroot is set and available, either in the database or int the
// config file. Cron requires it for some purposes.
if (!isset($CFG->wwwroot) && isset($_SERVER['HTTP_HOST'])) {
    $proto = (isset($_SERVER['HTTPS'])) ? 'https://' : 'http://';
    $host  =  (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
    $path  = substr(dirname(__FILE__), strlen($_SERVER['DOCUMENT_ROOT']));
    if ($path) {
        $path .= '/';
    }
    $wwwroot = $proto . $host . '/' . $path;
    try {
        set_config('wwwroot', $wwwroot);
    }
    catch (Exception $e) {
        // Just set it directly. The system will most likely not be installed, so we don't care
        $CFG->wwwroot = $wwwroot;
    }
}
if (!isset($CFG->noreplyaddress) && isset($_SERVER['HTTP_HOST'])) {
    $noreplyaddress = 'noreply@' .
        ((isset($_SERVER['HTTP_X_FORWARDED_HOST'])) 
         ? $_SERVER['HTTP_X_FORWARDED_HOST'] 
         : $_SERVER['HTTP_HOST']);
    try {
        set_config('noreplyaddress', $noreplyaddress);
    }
    catch (Exception $e) {
        // Do nothing again, same reason as above
        $CFG->noreplyaddress = $noreplyaddress;
    }
}

if (!get_config('theme')) { 
    // if it's not set, we're probably not installed, 
    // so set it in $CFG directly rather than the db which doesn't yet exist
    $CFG->theme = 'default'; 
}

$CFG->themeurl = get_config('wwwroot') . 'theme/' . get_config('theme') . '/static/';

header('Content-type: text/html; charset=UTF-8');

// Only do authentication once we know the page theme, so that the login form
// can have the correct theming.
require('auth/lib.php');
$SESSION = new Session;
$USER    = new User($SESSION);
// The installer does its own auth_setup checking, because some upgrades may
// break logging in and so need to allow no logins.
if (!defined('INSTALLER')) {
    auth_setup();
}

// check to see if we're installed...
if (!get_config('installed')
    && false === strpos($_SERVER['SCRIPT_FILENAME'], 'admin/index.php')
    && false === strpos($_SERVER['SCRIPT_FILENAME'], 'admin/upgrade.php')
    && false === strpos($_SERVER['SCRIPT_FILENAME'], 'admin/upgrade.json.php')) {
    redirect('/admin/');
}

if (defined('JSON')) {
    $sesskey = param_variable('sesskey');
    global $USER;
    if ($sesskey === null || $USER->get('sesskey') != $sesskey) {
        redirect(get_config('wwwroot'));
    }
}

?>
