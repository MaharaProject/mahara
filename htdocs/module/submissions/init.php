<?php
/**
 *
 * @package    Mahara
 * @subpackage module-submissions
 * @author     Alexander Del Ponte
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information, please see the README file distributed with this software.
 *
 */

require(dirname(__FILE__) . '/vendor/autoload.php');

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'module');
define('SECTION_PLUGINNAME', 'submissions');

require(dirname(dirname(dirname(__FILE__))). '/init.php');
