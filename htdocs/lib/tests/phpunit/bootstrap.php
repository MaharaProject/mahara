<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2009 Penny Leach
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
 * @subpackage tests
 * @author     Andrew Nicols
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */
define('TESTSRUNNING', 1);
define('INTERNAL', 1);
define('PUBLIC', 1);

// necessary since we're running in a limited scope
global $CFG, $db, $SESSION, $USER, $THEME;

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('libroot') . 'ddl.php');
require_once(get_config('libroot') . 'upgrade.php');
require_once(get_config('libroot') . 'phpunit.php');

$bootstrap = new UnitTestBootstrap();

$bootstrap->jimmy_config();
$bootstrap->clean_stale_tables();
$bootstrap->install_mahara();
