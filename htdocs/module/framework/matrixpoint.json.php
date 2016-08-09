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
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('module', 'framework');
safe_require('artefact', 'annotation');
safe_require('blocktype', 'annotation');

global $USER;

$framework  = param_integer('framework');
$option     = param_integer('option');
$view       = param_integer('view');
$action     = param_alphanum('action', 'form');

$evidence = get_record('framework_evidence', 'framework', $framework, 'element', $option, 'view', $view);

if ($action == 'update') {
    require_once(get_config('docroot') . 'blocktype/lib.php');
    $title = param_alphanumext('title', 'Annotation');
    $text = param_variable('text', '');
    $allowfeedback = param_boolean('allowfeedback');
    $retractable = param_integer('retractable', 0);
    $blockid = param_integer('blockconfig', 0);
    $tags = param_variable('tags', '');
    $tags = explode(',', $tags);
    $values = array('title' => $title,
                    'text' => $text,
                    'tags' => $tags,
                    'retractable' => $retractable,
                    'retractedonload' => 0,
                    );
    $bi = new BlockInstance($blockid);

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
        $id = Framework::save_evidence(null, $framework, $option, $view, $bi->get('id'));
        $message = get_string('matrixpointinserted', 'module.framework');
    }

    $class = 'icon icon-circle-o danger';

    $data = (object) array('id' => $id,
                           'class' => $class,
                           'view' => $view,
                           'option' => $option
                           );
    json_reply(false, array('message' => $message, 'data' => $data));
}
if ($action == 'delete') {
    // Clean up partial annotation block instance
    require_once(get_config('docroot') . 'blocktype/lib.php');
    $blockid = param_integer('blockconfig', 0);
    $bi = new BlockInstance($blockid);
    $bi->delete();
    $data = (object) array('class' => false,
                           'view' => $view,
                           'option' => $option
                           );
    json_reply(false, array('message' => '', 'data' => $data));
}
else {
    $message = null;
    $state = ($evidence) ? $evidence->state : -1;
    $states = Framework::get_state_array($state);
    $params = (object) array(
        'framework' => $framework,
        'option' => $option,
        'view' => $view,
        'id' => ($evidence) ? $evidence->id : null,
        'annotation' => ($evidence) ? $evidence->annotation : null,
        'begun' => $states['begun'],
        'incomplete' => $states['incomplete'],
        'partialcomplete' => $states['partialcomplete'],
        'completed' => $states['completed'],
    );
    $form = Framework::annotation_config_form($params);
    $data = (object) array('form' => $form);
    json_reply(false, (object) array('message' => $message, 'data' => $data));
}