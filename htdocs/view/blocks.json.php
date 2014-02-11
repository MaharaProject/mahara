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

$view = new View(param_integer('id'));
$change = param_boolean('change', false);
$action = param_alphanumext('action', '');

// If the view has been submitted, disallow editing
if ($view->is_submitted()) {
    $submittedto = $view->submitted_to();
    throw new AccessDeniedException(get_string('canteditsubmitted', 'view', $submittedto['name']));
}

// we actually ned to process stuff
if ($change) {
    try {
        $returndata = $view->process_changes();
        $returndata['css'] = $view->get_all_blocktype_css();
        json_reply(false, $returndata);
    }
    catch (Exception $e) {
        json_reply(true, $e->getMessage());
    }
}
// else we're just reading data...
switch ($action) {
case 'blocktype_list':
    $category = param_alpha('c');
    $data = $view->build_blocktype_list($category, true);
    json_reply(false, array('message' => false, 'data' => $data));
    break;
}

json_reply(true, get_string('noviewcontrolaction', 'error', $action));
