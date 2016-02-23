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
define('BEHAT_MAHARA_EXITCODE_NOTINSTALLED', 241);  // Mahara site for testing is not installed
define('BEHAT_MAHARA_EXITCODE_NOTENABLED', 242);
define('BEHAT_MAHARA_EXITCODE_OUTOFDATEDB', 243);
define('BEHAT_MAHARA_EXITCODE_NOTWRITABLEDATAROOT', 244);
define('BEHAT_MAHARA_EXITCODE_BADPERMISSIONS', 245);
define('BEHAT_MAHARA_EXITCODE_BADCONFIG_DUPLICATEDBPREFIX', 246);
define('BEHAT_MAHARA_EXITCODE_BADCONFIG_DUPLICATEDATAROOT', 247);
define('BEHAT_MAHARA_EXITCODE_BADCONFIG_DUPLICATEWWWROOT', 248);
define('BEHAT_MAHARA_EXITCODE_BADCONFIG_MISSING', 249);
define('BEHAT_EXITCODE_CANNOTRUN', 253);
define('BEHAT_EXITCODE_NOTINSTALLED', 254);     // Behat and is dependencies are not installed
                                                // in external directory
define('BEHAT_EXITCODE_NOTUPDATED', 255);

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
        case BEHAT_EXITCODE_NOTINSTALLED:
        case BEHAT_EXITCODE_NOTUPDATED:
            $text = 'Behat error: ' . $text;
            $path = testing_cli_argument_path('/testing/frameworks/behat/cli/init.php');
            $text .= "\n Please install behat and a mahara test site, use:\n php " . $path;
            break;
        case BEHAT_EXITCODE_CANNOTRUN:
            $text = 'Behat error: ' . $text;
            $text .= "\n Please check behat conflicts with other test site, e.g. moodle sites";
            break;
        case BEHAT_MAHARA_EXITCODE_BADCONFIG_MISSING:
        case BEHAT_MAHARA_EXITCODE_BADCONFIG_DUPLICATEWWWROOT:
        case BEHAT_MAHARA_EXITCODE_BADCONFIG_DUPLICATEDATAROOT:
        case BEHAT_MAHARA_EXITCODE_BADCONFIG_DUPLICATEDBPREFIX:
            $text = 'Mahara behat error: ' . $text;
            $text .= "\n Please correct behat settings in mahara config.php file";
            break;
        case BEHAT_MAHARA_EXITCODE_BADPERMISSIONS:
        case BEHAT_MAHARA_EXITCODE_NOTWRITABLEDATAROOT:
            $text = 'Mahara behat error: ' . $text;
            $text .= "\n Please check the permissions";
            break;
        case BEHAT_MAHARA_EXITCODE_OUTOFDATEDB:
            $text = 'Mahara behat error: ' . $text;
            $path = testing_cli_argument_path('/testing/frameworks/behat/cli/util.php');
            $text .= "\n Please drop mahara database for testing, use:\n php " . $path . " --drop";
            $text .= "\n and initialise the test site again, use:\n php " . $path . " --init";
             break;
        case BEHAT_MAHARA_EXITCODE_NOTINSTALLED:
            $text = 'Mahara behat error: ' . $text;
            $path = testing_cli_argument_path('/testing/frameworks/behat/cli/util.php');
            $text .= "\n Please install the test site, use:\n php " . $path . " --install";
            break;
         case BEHAT_MAHARA_EXITCODE_NOTENABLED:
            $text = 'Mahara behat error: ' . $text;
            $path = testing_cli_argument_path('/testing/frameworks/behat/cli/util.php');
            $text .= "\n Please enable the test site, use:\n php " . $path . " --enable";
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
            'wwwroot', 'dataroot', 'directorypermissions', 'filepermissions',
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
