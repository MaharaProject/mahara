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
$viewid = param_integer('view', null);
$signoff = param_integer('signoff', null);
$verify = param_integer('verify', null);

if (empty($viewid) && !empty($blockid)) {
    // try to find view from blockid
    $bi = new ArtefactTypePeerassessment($blockid);
    $viewid = $bi->get_view()->get('id');
}

// Does the view have a peerassessment block
if (!$blocks = get_records_sql_array("SELECT id FROM {block_instance} WHERE view = ? AND blocktype = ?", array($viewid, 'peerassessment'))) {
    json_reply('local', get_string('wrongblocktype', 'view'));
}

$view = new View($viewid);
$signable = ArtefactTypePeerassessment::is_signable($view);
$verifiable = ArtefactTypePeerassessment::is_verifiable($view);

$data = new stdClass();
if ($signable && $signoff !== null) {
    $currentstate = ArtefactTypePeerassessment::is_signed_off($view);
    $newstate = 1 - (int)$currentstate;
    execute_sql("UPDATE {view_signoff_verify} SET signoff = ? WHERE view = ?", array($newstate, $viewid));
    if ($currentstate == 1) {
        // we are removing sign-off so we should also remove verify as well
        execute_sql("UPDATE {view_signoff_verify} SET verified = ? WHERE view = ?", array(0, $viewid));
        $data->verify_change = 1;
    }
    $data->signoff_newstate = $newstate;
}
else if ($verifiable && !empty($verify)) {
    $currentstate = ArtefactTypePeerassessment::is_verified($view);
    $newstate = 1;
    if ((int)$currentstate != $newstate) {
        execute_sql("UPDATE {view_signoff_verify} SET verified = ? WHERE view = ?", array($newstate, $viewid));
    }
    $data->verify_newstate = $newstate;
}
else {
    // No rights to do what is requested
    json_reply('local', get_string('wrongassessmentviewrequest', 'artefact.peerassessment'));
}
json_reply(false, array('message' => get_string('assessmentviewupdated', 'artefact.peerassessment'),
                        'data' => $data));
