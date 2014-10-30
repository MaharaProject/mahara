<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

if (defined('CLI') && php_sapi_name() != 'cli') {
    die();
}

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

// Prevent clickjacking through iframe tags
header('X-Frame-Options: SAMEORIGIN');

// Set up error handling
require('errors.php');

if (!is_readable($CFG->docroot . 'config.php')) {
    // @todo Later, this will redirect to the installer script. For now, we
    // just log and exit.
    log_environ('Not installed! Please create config.php from config-dist.php');
    exit;
}

init_performance_info();

// Because the default XML loader is vulnerable to XEE attacks, we're disabling it by default.
// If you need to use it, you can re-enable the function, call it while passing in the
// LIBXML_NONET parameter, and then disable the function again, like this:
//
// EXAMPLE
//     if (function_exists('libxml_disable_entity_loader')) {
//         libxml_disable_entity_loader(false);
//     }
//     $options =
//         LIBXML_COMPACT |    // Reported to greatly speed XML parsing
//         LIBXML_NONET        // Disable network access - security check
//     ;
//     $xml = simplexml_load_file($filename, 'SimpleXMLElement', $options);
//     if (function_exists('libxml_disable_entity_loader')) {
//         libxml_disable_entity_loader(true);
//     }
// END EXAMPLE
if (function_exists('libxml_disable_entity_loader')) {
    libxml_disable_entity_loader(true);
}

require($CFG->docroot . 'config.php');
$CFG = (object)array_merge((array)$cfg, (array)$CFG);
require_once('config-defaults.php');
$CFG = (object)array_merge((array)$cfg, (array)$CFG);

// Fix up paths in $CFG
foreach (array('docroot', 'dataroot') as $path) {
    $CFG->{$path} = (substr($CFG->{$path}, -1) != '/') ? $CFG->{$path} . '/' : $CFG->{$path};
}

// Set default configs that are dependent on the docroot and dataroot
if (empty($CFG->sessionpath)) {
    $CFG->sessionpath = $CFG->dataroot . 'sessions';
}

// xmldb stuff
$CFG->xmldbdisablenextprevchecking = true;
$CFG->xmldbdisablecommentchecking = true;

// ensure directorypermissions is set
if (empty($CFG->directorypermissions)) {
    $CFG->directorypermissions = 0700;
}
$CFG->filepermissions = $CFG->directorypermissions & 0666;

// Now that we've loaded the configs, we can override the default error settings
// from errors.php
$errorlevel = $CFG->error_reporting;
error_reporting($errorlevel);
set_error_handler('error', $errorlevel);

// core libraries
require('mahara.php');
ensure_sanity();
require('dml.php');
require('web.php');
require('user.php');
// Optional local/lib.php file
$locallib = get_config('docroot') . 'local/lib.php';
if (file_exists($locallib)) {
    require($locallib);
}

// Database access functions
require('adodb/adodb-exceptions.inc.php');
require('adodb/adodb.inc.php');

