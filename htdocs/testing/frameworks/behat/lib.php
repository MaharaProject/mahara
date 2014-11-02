<?php
/**
 * @package    mahara
 * @subpackage test/behat
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from Moodle Behat, 2013 David Monllaó
 *
 */

defined('INTERNAL') || die();

require_once(dirname(dirname(__DIR__)) . '/lib.php');

/**
 * define BEHAT error codes
 */
define('BEHAT_EXITCODE_CONFIG', 250);
define('BEHAT_EXITCODE_REQUIREMENT', 251);
define('BEHAT_EXITCODE_PERMISSIONS', 252);
define('BEHAT_EXITCODE_REINSTALL', 253);
define('BEHAT_EXITCODE_INSTALL', 254);
define('BEHAT_EXITCODE_COMPOSER', 255);
define('BEHAT_EXITCODE_INSTALLED', 256);

/**
 * Exits with an error code
 *
 * @param  mixed $errorcode
 * @param  string $text
 * @return void Stops execution with error code
 */
function behat_error($errorcode, $text = '') {

    // Adding error prefixes.
    switch ($errorcode) {
        case BEHAT_EXITCODE_CONFIG:
            $text = 'Behat config error: ' . $text;
            break;
        case BEHAT_EXITCODE_REQUIREMENT:
            $text = 'Behat requirement not satisfied: ' . $text;
            break;
        case BEHAT_EXITCODE_PERMISSIONS:
            $text = 'Behat permissions problem: ' . $text . ', check the permissions';
            break;
        case BEHAT_EXITCODE_REINSTALL:
            $path = testing_cli_argument_path('/testing/frameworks/behat/cli/init.php');
            $text = "Reinstall Behat: ".$text.", use:\n php ".$path;
            break;
        case BEHAT_EXITCODE_INSTALL:
            $path = testing_cli_argument_path('/testing/frameworks/behat/cli/init.php');
            $text = "Install Behat before enabling it, use:\n php ".$path;
            break;
        case BEHAT_EXITCODE_INSTALLED:
            $text = "The Behat site is already installed";
            break;
        default:
            $text = 'Unknown error ' . $errorcode . ' ' . $text;
            break;
    }

    testing_error($errorcode, $text);
}

/**
 * PHP errors handler to use when running behat tests.
 *
 * Adds specific CSS classes to identify
 * the messages.
 *
 * @param int $errno
 * @param string $errstr
 * @param string $errfile
 * @param int $errline
 * @param array $errcontext
 * @return bool
 */
function behat_error_handler($errno, $errstr, $errfile, $errline, $errcontext) {

    // If is preceded by an @ we don't show it.
    if (!error_reporting()) {
        return true;
    }

    // This error handler receives E_ALL | E_STRICT, running the behat test site the debug level is
    // set to DEVELOPER and will always include E_NOTICE,E_USER_NOTICE... as part of E_ALL, if the current
    // error_reporting() value does not include one of those levels is because it has been forced through
    // the mahara code in that cases we respect the forced error level value.
    $respect = array(E_NOTICE, E_USER_NOTICE, E_STRICT, E_WARNING, E_USER_WARNING);
    foreach ($respect as $respectable) {

        // If the current value does not include this kind of errors and the reported error is
        // at that level don't print anything.
        if ($errno == $respectable && !(error_reporting() & $respectable)) {
            return true;
        }
    }

    // Using the default one in case there is a fatal catchable error.
    default_error_handler($errno, $errstr, $errfile, $errline, $errcontext);

    switch ($errno) {
        case E_USER_ERROR:
            $errnostr = 'Fatal error';
            break;
        case E_WARNING:
        case E_USER_WARNING:
            $errnostr = 'Warning';
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
        case E_STRICT:
            $errnostr = 'Notice';
            break;
        case E_RECOVERABLE_ERROR:
            $errnostr = 'Catchable';
            break;
        default:
            $errnostr = 'Unknown error type';
    }

    // Wrapping the output.
    echo '<div class="phpdebugmessage" data-rel="phpdebugmessage">' . PHP_EOL;
    echo "$errnostr: $errstr in $errfile on line $errline" . PHP_EOL;
    echo '</div>';

    // Also use the internal error handler so we keep the usual behaviour.
    return false;
}

/**
 * Restrict the config.php settings allowed.
 *
 * When running the behat features the config.php
 * settings should not affect the results.
 *
 * @return void
 */
function behat_clean_init_config() {
    global $CFG;

    $allowed = array_flip(array(
            'wwwroot', 'docroot', 'dataroot', 'admin', 'directorypermissions', 'filepermissions',
            'dbtype', 'dbhost', 'dbname', 'dbuser', 'dbpass', 'dbprefix', 'error_reporting',
            'sessionpath'
    ));

    // Add extra allowed settings.
    if (!empty($CFG->behat_extraallowedsettings)) {
        $allowed = array_merge($allowed, array_flip($CFG->behat_extraallowedsettings));
    }

    // Also allowing behat_ prefixed attributes.
    foreach ($CFG as $key => $value) {
        if (!isset($allowed[$key]) && strpos($key, 'behat_') !== 0) {
            unset($CFG->{$key});
        }
    }

}

