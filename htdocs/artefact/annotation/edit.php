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
define('MENUITEM', 'myportfolio');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('editannotationfeedback', 'artefact.annotation'));
safe_require('artefact', 'annotation');

$annotationfeedbackid = param_integer('id');
$viewid = param_integer('viewid');
$annotationfeedback = new ArtefactTypeAnnotationFeedback((int) $annotationfeedbackid);

if ($USER->get('id') != $annotationfeedback->get('author')) {
    throw new AccessDeniedException(get_string('canteditnotauthor', 'artefact.annotation'));
}

$annotationid = $annotationfeedback->get('onannotation');
$annotation = new ArtefactTypeAnnotation($annotationid);

$onview = $annotation->get('view');
if ($onview && $onview != $viewid) {
    throw new NotFoundException(get_string('annotationfeedbacknotinview', 'artefact.annotation', $annotationfeedbackid, $viewid));
}

$maxage = (int) get_config_plugin('artefact', 'annotation', 'commenteditabletime');
$editableafter = time() - 60 * $maxage;

$goto = $annotation->get_view_url($viewid, false);

if ($annotationfeedback->get('ctime') < $editableafter) {
    $SESSION->add_error_msg(get_string('cantedittooold', 'artefact.annotation', $maxage));
    redirect($goto);
}

$lastcomment = ArtefactTypeAnnotationfeedback::last_public_annotation_feedback($annotationid, $viewid, $annotation->get('artefact'));

if (!$annotationfeedback->get('private') && $annotationfeedbackid != $lastcomment->id) {
    $SESSION->add_error_msg(get_string('cantedithasreplies', 'artefact.annotation'));
    redirect($goto);
}

$elements = array();
$elements['message'] = array(
    'type'         => 'wysiwyg',
    'title'        => get_string('Annotationfeedback', 'artefact.annotation'),
    'rows'         => 5,
    'cols'         => 80,
    'defaultvalue' => $annotationfeedback->get('description'),
    'rules'        => array('maxlength' => 8192),
);
$elements['ispublic'] = array(
    'type'  => 'switchbox',
    'title' => get_string('makepublic', 'artefact.annotation'),
    'defaultvalue' => !$annotationfeedback->get('private'),
);

// What is this annotation feedback linked to? Store it in hidden fields.
$elements['viewid'] = array(
    'type'  => 'hidden',
    'value' => $viewid,
);
$elements['artefactid'] = array(
    'type'  => 'hidden',
    'value' => $annotation->get('artefact'),
);
// Save the artefactid of the annotation.
$elements['annotationid'] = array(
    'type'  => 'hidden',
    'value' => $annotationid,
);

$elements['submit'] = array(
    'type'  => 'submitcancel',
    'class' => 'btn-primary',
    'value' => array(get_string('save'), get_string('cancel')),
    'goto'  => $goto,
);

$form = pieform(array(
    'name'            => 'edit_annotation_feedback',
    'method'          => 'post',
    'plugintype'      => 'artefact',
    'pluginname'      => 'annotation',
    'elements'        => $elements,
));

function edit_annotation_feedback_validate(Pieform $form, $values) {
    require_once(get_config('libroot.php') . 'antispam.php');
    $result = probation_validate_content($values['message']);
    if ($result !== true) {
        $form->set_error('message', get_string('newuserscantpostlinksorimages1'));
    }
}

