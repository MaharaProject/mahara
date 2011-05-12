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

/**#@+
 * @access private
 */
// These are bitmaps - the next one should be 4
/** Display the errors on the screen */
define('LOG_TARGET_SCREEN', 1);
/** Write the errors to the error log, as specified in your php configuration */
define('LOG_TARGET_ERRORLOG', 2);
/** Write the error to stdout (using echo) */
define('LOG_TARGET_STDOUT', 4);
/** Display the errors on the screen in the admin area only (short term hack
    until we create an admin notifications page) */
define('LOG_TARGET_ADMIN', 8);
/** Log to a specific file */
define('LOG_TARGET_FILE', 16);

// Logging levels
/** Environment type errors, such as register_globals being on */
define('LOG_LEVEL_ENVIRON', 1);
/** Debug messages */
define('LOG_LEVEL_DBG', 2);
/** Informational messages */
define('LOG_LEVEL_INFO', 4);
/** Warnings */
define('LOG_LEVEL_WARN', 8);

// developermodes,  also bitmaps
/** inlude debug.js */
define('DEVMODE_DEBUGJS', 1);
/** include debug.css */
define('DEVMODE_DEBUGCSS', 2);
/** include unpacked mochikit */
define('DEVMODE_UNPACKEDJS', 8);
// more here.. start at 16 :)

/**#@-*/


// Tell PHP about our error settings
error_reporting(E_ALL);
set_error_handler('error');
set_exception_handler('exception');


// Logging functions

/**
 * Logs a message at the debug level
 *
 * @param string $message   The message to display
 * @param bool   $escape    Whether to HTML escape the message
 * @param bool   $backtrace Whether to provide a backtrace if the system is
 *                          configured to give backtraces at this level.
 */
function log_debug ($message, $escape=true, $backtrace=true) {
    log_message($message, LOG_LEVEL_DBG, $escape, $backtrace);
}

/**
 * Logs a message at the info level
 *
 * @param string $message   The message to display
 * @param bool   $escape    Whether to HTML escape the message
 * @param bool   $backtrace Whether to provide a backtrace if the system is
 *                          configured to give backtraces at this level.
 */
function log_info ($message, $escape=true, $backtrace=true) {
    log_message($message, LOG_LEVEL_INFO, $escape, $backtrace);
}

/**
 * Logs a message at the warning level
 *
 * @param string $message   The message to display
 * @param bool   $escape    Whether to HTML escape the message
 * @param bool   $backtrace Whether to provide a backtrace if the system is
 *                          configured to give backtraces at this level.
 */
function log_warn ($message, $escape=true, $backtrace=true) {
    log_message($message, LOG_LEVEL_WARN, $escape, $backtrace);
}

/**
 * Logs a message at the environment level
 *
 * @param string $message   The message to display
 * @param bool   $escape    Whether to HTML escape the message
 * @param bool   $backtrace Whether to provide a backtrace if the system is
 *                          configured to give backtraces at this level.
 */
function log_environ ($message, $escape=true, $backtrace=true) {
    log_message($message, LOG_LEVEL_ENVIRON, $escape, $backtrace);
}

/**
 * Logs a message at the given log level. This function should not be called by
 * any code outside of this module.
 *
 * @param string $message   The message to display
 * @param int    $loglevel  The level to log the message at
 * @param bool   $escape    Whether to HTML escape the message
 * @param bool   $backtrace Whether to provide a backtrace if the system is
 *                          configured to give backtraces at this level.
 * @param string $file      The file the error occured in
 * @param int    $line      The line number the error occured on
 * @param array  $trace     The backtrace for the error
 * @access private
 */
