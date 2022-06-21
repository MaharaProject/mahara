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

use Submissions\Controller;

define('JSON', 1);

require('init.php');

$indexController = new Controller('index.json');
$indexController->handleRequest();
