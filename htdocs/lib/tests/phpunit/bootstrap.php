<?php
/**
 *
 * @package    mahara
 * @subpackage tests
 * @author     Andrew Nicols
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2009 Penny Leach
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

error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
restore_error_handler();
restore_exception_handler();

$bootstrap = new UnitTestBootstrap();

$bootstrap->jimmy_config();
$bootstrap->clean_stale_tables();
$bootstrap->install_mahara();