function log_message ($message, $loglevel, $escape, $backtrace, $file=null, $line=null, $trace=null) {
    global $SESSION, $CFG;
    if (!$SESSION && function_exists('get_config') && $CFG) {
        require_once(get_config('docroot') . 'auth/lib.php');
        $SESSION = Session::singleton();
    }

    static $requestprefix = '';
    if (!$requestprefix) {
        $requestprefix = substr(md5(microtime()), 0, 2) . ' ';
    }

    static $loglevelnames = array(
        LOG_LEVEL_ENVIRON => 'environ',
        LOG_LEVEL_DBG     => 'dbg',
        LOG_LEVEL_INFO    => 'info',
        LOG_LEVEL_WARN    => 'warn'
    );

    if (!function_exists('get_config') || null === ($targets = get_config('log_' . $loglevelnames[$loglevel] . '_targets'))) {
        $targets = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
    }

    // Get nice backtrace information if required
    $trace = ($trace) ? $trace : debug_backtrace();
    // If the last caller was the 'error' function then it came from a PHP warning
    if (!is_null($file)) {
        $filename = $file;
        $linenum  = $line;
    }
    else {
        $filename  = $trace[1]['file'];
        $linenum   = $trace[1]['line'];
    }

    if (!function_exists('get_config') || get_config('log_backtrace_levels') & $loglevel) {
        list($textbacktrace, $htmlbacktrace) = log_build_backtrace($trace);
    }
    else {
        $textbacktrace = $htmlbacktrace = '';
    }

    if (is_bool($message)) {
        $loglines = array(($message ? 'bool(true)' : 'bool(false)'));
    }
    else if (is_null($message)) {
        $loglines = array('NULL');
    }
    else {
        $loglines = explode("\n", print_r($message, true));
    }

    // Make a prefix for each line, if we are logging a normal debug/info/warn message
    $prefix = $requestprefix;
    if ($loglevel != LOG_LEVEL_ENVIRON && function_exists('get_config')) {
        $docroot = get_config('docroot');
        $prefixfilename = (substr($filename, 0, strlen($docroot)) == $docroot)
            ? substr($filename, strlen($docroot))
            : $filename;
        $prefix .= '(' . $prefixfilename . ':' . $linenum . ') ';
    }
    $prefix = '[' . str_pad(substr(strtoupper($loglevelnames[$loglevel]), 0, 3), 3) . '] ' . $prefix;

    if ($targets & LOG_TARGET_SCREEN || (defined('ADMIN') && $targets & LOG_TARGET_ADMIN)) {
        // Work out which method to call for displaying the message
        if ($loglevel == LOG_LEVEL_DBG || $loglevel == LOG_LEVEL_INFO) {
            $method = 'add_info_msg';
        }
        else {
            $method = 'add_error_msg';
        }

        $message = implode("\n", $loglines);
        if ($escape) {
            $message = htmlspecialchars($message, ENT_COMPAT, 'UTF-8');
            $message = str_replace('  ', '&nbsp; ', $message);
        }
        $message = nl2br($message);
        $message = '<div class="backtrace">' . $prefix . $message . "</div>\n";
        if (is_a($SESSION, 'Session')) {
            $SESSION->$method($message, false);
        }
        else if (!function_exists('get_config') || get_config('installed')) {
            // Don't output when we are not installed, since this will cause the
            // redirect to the install page to fail.
            echo $message;
        }

        if ($backtrace && $htmlbacktrace) {
            if (is_a($SESSION, 'Session')) {
                $SESSION->add_info_msg($htmlbacktrace, false);
            }
            else if (!function_exists('get_config') || get_config('installed')) {
                echo $htmlbacktrace;
            }
        }
    }

    if ($targets & LOG_TARGET_ERRORLOG) {
        foreach ($loglines as $line) {
            error_log($prefix . $line);
        }
        if ($backtrace && $textbacktrace) {
            $lines = explode("\n", $textbacktrace);
            foreach ($lines as $line) {
                error_log($line);
            }
        }
    }

    if ($targets & LOG_TARGET_STDOUT) {
        foreach ($loglines as $line) {
            echo($prefix . $line . "\n");
        }
        if ($backtrace && $textbacktrace) {
            echo $textbacktrace;
        }
    }

    if (function_exists('get_config')) {
        if (!$logfilename = get_config('log_file')) {
            $logfilename = get_config('dataroot') . 'error.log';
        }
        if (($targets & LOG_TARGET_FILE) && $logfilename) {
            global $LOGFILE_FH;
            static $logfile_open_attempted = null;
            if (!$logfile_open_attempted) {
                $logfile_open_attempted = true;
                $LOGFILE_FH = fopen($logfilename, 'wb');
                if ($LOGFILE_FH !== false) {
                    function _close_logfile() {
                        global $LOGFILE_FH;
                        fclose($LOGFILE_FH);
                    }
                    register_shutdown_function('_close_logfile');
                }
                else {
                    error_log("Could not open your custom log file ($logfilename)");
                }
            }

            if (is_resource($LOGFILE_FH)) {
                foreach ($loglines as $line) {
                    fwrite($LOGFILE_FH, $prefix . $line . "\n");
                }
                if ($backtrace && $textbacktrace) {
                    $lines = explode("\n", $textbacktrace);
                    foreach ($lines as $line) {
                        fwrite($LOGFILE_FH, $line . "\n");
                    }
                }
            }
        }
    }
}

