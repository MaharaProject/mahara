<?php
/**
 *
 * @package    mahara
 * @subpackage module-framework
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('module', 'framework');
safe_require('artefact', 'annotation');
safe_require('blocktype', 'annotation');

global $USER;

if (!is_plugin_active('annotation','blocktype')) {
    json_reply(true, get_string('needtoactivate', 'module.framework'));
}

$framework  = param_integer('framework');
$option     = param_integer('option');
$viewid       = param_integer('view');
$action     = param_alphanum('action', 'form');
form_validate(param_variable('sesskey', null));
$evidence = get_record('framework_evidence', 'framework', $framework, 'element', $option, 'view', $viewid);

if ($action == 'update') {
    // When we click a dot on the matrix and add an annotation
    require_once(get_config('docroot') . 'blocktype/lib.php');
    $shortname = get_field_sql("SELECT fse.shortname FROM {framework_standard_element} fse
                                JOIN {framework_standard} fs ON fs.id = fse.standard
                                WHERE fs.framework = ? and fse.id = ?", array($framework, $option));
    $title = get_string('Annotation', 'artefact.annotation') . ': ' . $shortname;
    $text = clean_html(param_variable('text', ''));
    $allowfeedback = param_boolean('allowfeedback');
    $retractable = param_integer('retractable', 0);
    $blockid = param_integer('blockconfig', 0);
    $tags = param_variable('tags', '');
    $tags = explode(',', $tags);
    $values = array('title' => $title,
                    'text' => $text,
                    'tags' => $tags,
                    'allowfeedback' => $allowfeedback,
                    'retractable' => $retractable,
                    'retractedonload' => 0,
                    );
    $bi = new BlockInstance($blockid);
    $view = $bi->get_view();
    if (!$USER->can_edit_view($view)) {
        json_reply(true, get_string('accessdenied', 'error'));
        exit;
    }

    $values = call_static_method(generate_class_name('blocktype', $bi->get('blocktype')), 'instance_config_save', $values, $bi);
    $title = (isset($values['title'])) ? $values['title'] : '';
    unset($values['title']);
    unset($values['_redrawblocks']);

    $bi->set('configdata', $values);
    $bi->set('title', $title);
    $bi->commit();

    if ($evidence) {
        $id = Framework::save_evidence($evidence->id, null, null, null, $bi->get('id'));
        $message = get_string('matrixpointupdated', 'module.framework');
    }
    else {
        $id = Framework::save_evidence(null, $framework, $option, $view->get('id'), $bi->get('id'));
        $message = get_string('matrixpointinserted', 'module.framework');
    }

    $class = 'icon icon-circle-o begun';
    $choices = Framework::get_evidence_statuses($framework);
    $data = (object) array('id' => $id,
                           'class' => $class,
                           'view' => $view->get('id'),
                           'readyforassessment' => 1,
                           'option' => $option,
                           'title' => $choices[0]
                           );
    json_reply(false, array('message' => $message, 'data' => $data));
}
else if ($action == 'evidence') {
    global $USER;
    // When we click on one of the begun/incomplete/partialcomplete/completed symbols and submit that form
    if (!$evidence->id) {
        // problem need to return error
        json_reply(true, get_string('accessdenied', 'error'));
        exit;
    }
    require_once('view.php');
    $view = new View($evidence->view);
    if (!Framework::can_assess_user($view, $evidence->framework)) {
        json_reply(true, get_string('accessdenied', 'error'));
        exit;
    }

    $oldstate = $evidence->state;
    $reviewer = null;
    $assessment = param_alphanum('assessment', 0);
    $assessment = (int) $assessment;
    if (Framework::EVIDENCE_COMPLETED === $assessment) {
        $reviewer = $USER->get('id');
    }

    $id = Framework::save_evidence($evidence->id, null, null, null, $evidence->annotation, $assessment, $USER->get('id'));
    $message = get_string('matrixpointupdated', 'module.framework');

    // If we are changing to/from completed we need to change $completed to adjust the count on screen
    $readyforassessment = 0;
    if ((Framework::EVIDENCE_BEGUN === (int) $oldstate) && (Framework::EVIDENCE_BEGUN !== $assessment)) {
        $readyforassessment = -1;
    }
    else if (Framework::EVIDENCE_BEGUN === $assessment) {
        $readyforassessment = 1;
    }
    $dontmatch = 0;
    if ((Framework::EVIDENCE_INCOMPLETE === (int) $oldstate) && (Framework::EVIDENCE_INCOMPLETE !== $assessment)) {
        $dontmatch = -1;
    }
    else if (Framework::EVIDENCE_INCOMPLETE === $assessment) {
        $dontmatch = 1;
    }
    $partiallycomplete = 0;
    if ((Framework::EVIDENCE_PARTIALCOMPLETE === (int) $oldstate) && (Framework::EVIDENCE_PARTIALCOMPLETE !== $assessment)) {
        $partiallycomplete = -1;
    }
    else if (Framework::EVIDENCE_PARTIALCOMPLETE === $assessment) {
        $partiallycomplete = 1;
    }
    $completed = 0;
    if ((Framework::EVIDENCE_COMPLETED === (int) $oldstate) && (Framework::EVIDENCE_COMPLETED !== $assessment)) {
        $completed = -1;
    }
    else if (Framework::EVIDENCE_COMPLETED === $assessment) {
        $completed = 1;
    }

    $currentstate = Framework::get_state_array($assessment, true);
    $class = $currentstate['classes'];
    $choices = Framework::get_evidence_statuses($framework);
    $data = (object) array('id' => $id,
                           'class' => $class,
                           'view' => $view->get('id'),
                           'readyforassessment' => $readyforassessment,
                           'dontmatch' => $dontmatch,
                           'partiallycomplete' => $partiallycomplete,
                           'completed' => $completed,
                           'option' => $option,
                           'title' => $choices[$assessment]
                           );
    json_reply(false, array('message' => $message, 'data' => $data));
}
else if ($action == 'feedback') {
    $annotationid = param_integer('annotationid');
    $annotation = new ArtefactTypeAnnotation((int) $annotationid);
    $blockid = param_integer('blockid');
    $bi = new BlockInstance($blockid);
    $message = param_variable('message');
    $ispublic = param_boolean('ispublic');
    $view = $bi->get_view();
    if (!can_view_view($view->get('id')) || !PluginBlocktypeAnnotation::has_feedback_allowed($bi->get('id'))) {
        json_reply(true, get_string('accessdenied', 'error'));
        exit;
    }
    $newlist = ArtefactTypeAnnotationfeedback::save_matrix_feedback($annotation, $view, $blockid, $message, $ispublic);
    $message = get_string('annotationfeedbacksubmitted', 'artefact.annotation');
    $data = (object) array('id' => $evidence->id, 'tablerows' => $newlist);
    json_reply(false, array('message' => $message, 'data' => $data));
}
else if ($action == 'delete') {
    // Clean up partial annotation block instance
    require_once(get_config('docroot') . 'blocktype/lib.php');
    $blockid = param_integer('blockconfig', 0);
    $bi = new BlockInstance($blockid);
    $view = $bi->get_view();
    if (!$USER->can_edit_view($view)) {
        json_reply(true, get_string('accessdenied', 'error'));
        exit;
    }
    $bi->delete();
    $data = (object) array('class' => false,
                           'view' => $view->get('id'),
                           'option' => $option
                           );
    json_reply(false, array('message' => '', 'data' => $data));
}
else {
    if (!can_view_view($viewid)) {
        json_reply(true, get_string('accessdenied', 'error'));
        exit;
    }
    $message = null;
    $state = ($evidence) ? $evidence->state : -1;
    $states = Framework::get_state_array($state);
    $params = (object) array(
        'framework' => $framework,
        'option' => $option,
        'view' => $viewid,
        'id' => ($evidence) ? $evidence->id : null,
        'annotation' => ($evidence) ? $evidence->annotation : null,
        'begun' => $states['begun']['state'],
        'incomplete' => $states['incomplete']['state'],
        'partialcomplete' => $states['partialcomplete']['state'],
        'completed' => $states['completed']['state'],
    );

    if ($evidence) {
        // There is an annotation in play
        $form = Framework::annotation_feedback_form($params);
    }
    else {
        $form = Framework::annotation_config_form($params);
    }
    $data = (object) array('form' => $form);
    json_reply(false, (object) array('message' => $message, 'data' => $data));
}
