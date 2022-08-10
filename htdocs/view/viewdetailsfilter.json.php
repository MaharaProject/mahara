<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

$usrid = param_integer('usrid', $USER->get('id'));
$field = param_variable('field');
$value = param_integer('value');

//TODO: Allow admin's to set users details preferences
if ($usrid == $USER->get('id')) {
    set_account_preference($usrid, $field, $value);
}

json_reply(false, array(
    'returncode' => 1,
));
