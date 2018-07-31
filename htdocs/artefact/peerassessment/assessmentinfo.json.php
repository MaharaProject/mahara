<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-peerassessment
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'view.php');
safe_require('artefact', 'peerassessment');
safe_require('blocktype', 'peerassessment');

$id = param_integer('id', null);
$blockid = param_integer('block', null);

$block = new BlockInstance($blockid);

// Is the block correct type
if ($block->get('blocktype') != 'peerassessment') {
    json_reply('local', get_string('wrongblocktype', 'view'));
}

$view = $block->get_view();
$viewid = $view->get('id');
// Is the block on a page we can see
if (!can_view_view($viewid)) {
    json_reply('local', get_string('noaccesstoview', 'view'));
}

$item = new ArtefactTypePeerassessment($id);
$data = new stdClass();
$data->id = $item->get('id');
$data->message = $item->get('description');

json_reply(false, array('data' => $data));