/**
 * Given an array that contains a backtrace, builds two versions of it - one in
 * HTML form and one in text form - for logging.
 *
 * @param array $backtrace The backtrace to build
 * @return array           An array containing the backtraces, index 0
 *                         containing the text version and index 1 containing
 *                         the HTML version.
 * @access private
 */
function log_build_backtrace($backtrace) {
    $calls = array();

    // Remove the call to log_message
    //array_shift($backtrace);

    foreach ($backtrace as $bt) {
        $bt['file']  = (isset($bt['file'])) ? $bt['file'] : 'Unknown';
        $bt['line']  = (isset($bt['line'])) ? $bt['line'] : 0;
        $bt['class'] = (isset($bt['class'])) ? $bt['class'] : '';
        $bt['type']  = (isset($bt['type'])) ? $bt['type'] : '';
        $bt['args']  = (isset($bt['args'])) ? $bt['args'] : '';

        $args = '';
        if ($bt['args']) {
            foreach ($bt['args'] as $arg) {
                if (!empty($args)) {
                    $args .= ', ';
                }
                switch (gettype($arg)) {
                    case 'integer':
                    case 'double':
                        $args .= $arg;
                        break;
                    case 'string':
                        $arg = substr($arg, 0, 50) . ((strlen($arg) > 50) ? '...' : '');
                        $args .= '"' . $arg . '"';
                        break;
                    case 'array':
                        $args .= 'array(size ' . count($arg) . ')';
                        break;
                    case 'object':
                        $args .= 'object(' . get_class($arg) . ')';
                        break;
                    case 'resource':
                        $args .= 'resource(' . strstr((string)$arg, '#') . ')';
                        break;
                    case 'boolean':
                        $args .= $arg ? 'true' : 'false';
                        break;
                    case 'NULL':
                        $args .= 'null';
                        break;
                    default:
                        $args .= 'unknown';
                }
            }
        }

        $calls[] = array(
            'file'  => $bt['file'],
            'line'  => $bt['line'],
            'class' => $bt['class'],
            'type'  => $bt['type'],
            'func'  => $bt['function'],
            'args'  => $args
        );
    }

    $textmessage = "Call stack (most recent first):\n";
    $htmlmessage = "<div class=\"backtrace\"><strong>Call stack (most recent first):</strong>\n<ul>";

    foreach ($calls as $call) {
        $textmessage .= "  * {$call['class']}{$call['type']}{$call['func']}({$call['args']}) at {$call['file']}:{$call['line']}\n";
        $htmlmessage .= '<li><span style="color:#933;">' . htmlspecialchars($call['class'], ENT_COMPAT, 'UTF-8') . '</span><span style="color:#060;">'
            . htmlspecialchars($call['type'], ENT_COMPAT, 'UTF-8') . '</span><span style="color:#339;">' . htmlspecialchars($call['func'], ENT_COMPAT, 'UTF-8')
            . '</span><span style="color:#060;">(</span><span style="color:#f00;">' . htmlspecialchars($call['args'], ENT_COMPAT, 'UTF-8') . '</span><span style="color:#060;">)</span> at <strong>' . htmlspecialchars($call['file'], ENT_COMPAT, 'UTF-8') . ':' . $call['line'] . '</strong></li>';
    }
    $htmlmessage .= "</div>\n";

    return array($textmessage, $htmlmessage);
}