try {
    // ADODB does not provide the raw driver error message if the connection
    // fails for some reason, so we use output buffering to catch whatever
    // the error is instead.
    ob_start();

    // Transform $CFG->dbtype into the name of the ADODB driver we will use
    if (is_postgres()) {
        $CFG->dbtype = 'postgres7';
    }
    else if (is_mysql()) {
        // If they have mysqli, use it. Otherwise, fall back to the older "mysql" extension.
        if (extension_loaded('mysqli')) {
            $CFG->dbtype = 'mysqli';
        }
        else {
            $CFG->dbtype = 'mysql';
        }
    }

    $db = ADONewConnection($CFG->dbtype);
    if (empty($CFG->dbhost)) {
        $CFG->dbhost = '';
    }
    // The ADODB connection function doesn't have a separate port argument, but the
    // postgres7, mysql, and mysqli drivers all support a $this->dbport field.
    if (!empty($CFG->dbport)) {
        $db->port = $CFG->dbport;
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
        $okversion = '8.3';
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
    else {
        $self = explode('/', $_SERVER['PHP_SELF']);
        $dir = dirname(__FILE__);
        $i = 0;
        while (realpath($_SERVER['DOCUMENT_ROOT'].$path) != $dir) {
            if ($i >= count($self) - 1) {
                $path = '';
                break;
            }
            if (empty($self[$i])) {
                $i ++;
                continue;
            }
            $path .= '/'.$self[$i];
            $i ++;
        }
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

// If we have cleanurl subdomains turned on, we need to set cookiedomain
// to ensure cookies are given back to us in all subdomains
if (isset($CFG->cleanurls) && isset($CFG->cleanurlusersubdomains) && !isset($CFG->cookiedomain)) {
    $url = parse_url(get_config('wwwroot'));
    $CFG->cookiedomain = '.' . $url['host'];
}

// If we're forcing an ssl proxy, make sure the wwwroot is correct
if ($CFG->sslproxy == true && parse_url($CFG->wwwroot, PHP_URL_SCHEME) !== 'https') {
    throw new ConfigSanityException(get_string('wwwrootnothttps', 'error', get_config('wwwroot')));
}

// Make sure that we are using ssl if wwwroot expects us to do so
if ($CFG->sslproxy === false && isset($_SERVER['REMOTE_ADDR']) && (!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off') &&
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
$bcrypt_cost = get_config('bcrypt_cost');
// bcrypt_cost is the cost parameter passed as part of the bcrypt hash
// See http://php.net/manual/en/function.crypt.php
// The value is a 2 digit number in the range of 04-31
if (!$bcrypt_cost || !is_int($bcrypt_cost) || $bcrypt_cost < 4 || $bcrypt_cost > 31) {
    $bcrypt_cost = 12;
}
$CFG->bcrypt_cost = sprintf('%02d', $bcrypt_cost);

if (!get_config('productionmode')) {
    $CFG->log_dbg_targets     = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
    $CFG->log_info_targets    = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
    $CFG->log_warn_targets    = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
    $CFG->log_environ_targets = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
    $CFG->developermode       = DEVMODE_DEBUGJS | DEVMODE_DEBUGCSS | DEVMODE_UNPACKEDJS;
    $CFG->perftofoot          = true;
    $CFG->nocache             = true;
}

header('Content-type: text/html; charset=UTF-8');

// Only do authentication once we know the page theme, so that the login form
// can have the correct theming.
require_once('auth/lib.php');
$SESSION = Session::singleton();
$USER    = new LiveUser();

if (function_exists('local_init_user')) {
    local_init_user();
}

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
// Command-line scripts obviously have no logged-in user.
if (!defined('INSTALLER') && !defined('CLI')) {
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
    && false === strpos($scriptfilename, 'admin/upgrade.json.php')
    && false === strpos($scriptfilename, 'admin/cli/install.php')
    && false === strpos($scriptfilename, 'admin/cli/upgrade.php')) {
        redirect('/admin/index.php');
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

// Device detection
if (get_config('installed') && get_account_preference($USER->get('id'), 'devicedetection')) {
    require_once(get_config('libroot') . 'mobile_detect/Mobile_Detect.php');
    $detect = new Mobile_Detect();
    $SESSION->set('handheld_device', $detect->isMobile());
    $SESSION->set('mobile', $detect->isTablet() ? false : $detect->isMobile());
    $SESSION->set('tablet', $detect->isTablet());
}
else {
    $SESSION->set('handheld_device', false);
    $SESSION->set('mobile', false);
    $SESSION->set('tablet', false);
}

// Run modules bootstrap code.
if (!defined('INSTALLER')) {
    // make sure the table exists if upgrading from older version
    require_once('ddl.php');
    if (table_exists(new XMLDBTable('module_installed'))) {
        if ($plugins = plugins_installed('module')) {
            foreach ($plugins as &$plugin) {
                if (safe_require_plugin('module', $plugin->name)) {
                    call_static_method(generate_class_name('module', $plugin->name), 'bootstrap');
                }
            }
        }
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
