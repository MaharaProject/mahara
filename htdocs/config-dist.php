<?php
/**
 * This program is part of Maraha.
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

$cfg = new StdClass;


// database connection details
$cfg->dbtype   = 'postgres8';
$cfg->dbhost   = 'localhost';
$cfg->dbport   = 5432;
$cfg->dbname   = 'mahara';
$cfg->dbuser   = 'mahara';
$cfg->dbpass   = 'mahara';
$cfg->dbprefix = '';

// wwwroot - the web-visible path to your Mahara installation
// Normally, this is automatically detected - if it doesn't work for you
// then try specifying it here
//$cfg->wwwroot = 'http://myhost.com/mahara/';

// dirroot - uploaded files are stored here
// must be writable by the webserver and outside document root.
// Mahara will NOT RUN if this is inside your document root, because
// this is a big security hole.
$cfg->dataroot = '/path/to/uploaddir';

// Logging configuration
// For each log level, you can specify where the messages are displayed.
// LOG_TARGET_SCREEN makes the error messages go to the screen - useful
// when debugging but not on a live site!
// LOG_TARGET_ERRORLOG makes the error messages go to the log as specified
// by the apache ErrorLog directive. It's probably useful to have this on
// for all log levels.
// You can combine them with bitwise operations,
// e.g. LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG
$cfg->log_dbg_targets      = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
$cfg->log_info_targets     = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
$cfg->log_warn_targets     = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
$cfg->log_environ_targets  = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;

// The log levels that will generate backtraces. Useful for development,
// but probably only warnings are useful on a live site.
$cfg->log_backtrace_levels = LOG_LEVEL_WARN | LOG_LEVEL_ENVIRON;

?>
