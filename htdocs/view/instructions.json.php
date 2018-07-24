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
require_once('view.php');

global $USER;

$view = new View(param_integer('viewid'));
$action = param_alphanumext('action', '');

if (!$USER->can_edit_view($view)) {
    json_reply(true, get_string('accessdenied', 'error'));
    exit;
}

switch ($action) {
    case 'hide':
        $view->set('instructionscollapsed', 1);
        json_reply(false, array('message' => false, 'data' => 'success'));
        break;
    case 'show':
        $view->set('instructionscollapsed', 0);
        json_reply(false, array('message' => false, 'data' => 'success'));
        break;
}

json_reply(true, get_string('noviewcontrolaction', 'error', $action));
