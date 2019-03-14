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
define('MENUITEM', 'myportfolio');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('editassessment', 'artefact.peerassessment'));
safe_require('artefact', 'peerassessment');

$id = param_integer('id');
$viewid = param_integer('view');
$assessment = new ArtefactTypePeerassessment($id);

if ($USER->get('id') != $assessment->get('author')) {
    throw new AccessDeniedException(get_string('canteditnotauthor', 'artefact.peerassessment'));
}

$onview = $assessment->get('onview');
if ($onview && $onview != $viewid) {
    throw new NotFoundException(get_string('notinview', 'artefact.peerassessment', $id, $viewid));
}

$maxage = (int) get_config_plugin('artefact', 'comment', 'commenteditabletime');
$editableafter = time() - 60 * $maxage;

$goto = $assessment->get_view_url($viewid, false);

if ($assessment->get('ctime') < $editableafter) {
    $SESSION->add_error_msg(get_string('cantedittooold', 'artefact.peerassessment', $maxage));
    redirect($goto);
}

$lastcomment = ArtefactTypePeerassessment::last_public_assessment($viewid);

if (!$assessment->get('private') && $id != $lastcomment->id) {
    $SESSION->add_error_msg(get_string('cantedithasreplies', 'artefact.peerassessment'));
    redirect($goto);
}

$elements = array();
$elements['message'] = array(
    'type'         => 'wysiwyg',
    'title'        => get_string('message'),
    'rows'         => 5,
    'cols'         => 80,
    'defaultvalue' => $assessment->get('description'),
    'rules'        => array('maxlength' => 1000000),
);
$elements['ispublic'] = array(
    'type'  => 'switchbox',
    'title' => get_string('makeassessmentpublic', 'artefact.peerassessment'),
    'defaultvalue' => !$assessment->get('private'),
);
if (get_config('licensemetadata')) {
    $elements['license'] = license_form_el_basic($assessment);
    $elements['licensing_advanced'] = license_form_el_advanced($assessment);
}
$elements['submit'] = array(
    'type'  => 'submitcancel',
    'class' => 'btn-primary',
    'value' => array(get_string('save'), get_string('cancel')),
    'goto'  => $goto,
);

$form = pieform(array(
    'name'            => 'edit_assessment',
    'method'          => 'post',
    'plugintype'      => 'artefact',
    'pluginname'      => 'peerassessment',
    'elements'        => $elements,
));

function edit_assessment_submit(Pieform $form, $values) {
    global $assessment, $SESSION, $goto, $USER;
    require_once('embeddedimage.php');

    db_begin();
    require_once(get_config('libroot') . 'view.php');
    $view = $assessment->get_view();
    $owner = $view->get('owner');
    $newdescription = EmbeddedImage::prepare_embedded_images($values['message'], 'assessment', $assessment->get('id'));
    $assessment->set('description', $newdescription);
    $assessment->set('private', 1 - (int) $values['ispublic']);
    $assessment->commit();

    require_once('activity.php');
    $data = (object) array(
        'assessmentid' => $assessment->get('id'),
        'viewid'       => $viewid,
    );

    db_commit();

    $SESSION->add_ok_msg(get_string('assessmentupdated', 'artefact.peerassessment'));
    redirect($goto);
}

$smarty = smarty();
$smarty->assign('strdescription', get_string('editassessmentdescription', 'artefact.peerassessment', $maxage));
$smarty->assign('form', $form);
$smarty->display('artefact:peerassessment:edit.tpl');