/**
 * Ends the script with an informational message
 *
 * @param string $message The message to display
 * @todo this function should go away
 */
function die_info($message) {
    $smarty = smarty(array(), array(), array(), array('sidebars' => false));
    $smarty->assign('message', $message);
    $smarty->assign('type', 'info');
    $smarty->display('message.tpl');
    exit;
}


// Error/Exception handling

/**
 * Called when any error occurs, due to a PHP error or through a call to
 * {@link trigger_error}.
 *
 * @param int    $code    The code of the error message
 * @param string $message The message reported
 * @param string $file    The file the error was detected in
 * @param string $line    The line number the error was detected on
 * @param array  $vars    The contents of $GLOBALS at the time the error was detected
 * @access private
 */
function error ($code, $message, $file, $line, $vars) {
    static $error_lookup = array(
        E_NOTICE => 'Notice',
        E_WARNING => 'Warning',
        // Not sure if these ever get handled here
        E_ERROR => 'Error',
        // These three are not used by this application but may be used by third parties
        E_USER_NOTICE => 'User Notice',
        E_USER_WARNING => 'User Warning',
        E_USER_ERROR => 'User Error'
    );

    if (defined('E_RECOVERABLE_ERROR')) {
        $error_lookup[E_RECOVERABLE_ERROR] = 'Warning';
    }

    if (!error_reporting()) {
        return;
    }

    if (!isset($error_lookup[$code])) {
        return;
    }

    // Ignore errors from smarty templates, which happen all too often
    if (function_exists('get_config')) {
        $compiledir = realpath(get_config('dataroot') . 'dwoo/compile');

        if (E_NOTICE == $code && substr($file, 0, strlen($compiledir)) == $compiledir) {
            return;
        }

        if (E_NOTICE == $code && preg_match('#^' . quotemeta(get_config('docroot') . 'theme/') . '[a-z0-9-]+/pieforms#', $file)) {
            return;
        }
    }

    // Fix up the message, which is in HTML form
    $message = strip_tags($message);
    $message = htmlspecialchars_decode($message);

    log_message($message, LOG_LEVEL_WARN, true, true, $file, $line);
}

/**
 * Catches all otherwise uncaught exceptions. Will be deliberately used in some
 * situations. After this is called the script will end, so make sure to catch
 * any exceptions that you can deal with.
 *
 * @param Exception $e The exception that was thrown.
 * @access private
 */
function exception (Exception $e) {
    global $USER;
    if ($USER) {
        if (!($e instanceof MaharaException) || get_class($e) == 'MaharaException') {
            log_warn("An exception was thrown of class " . get_class($e) . ". \nTHIS IS BAD "
                     . "and should be changed to something extending MaharaException,\n" 
                     . "unless the exception is from a third party library.\n"
                     . "Original trace follows", true, false);
            log_message($e->getMessage(), LOG_LEVEL_WARN, true, true, $e->getFile(), $e->getLine(), $e->getTrace());
            $e = new SystemException($e->getMessage());
            $e->set_log_off();
        }
    }

    // Display the message and die
    $e->handle_exception();
}


interface MaharaThrowable {
    
    public function render_exception();
    
}

// Standard exceptions  - top level exception class. 
// all exceptions should extend one of these three.

/**
 * Very top of the tree for exceptions in Mahara.
 * Nothing should extend this directly. 
 * Contains a few helper functions for all exceptions.
 */
class MaharaException extends Exception {

    protected $log = true;

    public function __construct($message='', $code=0) {
        parent::__construct($message, $code);
        if (!defined('MAHARA_CRASHING')) {
            define('MAHARA_CRASHING', true);
        }
    }

    public function get_string() {
        $args = func_get_args();
        if (function_exists('get_string')) {
            $args[0] = strtolower(get_class($this)) . $args[0];
            $args[1] = 'error';
            $str = call_user_func_array('get_string', $args);
            if (strpos($str, '[[') !== 0) {
                return $str;
            }
        }

        $tag = func_get_arg(0);
        $strings = $this->strings();
        if (array_key_exists($tag, $strings)) {
            return $strings[$tag];
        }
        
        return 'An error occured';
    }