/**
 * Checks that the behat config vars are properly set.
 *
 * @return void Stops execution with error code if something goes wrong.
 */
function behat_check_config_vars() {
    global $CFG;

    // Verify prefix value.
    if (empty($CFG->behat_dbprefix)) {
        behat_error(BEHAT_EXITCODE_CONFIG,
                        'Define $CFG->behat_dbprefix in config.php');
    }
    if (!empty($CFG->dbprefix) and $CFG->behat_dbprefix == $CFG->dbprefix) {
        behat_error(BEHAT_EXITCODE_CONFIG,
                        '$CFG->behat_dbprefix in config.php must be different from $CFG->dbprefix');
    }
    if (!empty($CFG->phpunit_dbprefix) and $CFG->behat_dbprefix == $CFG->phpunit_dbprefix) {
        behat_error(BEHAT_EXITCODE_CONFIG,
                        '$CFG->behat_dbprefix in config.php must be different from $CFG->phpunit_dbprefix');
    }

    // Verify behat wwwroot value.
    if (empty($CFG->behat_wwwroot)) {
        behat_error(BEHAT_EXITCODE_CONFIG,
                        'Define $CFG->behat_wwwroot in config.php');
    }
    if (!empty($CFG->wwwroot) and $CFG->behat_wwwroot == $CFG->wwwroot) {
        behat_error(BEHAT_EXITCODE_CONFIG,
                        '$CFG->behat_wwwroot in config.php must be different from $CFG->wwwroot');
    }

    // Verify behat dataroot value.
    if (empty($CFG->behat_dataroot)) {
        behat_error(BEHAT_EXITCODE_CONFIG,
                        'Define $CFG->behat_dataroot in config.php');
    }
    if (!file_exists($CFG->behat_dataroot)) {
        $permissions = isset($CFG->directorypermissions) ? $CFG->directorypermissions : 02777;
        umask(0);
        if (!mkdir($CFG->behat_dataroot, $permissions, true)) {
            behat_error(BEHAT_EXITCODE_PERMISSIONS, '$CFG->behat_dataroot directory can not be created');
        }
    }
    $CFG->behat_dataroot = realpath($CFG->behat_dataroot);
    if (empty($CFG->behat_dataroot) or !is_dir($CFG->behat_dataroot) or !is_writable($CFG->behat_dataroot)) {
        behat_error(BEHAT_EXITCODE_CONFIG,
                        '$CFG->behat_dataroot in config.php must point to an existing writable directory');
    }
    if (!empty($CFG->dataroot) and $CFG->behat_dataroot == realpath($CFG->dataroot)) {
        behat_error(BEHAT_EXITCODE_CONFIG,
                        '$CFG->behat_dataroot in config.php must be different from $CFG->dataroot');
    }
    if (!empty($CFG->phpunit_dataroot) and $CFG->behat_dataroot == realpath($CFG->phpunit_dataroot)) {
        behat_error(BEHAT_EXITCODE_CONFIG,
                        '$CFG->behat_dataroot in config.php must be different from $CFG->phpunit_dataroot');
    }
}

/**
 * Should we switch to the test site data?
 * @return bool
 */
function behat_is_test_site() {
    global $CFG;

    if (defined('BEHAT_UTIL')) {
        // This is the framework tool that installs/drops the test site install.
        return true;
    }
    if (defined('BEHAT_TEST')) {
        // This is the main vendor/bin/behat script.
        return true;
    }
    if (empty($CFG->behat_wwwroot)) {
        return false;
    }
    if (isset($_SERVER['REMOTE_ADDR']) and behat_is_requested_url($CFG->behat_wwwroot)) {
        // Something is accessing the web server like a real browser.
        return true;
    }

    return false;
}

/**
 * Checks if the URL requested by the user matches the provided argument
 *
 * @param string $url
 * @return bool Returns true if it matches.
 */
function behat_is_requested_url($url) {

    $parsedurl = parse_url($url . '/');
    $parsedurl['port'] = isset($parsedurl['port']) ? $parsedurl['port'] : 80;
    $parsedurl['path'] = rtrim($parsedurl['path'], '/');

    // Removing the port.
    $pos = strpos($_SERVER['HTTP_HOST'], ':');
    if ($pos !== false) {
        $requestedhost = substr($_SERVER['HTTP_HOST'], 0, $pos);
    }
    else {
        $requestedhost = $_SERVER['HTTP_HOST'];
    }

    // The path should also match.
    if (empty($parsedurl['path'])) {
        $matchespath = true;
    }
    else if (strpos($_SERVER['SCRIPT_NAME'], $parsedurl['path']) === 0) {
        $matchespath = true;
    }

    // The host and the port should match
    if ($parsedurl['host'] == $requestedhost && $parsedurl['port'] == $_SERVER['SERVER_PORT'] && !empty($matchespath)) {
        return true;
    }

    return false;
}

