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

$blockid = param_integer('block', null);
$extradata = json_decode(param_variable('extradata', null));

if (empty($blockid)) {
    // try to find it in extradata
    $blockid = $extradata->block;
}
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

$limit    = param_integer('limit', 10);
$offset   = param_integer('offset', 0);

$options = ArtefactTypePeerassessment::get_assessment_options();
$options->limit = $limit;
$options->offset = $offset;
$options->view = $view;
$options->block = $blockid;
$data = ArtefactTypePeerAssessment::get_assessments($options);

json_reply(false, array('data' => $data));
