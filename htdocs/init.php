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
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$CFG = new StdClass;
$CFG->docroot = dirname(__FILE__) . '/';
//array containing site options from database that are overrided by $CFG
$OVERRIDDEN = array();

// Figure out our include path
if (!empty($_SERVER['MAHARA_LIBDIR'])) {
    $CFG->libroot = $_SERVER['MAHARA_LIBDIR'];
}
else {
    $CFG->libroot = dirname(__FILE__) . '/lib/';
}
set_include_path($CFG->libroot . PATH_SEPARATOR . $CFG->libroot . 'pear/' . PATH_SEPARATOR . get_include_path());

// Ensure that, by default, the response is not cached
header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
header('Expires: '. gmdate('D, d M Y H:i:s', 507686400) .' GMT');
header('Pragma: no-cache');

// Set up error handling
require('errors.php');

if (!is_readable($CFG->docroot . 'config.php')) {
    // @todo Later, this will redirect to the installer script. For now, we
    // just log and exit.
    log_environ('Not installed! Please create config.php from config-dist.php');
    exit;
}

init_performance_info();

require($CFG->docroot . 'config.php');
$CFG = (object)array_merge((array)$cfg, (array)$CFG);
require_once('config-defaults.php');
$CFG = (object)array_merge((array)$cfg, (array)$CFG);

// Fix up paths in $CFG
foreach (array('docroot', 'dataroot') as $path) {
    $CFG->{$path} = (substr($CFG->{$path}, -1) != '/') ? $CFG->{$path} . '/' : $CFG->{$path};
}

// xmldb stuff
$CFG->xmldbdisablenextprevchecking = true;
$CFG->xmldbdisablecommentchecking = true;

// ensure directorypermissions is set
if (empty($CFG->directorypermissions)) {
    $CFG->directorypermissions = 0700;
}

// core libraries
require('mahara.php');
ensure_sanity();
require('dml.php');
require('web.php');
require('user.php');
require(get_config('docroot') . 'local/lib.php');

// Database access functions
require('adodb/adodb-exceptions.inc.php');
require('adodb/adodb.inc.php');

try {
    // ADODB does not provide the raw driver error message if the connection
    // fails for some reason, so we use output buffering to catch whatever
    // the error is instead.
    ob_start();

    if (is_postgres()) {
        $CFG->dbtype = 'postgres7';
    }
    else if (is_mysql()) {
        $CFG->dbtype = 'mysql';
    }
    
    $db = &ADONewConnection($CFG->dbtype);
    if (empty($CFG->dbhost)) {
        $CFG->dbhost = '';
    }
    else if (!empty($CFG->dbport)) {
        $CFG->dbhost .= ':'.$CFG->dbport;
    }
    if (!empty($CFG->dbpersist)) {    // Use persistent connection (default)
        $dbconnected = $db->PConnect($CFG->dbhost,$CFG->dbuser,$CFG->dbpass,$CFG->dbname);
    } 
    else {                                                     // Use single connection
        $dbconnected = $db->Connect($CFG->dbhost,$CFG->dbuser,$CFG->dbpass,$CFG->dbname);
    }

    // Now we have a connection, verify the server is a new enough version
    $dbversion = $db->ServerInfo();
    if (is_postgres()) {
        $okversion = '8.1';
        $dbfriendlyname = 'PostgreSQL';
    }
    else if (is_mysql()) {
        $okversion = '5.0.25';
        $dbfriendlyname = 'MySQL';
    }
    if ($dbversion['version'] < $okversion) {
        throw new ConfigSanityException(get_string('dbversioncheckfailed', 'error', $dbfriendlyname, $dbversion['version'], $okversion));
    }

    $db->SetFetchMode(ADODB_FETCH_ASSOC);
    configure_dbconnection();
    ensure_internal_plugins_exist();

    ob_end_clean();
}
catch (Exception $e) {
    if ($e instanceof ConfigSanityException) {
        throw $e;
    }
    $errormessage = ob_get_contents();
    if (!$errormessage) {
        $errormessage = $e->getMessage();
    }
    ob_end_clean();
    $errormessage = get_string('dbconnfailed', 'error') . $errormessage;
    throw new ConfigSanityException($errormessage);
}
try {
    db_ignore_sql_exceptions(true);
    load_config();
    db_ignore_sql_exceptions(false);
} 
catch (SQLException $e) {
    db_ignore_sql_exceptions(false);
}

