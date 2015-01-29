<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

// We relocated this file to artefact/artefact.php to make it easier to some day do per-artefact permissions.
// Redirect to the new URL.
redirect(get_config('wwwroot') . 'artefact/artefact.php?' . $_SERVER['QUERY_STRING']);