    public function get_sitename() {
        if (!function_exists('get_config') || !$sitename = @get_config('sitename')) {
            $sitename = 'Mahara';
        }
        return $sitename;
    }

    public function strings() {
        return array('title' => $this->get_sitename() . ': Site unavailable');
    }

    public function set_log() {
        $this->log = true;
    }

    public function set_log_off() {
        $this->log = false;
    }

    public function render_exception() {
        return $this->getMessage();
    }

    public final function handle_exception() {

        if (!empty($this->log)) {
            log_message($this->getMessage(), LOG_LEVEL_WARN, true, true, $this->getFile(), $this->getLine(), $this->getTrace());
        }

        if (defined('JSON')) { // behave differently
            @header('Content-type: text/plain');
            @header('Pragma: no-cache');
            echo json_encode(array('error' => true, 'message' => $this->render_exception()));
            exit;
        }
        
        if (defined('CRON')) {
            echo $this->render_exception();
            exit;
        }

        if (defined('XMLRPC')) { // it's preferable to throw an XmlrpcServerException
            echo xmlrpc_error($this->render_exception(), $this->getCode());
            exit;
        }

        if (headers_sent()) {
            echo '<span style="color: red;">ERROR - something bad happened after headers have been sent. Check the error log for more information.</span>';
            die();
        }

        $outputtitle = $this->get_string('title');
        $outputmessage = trim($this->render_exception());

        if (function_exists('smarty') && !$this instanceof ConfigSanityException) {
            try {
                $smarty = smarty(array(), array(), array(), array('sidebars' => false));
                $smarty->assign('title', $outputtitle);
                $smarty->assign('message', $outputmessage);
                $smarty->display('error.tpl');
                die();
            }
            catch (Exception $e) {
                // If an exception is thrown in smarty(), ignore it
                // and print the message out the ugly way
                log_warn("Exception thrown by smarty call while handling exception");
            }
        }

        $outputtitle   = htmlspecialchars($outputtitle, ENT_COMPAT, 'UTF-8');
        $outputmessage = nl2br(htmlspecialchars($outputmessage, ENT_COMPAT, 'UTF-8'));
        echo <<<EOF
<html>
<head>
    <title>$outputtitle</title>
    <style type="text/css">
        html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        body {
            width: 600px;
            margin: 100px auto;
            font-size: 12px;
        }
        h1 {
            color: #547c22;
            font-size: 20px;
            font-weight: normal;	
            margin: 0 0 5px 0;
            padding: 0;
            text-transform: capitalize;
            border-bottom: 1px solid #819f18;
            text-align: center;
        }
        #message {
            width: 90%;
            margin: 0 auto;
            text-align: justify;
        }
        #reason {
            margin: 0 3em;
        }
    </style>
</head>
<body>
EOF;
        echo <<<EOF
<h1>$outputtitle</h1>
<div id="message">$outputmessage</div>
</body>
</html>
EOF;
        // end of printing stuff to the screen...
        die();
    }
}






/**
 * SystemException - this is basically a bug in the system.
 */
class SystemException extends MaharaException implements MaharaThrowable {

    public function __construct($message, $code=0) {
        parent::__construct($message, $code);
        $this->set_log();
    }

    public function render_exception () {
        return $this->get_string('message');
    }

    public function strings() {
        return array_merge(parent::strings(), 
                           array('message' => 'A nonrecoverable error occured. '
                                 . 'This probably means you have encountered a bug in the system'));
    }
    
}

/**
 * ConfigException - something is misconfigured that's causing a problem.
 * Generally these will be the fault of admins
 */
class ConfigException extends MaharaException  implements MaharaThrowable {
     
    public function render_exception () {
        return $this->get_string('message') . "\n\n" . $this->getMessage();
    }

