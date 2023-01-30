<?php

/**
 * Fetch the checkpoint feedback comments
 *
 * @package    mahara
 * @subpackage artefact-checkpoint
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'view.php');
safe_require('artefact', 'checkpoint');
safe_require('blocktype', 'checkpoint');

$id = param_integer('id');
$blockid = param_integer('block');
$block = new BlockInstance($blockid);

// Is the block correct type
if ($block->get('blocktype') != 'checkpoint') {
    json_reply('local', get_string('wrongblocktype', 'view'));
}

$view = $block->get_view();
$viewid = $view->get('id');
// Is the block on a page we can see
if (!can_view_view($viewid)) {
    json_reply('local', get_string('noaccesstoview', 'view'));
}

$data = get_field('artefact', 'description', 'id', $id, 'artefacttype', 'checkpointfeedback');
json_reply(false, array('data' => $data));