// Make sure wwwroot is set and available, either in the database or in the
// config file. Cron requires it when sending out forums emails.
if (!isset($CFG->wwwroot) && isset($_SERVER['HTTP_HOST'])) {
    $proto = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ? 'https://' : 'http://';
    $host  =  (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
    if (false !== strpos($host, ',')) {
        list($host) = explode(',', $host);
        $host = trim($host);
    }
    $path = '';
    if (strpos(dirname(__FILE__), strlen($_SERVER['DOCUMENT_ROOT'])) === 0) {
        $path  = substr(dirname(__FILE__), strlen($_SERVER['DOCUMENT_ROOT']));
    }
    if ($path) {
        $path = str_replace('\\', '/', $path);  // windows
        if (substr($path, 0, 1) != '/') {
            $path = '/' . $path;
        }
        $path .= '/';
    } else {
        $path = '/';
    }
    $wwwroot = $proto . $host . $path;
    try {
        set_config('wwwroot', $wwwroot);
    }
    catch (Exception $e) {
        // Just set it directly. The system will most likely not be installed, so we don't care
        $CFG->wwwroot = $wwwroot;
    }
}
if (isset($CFG->wwwroot)) {
    if (substr($CFG->wwwroot, -1, 1) != '/') {
        $CFG->wwwroot .= '/';
    }
}
// Make sure that we are using ssl if wwwroot expects us to do so
if (isset($_SERVER['REMOTE_ADDR']) && (!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off') &&
    parse_url($CFG->wwwroot, PHP_URL_SCHEME) === 'https'){
    redirect(get_relative_script_path());
}
if (!isset($CFG->noreplyaddress) && isset($_SERVER['HTTP_HOST'])) {
    $noreplyaddress = 'noreply@';
    $host  =  (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
    if (false !== strpos($host, ',')) {
        list($host) = explode(',', $host);
        $host = trim($host);
    }
    $noreplyaddress .= $host;
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

if (defined('INSTALLER')) { 
    // Custom themes sometimes cause upgrades to fail.
    $CFG->theme = 'default';
}

// Make sure the search plugin is configured
if (!get_config('searchplugin')) {
    try {
        set_config('searchplugin', 'internal');
    }
    catch (Exception $e) {
        $CFG->searchplugin = 'internal';
    }
}
header('Content-type: text/html; charset=UTF-8');

// Only do authentication once we know the page theme, so that the login form
// can have the correct theming.
require_once('auth/lib.php');
$SESSION = Session::singleton();
$USER    = new LiveUser();

// try to set the theme, or catch the thrown exception (eg if the name is invalid)
try {
    $THEME   = new Theme($USER);
} catch (SystemException $exception) {
    // set the theme to 'default' and put up an error message
    $THEME = new Theme('default');
    $SESSION->add_error_msg($exception->getMessage());
}

// The installer does its own auth_setup checking, because some upgrades may
// break logging in and so need to allow no logins.
if (!defined('INSTALLER')) {
    auth_setup();
}

$siteclosedforupgrade = get_config('siteclosed');
if ($siteclosedforupgrade && $USER->admin) {
    if (get_config('disablelogin')) {
        $USER->logout();
    }
    else if (!defined('INSTALLER')) {
        redirect('/admin/upgrade.php');
    }
}

$siteclosed = $siteclosedforupgrade || get_config('siteclosedbyadmin');
if ($siteclosed && !$USER->admin) {
    if ($USER->is_logged_in()) {
        $USER->logout();
    }
    if (!defined('HOME') && !defined('INSTALLER')) {
        redirect();
    }
}

// check to see if we're installed...
if (!get_config('installed')) {
    ensure_install_sanity();

    $scriptfilename = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
    if (false === strpos($scriptfilename, 'admin/index.php')
    && false === strpos($scriptfilename, 'admin/upgrade.php')
    && false === strpos($scriptfilename, 'admin/upgrade.json.php')) {
        redirect('/admin/');
    }
}

if (defined('JSON') && !defined('NOSESSKEY')) {
    $sesskey = param_variable('sesskey', null);
    global $USER;
    if ($sesskey === null || $USER->get('sesskey') != $sesskey) {
        $USER->logout();
        json_reply('global', get_string('invalidsesskey'), 1);
    }
}

/*
 * Initializes our performance info early.
 *
 * Pairs up with get_performance_info() which is actually
 * in lib/mahara.php. This function is here so that we can 
 * call it before all the libs are pulled in. 
 *
 * @uses $PERF
 */
function init_performance_info() {

    global $PERF;
  
    $PERF = new StdClass;
    $PERF->dbreads = $PERF->dbwrites = $PERF->dbcached = 0;
    $PERF->logwrites = 0;
    if (function_exists('microtime')) {
        $PERF->starttime = microtime();
        }
    if (function_exists('memory_get_usage')) {
        $PERF->startmemory = memory_get_usage();
    }
    if (function_exists('posix_times')) {
        $PERF->startposixtimes = posix_times();  
    }
}
