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
require(dirname(dirname(__FILE__)) . '/init.php');

$result = array();

if ($USER->is_logged_in()) {
    $result['quota']     = $USER->get('quota');
    $result['quotaused'] = $USER->get('quotaused');
}

json_headers();
print json_encode($result);
