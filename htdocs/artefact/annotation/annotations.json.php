<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-annotation
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);


require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'view.php');
safe_require('artefact', 'annotation');

// Pagination is not really working here so extradata won't
// really be a parameter.
$extradata = json_decode(param_variable('extradata', null));
$ispagination = false;
if (param_exists('offset')) {
    $ispagination = true;
    $limit    = param_integer('limit', 10);
    $offset   = param_integer('offset');
}

if (!isset($extradata)) {
    $viewid = json_decode(param_variable('viewid'));
    $annotationid = json_decode(param_variable('annotationid'));
    $artefactid = json_decode(param_variable('artefactid', ''));
    $blockid = json_decode(param_variable('blockid'));
    $extradata = new stdClass();
    $extradata->view = $viewid;
    $extradata->artefact = $artefactid;
    $extradata->annotation = $annotationid;
    $extradata->blockid = $blockid;
}

if (empty($extradata->view) || empty($extradata->annotation) || empty($extradata->blockid)) {
    json_reply('local', get_string('annotationinformationerror', 'artefact.annotation'));
}
if (!can_view_view($extradata->view)) {
    json_reply('local', get_string('noaccesstoview', 'view'));
}
if (!artefact_in_view($extradata->annotation, $extradata->view)) {
    json_reply('local', get_string('accessdenied', 'error'));
}
if (!empty($extradata->artefact) && !artefact_in_view($extradata->artefact, $extradata->view)) {
    json_reply('local', get_string('accessdenied', 'error'));
}

if ($ispagination) {
    // This is not really working yet. Need to do more work on artefact/artefact.php
    $options = ArtefactTypeAnnotationfeedback::get_annotation_feedback_options();
    $options->limit = $limit;
    $options->offset = $offset;
    $options->view = $extradata->view;
    $options->annotation = $extradata->annotation;
    $options->artefact = $extradata->artefact;
    $options->block = $extradata->blockid;
    $annotationfeedback = ArtefactTypeAnnotationfeedback::get_annotation_feedback($options);
    json_reply(false, array('data' => $annotationfeedback));
}
else {
    $view = new View($extradata->view);
    $annotationartefact = artefact_instance_from_id($extradata->annotation);
    list($feedbackcount, $annotationfeedback) = ArtefactTypeAnnotationfeedback::get_annotation_feedback_for_view($annotationartefact, $view, $extradata->blockid);
    json_reply(false, array('data' => $annotationfeedback));
}
