<?php
/**
 *  This file is part of maraha.
 *
 *   maraha is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.

 *   maraha is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.

 *   You should have received a copy of the GNU General Public License
 *   along with maraha; if not, write to the Free Software
 *   Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
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
// wwwroot - include trailing slash
// @todo <nigel>: Generate programatically
$cfg->wwwroot = 'http://myhost.com/mahara/';

// dirroot - uploaded files - include trailing slash.
// must be writable by the webserver and outside document root.
$cfg->dataroot = '/path/to/uploaddir';

// Logging configuration
$cfg->log_dbg_targets = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
$cfg->log_info_targets = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
$cfg->log_warn_targets = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
$cfg->log_environ_targets = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;

$cfg->log_backtrace_levels = LOG_LEVEL_DBG | LOG_LEVEL_INFO;

?>