    public function strings() {
        return array_merge(parent::strings(), 
                           array('message' => 'The environment where ' . $this->get_sitename() 
                           . ' is running is misconfigured and this is causing problems. '
                           . 'You probably need to contact a server administrator to get this fixed. '
                           . 'Details, if any, follow:'));
    }
}

/**
 * UserException - the user has done something they shouldn't (or tried to)
 */
class UserException extends MaharaException implements MaharaThrowable {

    protected $log = false;

    public function render_exception() {
        return $this->get_string('message') . "\n\n" . $this->getMessage();
    }

    public function strings() {
        return array_merge(parent::strings(),  
                           array('message' => 'Something in the way you\'re interacting with ' 
                                 . $this->get_sitename()
                                 . " is causing an error.\nDetails if any, follow:"));
    }
}

/**
 * Exception - Not found. Throw this if a user is trying to view something
 * that doesn't exist
 */
class NotFoundException extends UserException {
    public function strings() {
        return array_merge(parent::strings(), 
                           array('message' => get_string('notfoundexception', 'error'),
                                 'title'   => get_string('notfound', 'error')));
    }

    public function render_exception() {
        header('HTTP/1.0 404 Not Found', true);
        return parent::render_exception();
    }
}


/** 
 * The configuration that Mahara is trying to be run in is insane
 */
class ConfigSanityException extends ConfigException {
    public function strings() {
        return array_merge(parent::strings(), array('message' => ''));
    }
}

/**
 * An SQL related error occured
 */
class SQLException extends SystemException {
    public function __construct($message=null, $code=0) {
        global $DB_IGNORE_SQL_EXCEPTIONS;

        if ($GLOBALS['_TRANSACTION_LEVEL'] > 0) {
            db_rollback();
        }
        parent::__construct($message, $code);

        if (empty($DB_IGNORE_SQL_EXCEPTIONS)) {
            log_warn($this->getMessage());
        }
    }
}

/**
 * An exception generated by invalid GET or POST parameters
 */
class ParameterException extends UserException {
    public function strings() {
        return array_merge(parent::strings(), array(
            'title'   => 'Mahara: Invalid Parameter',
            'message' => 'A required parameter is missing or malformed')
        );
    }
}

/**
 * A function or method has been passed the wrong kind of argument
 * Unfortunately, broken type-hints cause fatal errors - not exceptions
 */
class ParamOutOfRangeException extends SystemException {}

/**
 * Remote Server exception - something has gone wrong at the remote machine
 */
class RemoteServerException extends SystemException {}

/**
 * Xmlrpc Server exception - must output well formed XMLRPC error to the client
 */
class XmlrpcServerException extends SystemException {}

/**
 * Xmlrpc Client exception - Something has gone wrong in the networking
 */
class XmlrpcClientException extends SystemException {
    public function strings() {
        return array_merge(parent::strings(), array(
            'title'   => get_string('xmlrpccouldnotlogyouin', 'auth'),
            'message' => get_string('xmlrpccouldnotlogyouindetail', 'auth'))
        );
    }
}

/**
 * Error with SSL and encryption
 */
class CryptException extends SystemException {}

/**
 * An exception generated when e-mail can't be sent
 */
class EmailException extends SystemException {}

/**
 * Exception - Invalid address
 */
class InvalidEmailException extends EmailException {}

/**
 * Exception - Email is disabled for this user
 */
class EmailDisabledException extends EmailException {}

/** 
 * Exception - artefact not found 
 */
class ArtefactNotFoundException extends NotFoundException {}

/**
 * Exception - block instance not found
 */
class BlockInstanceNotFoundException extends NotFoundException {}

/** 
 * Exception - interaction instance not found
 */
class InteractionInstanceNotFoundException extends NotFoundException {}

/**
 * Exception - view not found
 */
class ViewNotFoundException extends NotFoundException {}

/**
 * Exception - view limit exceeded
 * for example, multiple profile views
 */
class ViewLimitExceededException extends UserException {}

/**
 * Exception - user not found
 */
class UserNotFoundException extends NotFoundException {}

/**
 * Exception - user not found while doing XMLRPC authentication
 */