function edit_annotation_feedback_submit(Pieform $form, $values) {
    global $viewid, $annotationfeedback, $annotation, $SESSION, $goto, $USER;

    db_begin();

    $annotationfeedback->set('description', $values['message']);
    require_once(get_config('libroot') . 'view.php');
    $view = new View($viewid);
    $owner = $view->get('owner');
    $group = $annotationfeedback->get('group');
    $oldispublic = !$annotationfeedback->get('private');
    $approvecomments = $view->get('approvecomments');

    // We need to figure out what to set the 'requestpublic' field in the artefact_annotation_feedback table.
    // Then, set who is requesting to make it public - if the public flag has changed.
    if (!empty($group)
        && ($approvecomments || (!$approvecomments && $view->user_comments_allowed($USER) == 'private'))
        && $values['ispublic'] && !$USER->can_edit_view($view)
        && $values['ispublic'] != $oldispublic) {
        // This annotation belongs to a group - but this shouldn't really happen - keeping in case
        // we allow annotations in group views.
        // 1. If approvecomments on this view is switched on and
        //    the author of the feedback wants to make it public and
        //    the author of the feeback can't edit the group view and
        //    the auther of the feedback has changed the public setting,
        // the owner of the view needs to approve the feedback before it's made public.
        // 2. If approvecomments on this view is switched off and
        //    the access (for the author of the feedback) of the view forces private comments and
        //    the author of the feeback can't edit the view and
        //    the auther of the feedback has changed the public setting,
        // the owner of the view needs to approve the feedback before it's made public.

        // The author of the feedback wants to make the feedback public.
        $annotationfeedback->set('requestpublic', 'author');
    }
    else if (($approvecomments || (!$approvecomments && $view->user_comments_allowed($USER) == 'private'))
             && $values['ispublic']
             && !empty($owner)
             && $owner != $annotationfeedback->get('author')
             && $values['ispublic'] != $oldispublic) {
        // 1. If approvecomments on this view is switched on and
        //    the author of the feedback would like to make this public and
        //    the author of the feeback is not the owner of the view and
        //    the auther of the feedback has changed the public setting,
        // the owner of the view needs to approve the feedback before it's made public.
        // 2. If approvecomments on this view is switched off and
        //    the access (for the author of the feedback) of the view forces private feedback and
        //    the author of the feeback is not the owner of the view and
        //    the auther of the feedback has changed the public setting,
        // the owner of the view needs to approve the feedback before it's made public.

        // The author of the feedback wants to make the feedback public.
        $annotationfeedback->set('requestpublic', 'author');
    }
    else {
        // Otherwise, the owner of the feedback is editing the feedback.
        // Set the privacy setting of the feedback - based on the 'ispublic' flag set by the user.
        // And, clear the request to make the feedback public.
        $annotationfeedback->set('private', 1 - (int) $values['ispublic']);
        $annotationfeedback->set('requestpublic', null);
    }
    $annotationfeedback->commit();

    require_once('activity.php');
    $data = (object) array(
        'annotationfeedbackid' => $annotationfeedback->get('id'),
        'annotationid'         => $annotation->get('id'),
        'viewid'               => $viewid,
        'artefactid'           => '',
    );
    activity_occurred('annotationfeedback', $data, 'artefact', 'annotation');
    if ($annotationfeedback->get('requestpublic') == 'author') {
        if (!empty($owner)) {
            edit_annotation_feedback_notify($view, $annotationfeedback->get('author'), $owner);
        }
        else if (!empty($group)) {
            $group_admins = group_get_admin_ids($group);
            // TODO: need to notify the group admins bug #1197197
        }
    }

    db_commit();

    $SESSION->add_ok_msg(get_string('annotationfeedbackupdated', 'artefact.annotation'));
    redirect($goto);
}

function edit_annotation_feedback_notify($view, $author, $owner) {
    global $annotation, $SESSION;

    $data = (object) array(
        'subject'   => false,
        'message'   => false,
        'strings'   => (object) array(
            'subject' => (object) array(
                'key'     => 'makepublicrequestsubject',
                'section' => 'artefact.annotation',
                'args'    => array(),
            ),
            'message' => (object) array(
                'key'     => 'makepublicrequestbyauthormessage',
                'section' => 'artefact.annotation',
                'args'    => array(hsc(display_name($author, $owner))),
            ),
            'urltext' => (object) array(
                'key'     => 'annotation',
                'section' => 'artefact.annotation',
            ),
        ),
        'users'     => array($owner),
        'url'       => $annotation->get_view_url($view->get('id'), true, false),
    );
    if (!empty($owner)) {
        $SESSION->add_ok_msg(get_string('makepublicrequestsent', 'artefact.annotation', display_name($owner)));
    }
    activity_occurred('maharamessage', $data);
}

$smarty = smarty();
$smarty->assign('strdescription', get_string('editannotationfeedbackdescription', 'artefact.annotation', $maxage));
$smarty->assign('form', $form);
$smarty->display('artefact:annotation:edit.tpl');
