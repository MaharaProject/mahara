<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'content/resume');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'resume');
define('RESUME_SUBPAGE', 'goalsandskills');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('docroot') . 'artefact/lib.php');
safe_require('artefact', 'resume');
safe_require('artefact', 'file');

if (!PluginArtefactResume::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('resume','artefact.resume')));
}

define('TITLE', get_string('resume', 'artefact.resume'));
define('SUBSECTIONHEADING', get_string('goalsandskills',  'artefact.resume'));
$id = param_integer('id', 0);
$type = param_variable('type', '');

if ($id > 0) {
    $artefact = artefact_instance_from_id($id);
    $type = $artefact->get('artefacttype');
}
else if ($id == 0 && !empty($type)) {
    $classname = generate_artefact_class_name($type);
    try {
        $artefact = artefact_instance_from_type($type);
    }
    catch (Exception $e) {
        $artefact = new $classname(0, array('owner' => $USER->get('id')));
        $artefact->commit();
    }
}
else {
    throw new ArtefactNotFoundException(get_string('cannotfindcreateartefact', 'artefact.resume'));
}

if ($artefact->get('owner') != $USER->get('id')) {
    throw new AccessDeniedException(get_string('notartefactowner', 'error'));
}

$folder = param_integer('folder', 0);
$browse = (int) param_variable('browse', 0);
$highlight = null;
if ($file = param_integer('file', 0)) {
    $highlight = array($file);
}


$form = pieform(array(
    'name'              => 'editgoalsandskills',
    'method'            => 'post',
    'jsform'            => true,
    'newiframeonsubmit' => true,
    'jssuccesscallback' => 'editgoalsandskills_callback',
    'jserrorcallback'   => 'editgoalsandskills_callback',
    'plugintype'        => 'artefact',
    'pluginname'        => 'resume',
    'configdirs'        => array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/file/form/'),
    'elements' => array(
        'description' => array(
            'type' => 'wysiwyg',
            'title' => get_string('description', 'artefact.resume'),
            'rows' => 20,
            'cols' => 65,
            'defaultvalue' => $artefact->get('description'),
            'rules' => array('maxlength' => 65536),
        ),
        'filebrowser' => array(
            'type'         => 'filebrowser',
            'title'        => get_string('attachments', 'artefact.blog'),
            'folder'       => $folder,
            'highlight'    => $highlight,
            'browse'       => $browse,
            'page'         => get_config('wwwroot') . 'artefact/resume/editgoalsandskills.php?id=' . $artefact->get('id') . '&browse=1',
            'browsehelp'   => 'browsemyfiles',
            'config'       => array(
                'upload'          => true,
                'uploadagreement' => get_config_plugin('artefact', 'file', 'uploadagreement'),
                'resizeonuploaduseroption' => get_config_plugin('artefact', 'file', 'resizeonuploaduseroption'),
                'resizeonuploaduserdefault' => $USER->get_account_preference('resizeonuploaduserdefault'),
                'createfolder'    => false,
                'edit'            => false,
                'select'          => true,
            ),
            'defaultvalue'       => $artefact->attachment_id_list(),
            'selectlistcallback' => 'artefact_get_records_by_id',
            'selectcallback'     => 'add_resume_attachment',
            'unselectcallback'   => 'delete_resume_attachment',
        ),
        'artefacttype' => array(
            'type' => 'hidden',
            'value' => $artefact->get('artefacttype'),
        ),
        'submitform' => array(
            'type' => 'submitcancel',
            'class' => 'btn-primary',
            'value' => array(get_string('save'), get_string('cancel')),
            'goto' => get_config('wwwroot') . 'artefact/resume/goalsandskills.php',
        ),
    )
));

/*
 * Javascript specific to this page.  Creates the list of files
 * attached to the resume goals or skills.
 */
$wwwroot = get_config('wwwroot');
$noimagesmessage = json_encode(get_string('noimageshavebeenattachedtothispost', 'artefact.blog'));
$javascript = <<<EOF
function editgoalsandskills_callback(form, data) {
    editgoalsandskills_filebrowser.callback(form, data);
};
EOF;

$smarty = smarty(array(), array(), array(), array(
    'tinymceconfig' => '
        image_filebrowser: "editgoalsandskills_filebrowser",
    ',
    'sideblocks' => array(
        array(
            'name'   => 'quota',
            'weight' => -10,
            'data'   => array(),
        ),
    ),
));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('SUBPAGENAV', PluginArtefactResume::submenu_items());
$smarty->assign('artefactform', $form);
$smarty->assign('artefacttype', $type);
$smarty->display('artefact:resume:editgoalsandskills.tpl');


function editgoalsandskills_submit(Pieform $form, array $values) {
    global $SESSION, $artefact, $USER;
    require_once('embeddedimage.php');

    $newdescription = EmbeddedImage::prepare_embedded_images($values['description'], $values['artefacttype'], $USER->get('id'));

    db_begin();
    $artefact->set('title', get_string($values['artefacttype'], 'artefact.resume'));
    $artefact->set('description', $newdescription);
    $artefact->commit();

    // Attachments
    $old = $artefact->attachment_id_list();
    $new = is_array($values['filebrowser']) ? $values['filebrowser'] : array();
    // only allow the attaching of files that exist and are editable by user
    foreach ($new as $key => $fileid) {
        $file = artefact_instance_from_id($fileid);
        if (!($file instanceof ArtefactTypeFile) || !$USER->can_publish_artefact($file)) {
            unset($new[$key]);
        }
    }
    if (!empty($new) || !empty($old)) {
        foreach ($old as $o) {
            if (!in_array($o, $new)) {
                try {
                    $artefact->detach($o);
                }
                catch (ArtefactNotFoundException $e) {}
            }
        }
        foreach ($new as $n) {
            if (!in_array($n, $old)) {
                try {
                    $artefact->attach($n);
                }
                catch (ArtefactNotFoundException $e) {}
            }
        }
    }
    db_commit();

    $result = array(
        'error'   => false,
        'message' => get_string('goalandskillsaved', 'artefact.resume'),
        'goto'    => get_config('wwwroot') . 'artefact/resume/goalsandskills.php',
    );
    if ($form->submitted_by_js()) {
        // Redirect back to the resume goals and skills page from within the iframe
        $SESSION->add_ok_msg($result['message']);
        $form->json_reply(PIEFORM_OK, $result, false);
    }
    $form->reply(PIEFORM_OK, $result);
}