class XmlrpcUserNotFoundException extends UserNotFoundException {
    public function strings() {
        return array_merge(parent::strings(),
            array('message' => ''),
            array('title'   => get_string('unabletosigninviasso', 'auth')));
    }
}

/**
 * Exception - group not found
 */
class GroupNotFoundException extends NotFoundException {}

/**
 * Exception - fired when something happens that would make the user exceed their quota
 */
class QuotaExceededException extends UserException {}

/**
 * Exception - fired when uploading a file fails
 */
class UploadException extends UserException {}

/**
 * Exception - collection not found
 */
class CollectionNotFoundException extends NotFoundException {}

/**
 * Exception - Access denied. Throw this if a user is trying to view something they can't
 */
class AccessDeniedException extends UserException {
    public function strings() {
        return array_merge(parent::strings(), 
                           array('message' => get_string('accessdeniedexception', 'error'),
                                 'title'   => get_string('accessdenied', 'error')));
    }

    public function render_exception() {
        global $USER;
        if (defined('PUBLIC') && !$USER->is_logged_in()) {
            $loginurl = substr($_SERVER['REQUEST_URI'], strlen(get_mahara_install_subdirectory()) - 1);
            $loginurl .= (false === strpos($loginurl, '?')) ? '?' : '&';
            $loginurl .= 'login';
            redirect($loginurl);
        }
        header("HTTP/1.0 403 Forbidden", true);
        return parent::render_exception();
    }
}

/**
 * Exception - Access denied to a group page because the user isn't a member
 * Give the user a chance to join then continue
 */
class GroupAccessDeniedException extends AccessDeniedException {
    public function render_exception() {
        global $USER, $SESSION;
        if (defined('GROUP') && $USER->is_logged_in()) {
            $roles = $USER->get('grouproles');
            if (!isset($roles[GROUP])) {
                $group = group_current_group();
                if ($group->jointype == 'open'
                    || $group->jointype == 'invite' && get_record('group_member_invite', 'group', GROUP, 'member', $USER->get('id'))) {
                    $SESSION->add_error_msg(get_string('notmembermayjoin', 'group', $group->name));
                    $next = substr($_SERVER['REQUEST_URI'], strlen(get_mahara_install_subdirectory()) - 1);
                    redirect(get_config('wwwroot') . 'group/view.php?id=' . GROUP . '&next=' . urlencode($next));
                }
                if ($group->jointype == 'request' && !get_record('group_member_request', 'group', GROUP, 'member', $USER->get('id'))) {
                    $SESSION->add_error_msg(get_string('notamember', 'group'));
                    redirect(get_config('wwwroot') . 'group/requestjoin.php?id=' . GROUP . '&returnto=view');
                }
            }
        }
        header("HTTP/1.0 403 Forbidden", true);
        return parent::render_exception();
    }
}

/**
 * Exception - Access totally denied, the user won't be able to access it even if they log in 
 * as the administrator
 */
class AccessTotallyDeniedException extends UserException {
    public function strings() {
        return array_merge(parent::strings(), 
                           array('message' => get_string('accessdeniedexception', 'error'),
                                 'title'   => get_string('accessdenied', 'error')));
    }

    public function render_exception() {
        header("HTTP/1.0 403 Forbidden", true);
        return parent::render_exception();
    }
}

/**
* something has happened during import.
* either: the user is there, in which case they get the bug screen,
*         it's a spawned request during an xmlrpc server ping (content_ready) in which case XMLRPC will be defined
*         or it's a queued fetch, in which case CRON will be defined.
* @todo maybe refactor at the point that we have something other than importing over mnet (eg userland)
*/
class ImportException extends SystemException {

    public function __construct($importer, $message=null, $code=0) {
        parent::__construct($message, $code);
        if ($importer instanceof PluginImport) {
            $importer->cleanup();
        }
    }

    public function render_exception() {
        return $this->getMessage();
    }
}

/**
* something has happened during export.
*/
class ExportException extends SystemException {

    public function __construct($exporter, $message=null, $code=0) {
        parent::__construct($message, $code);
    }

    public function render_exception() {
        return $this->getMessage();
    }
}
