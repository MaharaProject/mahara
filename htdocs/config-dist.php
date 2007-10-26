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

$cfg = new StdClass;


// database connection details
// valid values for dbtype are 'postgres8' and 'mysql5'
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

// dataroot - uploaded files are stored here
// must be writable by the webserver and outside document root.
// Mahara will NOT RUN if this is inside your document root, because
// this is a big security hole.
$cfg->dataroot = '/path/to/uploaddir';

// system mail address. emails out come from this address.
// if not specified, will default to noreply@ automatically detected host.
// if that doesn't work or you want something else, then specify it here.
// $cfg->noreplyaddress = 'noreply@myhost.com'

// Logging configuration
// For each log level, you can specify where the messages are displayed.
// LOG_TARGET_SCREEN makes the error messages go to the screen - useful
// when debugging but not on a live site!
// LOG_TARGET_ERRORLOG makes the error messages go to the log as specified
// by the apache ErrorLog directive. It's probably useful to have this on
// for all log levels.
// You can combine them with bitwise operations,
// e.g. LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG
//
// This configuration is suitable for people running Mahara for the first
// time. You will immediately see environment errors, and so can correct
// them. You will be able to see other debugging information in your error
// logs. Once your site is up and running you might want to remove the
// environment level logging completely, and just log everything else to
// the error log.
$cfg->log_dbg_targets     = LOG_TARGET_ERRORLOG;
$cfg->log_info_targets    = LOG_TARGET_ERRORLOG;
$cfg->log_warn_targets    = LOG_TARGET_ERRORLOG;
$cfg->log_environ_targets = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
// This configuration is suitable for developers. You will see all errors
// and they will also be in the logs.
//$cfg->log_dbg_targets     = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
//$cfg->log_info_targets    = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
//$cfg->log_warn_targets    = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
//$cfg->log_environ_targets = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;

// The log levels that will generate backtraces. Useful for development,
// but probably only warnings are useful on a live site.
$cfg->log_backtrace_levels = LOG_LEVEL_WARN | LOG_LEVEL_ENVIRON;

// Developer mode
// When set, the following things (among others) will happen:
//
// * 'debug.js' will be included on each page. You can edit this file to add 
//   debugging javascript at your discretion
// * 'debug.css' will be included on each page. You can edit this file to add 
//   debugging CSS at your discretion
// * firebuglite will be included, if you are not using Firefox
// * the unpacked version of MochiKit will be used
//
// These options are a performance hit otherwise, enable when you are 
// developing for Mahara
$cfg->developermode = false;

// capture performance information and print it
// $cfg->perftofoot = true; // needs a call to mahara_performance_info (smarty callback) - see default theme's footer.tpl
// $cfg->perftolog = true;
// if neither are set, performance info wont be captured.

// mail handling
// if you want mahara to use smtp servers to send mail, enter one or more here
// blank means mahara will use the default PHP method.
// $cfg->smtphosts = 'mail.a.com;mail.b.com';
// If you have specified an smtp server above, and the server requires authentication, 
// enter them here
// $cfg->smtpuser = '';
// $cfg->smtppass = '';

// xmlrpc
// if you're running in a configuration where the host contacting you will be
// using an IP address that is not the same as the IP address that is registered
// for its host name, then you should change the value below to 'true'.
$cfg->xmlrpc_allow_masquerading = false;

// maximum allowed size of uploaded images
// NOTE: the scalable resize might result in images with one dimesion larger than one of these sizes, but not both
$cfg->imagemaxwidth = 1024;
$cfg->imagemaxheight = 1024;

?>
