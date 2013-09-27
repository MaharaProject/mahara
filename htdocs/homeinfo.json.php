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
define('JSON', 1);

require('init.php');

$showhomeinfo = (int) param_boolean('showhomeinfo');

$result = array();

$USER->set_account_preference('showhomeinfo', $showhomeinfo);

json_reply(false, $result);
