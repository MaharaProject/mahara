<?php
/**
 * This program is part of mahara
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
 * @subpackage core or plugintype/pluginname
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

// These are bitmaps - the next one should be 4
define('LOG_TARGET_SCREEN', 1);
define('LOG_TARGET_ERRORLOG', 2);

// Logging levels
define('LOG_LEVEL_ENVIRON', 1);
define('LOG_LEVEL_DBG', 2);
define('LOG_LEVEL_INFO', 4);
define('LOG_LEVEL_WARN', 8);

// Turn error reporting all the way up
error_reporting(E_ALL);


// Logging functions

function log_dbg ($message) {
    log_message($message, LOG_LEVEL_DBG);
}

function log_info ($message) {
    log_message($message, LOG_LEVEL_INFO);
}

function log_warn ($message) {
    log_message($message, LOG_LEVEL_WARN);
}

function log_environ ($message) {
    log_message($message, LOG_LEVEL_ENVIRON);
}

function log_message ($message, $loglevel, $file = null, $line = null) {
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
    $backtrace = debug_backtrace();
    // If the last caller was the 'error' function then it came from a PHP warning
    //print_r($backtrace);
    if (!is_null($file)) {
        $filename = $file;
        $linenum  = $line;
    }
    else {
        $filename  = $backtrace[1]['file'];
        $linenum   = $backtrace[1]['line'];
    }

    if (!function_exists('get_config') || get_config('log_backtrace_levels') & $loglevel) {
        list($textbacktrace, $htmlbacktrace) = log_build_backtrace(debug_backtrace());
    }
    else {
        $textbacktrace = $htmlbacktrace = '';
    }

    $loglines = explode("\n", print_r($message, true));

    // Make a prefix for each line, if we are logging a normal debug/info/warn message
    if ($loglevel != LOG_LEVEL_ENVIRON && function_exists('get_config')) {
        $prefix = '(' . substr($filename, strlen(get_config('docroot'))) . ':' . $linenum . ') ';
    }
    else {
        $prefix = '';
    }

    if ($targets & LOG_TARGET_SCREEN) {
        foreach ($loglines as $line) {
            $line = $prefix . htmlspecialchars($line, ENT_COMPAT, 'UTF-8');
            $line = str_replace('  ', '&nbsp; ', $line);
            echo '<div style="font-family: monospace;">' . $line . "</div>\n";
        }
        if ($htmlbacktrace) {
            echo $htmlbacktrace;
        }
    }

    if ($targets & LOG_TARGET_ERRORLOG) {
        foreach ($loglines as $line) {
            error_log($prefix . $line);
        }
        if ($textbacktrace) {
            $lines = explode("\n", $textbacktrace);
            foreach ($lines as $line) {
                error_log($line);
            }
        }
    }
}


function log_build_backtrace($backtrace) {
    $calls = array();

    // Remove the call to log_message
    array_shift($backtrace);

    foreach ($backtrace as $bt) {
        $bt['file']  = (isset($bt['file'])) ? $bt['file'] : 'Unknown';
        $bt['line']  = (isset($bt['line'])) ? $bt['line'] : 0;
        $bt['class'] = (isset($bt['class'])) ? $bt['class'] : '';
        $bt['type']  = (isset($bt['type'])) ? $bt['type'] : '';
        $bt['args']  = (isset($bt['args'])) ? $bt['args'] : '';

        $args = '';
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
                    $args .= 'resource(' . strstr($arg, '#') . ')';
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
    $htmlmessage = "<pre><strong>Call stack (most recent first):</strong>\n<ul>";

    foreach ($calls as $call) {
        $textmessage .= "  * {$call['class']}{$call['type']}{$call['func']}({$call['args']}) at {$call['file']}:{$call['line']}\n";
        $htmlmessage .= '<li><span style="color:#933;">' . htmlspecialchars($call['class'], ENT_COMPAT, 'UTF-8') . '</span><span style="color:#060;">'
            . htmlspecialchars($call['type'], ENT_COMPAT, 'UTF-8') . '</span><span style="color:#933;">' . htmlspecialchars($call['func'], ENT_COMPAT, 'UTF-8')
            . '</span><span style="color:#060;">(</span><span style="color:#f00;">' . htmlspecialchars($call['args'], ENT_COMPAT, 'UTF-8') . '</span><span style="color:#060;">)</span> at <strong>' . htmlspecialchars($call['file'], ENT_COMPAT, 'UTF-8') . ':' . $call['line'] . '</strong></li>';
    }
    $htmlmessage .= "</pre>\n";

    return array($textmessage, $htmlmessage);
}
// Error/Exception handling

set_error_handler('error');
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

    if (!error_reporting()) {
        return;
    }

    if (!isset($error_lookup[$code])) {
        return;
    }

    log_message($message, LOG_LEVEL_WARN, $file, $line);
}


// Standard exceptions
class ConfigSanityException extends Exception {}

// Catch exceptions that fall through to main()
set_exception_handler('exception');

function exception ($e) {
    // @todo<nigel>: maybe later, rewrite as:
    // if $e not Exception
    //     get language string based on class name
    // rather than by switch on class name
    switch (get_class($e)) {
        case 'ConfigSanityException':
            $message = get_string('configsanityexception', 'error', $e->getMessage());
            break;
        default:
            $message = $e->getMessage();
    }

    echo <<<EOF
<html>
<head>
    <title>\$projectname - Site Unavailable</title>
    <style type="text/css">
        #reason {
            margin: 0 3em;
        }
    </style>
</head>
<h1>OMGWTF</h1>
<body>
<p>$message</p>
<hr>
<p>@todo&lt;nigel&gt;: make this page more shiny :)</p>
</body>
</html>
EOF;
    die();
}

?>
