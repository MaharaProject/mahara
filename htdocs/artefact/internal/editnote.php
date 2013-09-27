<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'internal');
define('SECTION_PAGE', 'editnote');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('license.php');
safe_require('artefact', 'internal');
safe_require('artefact', 'file');

define('TITLE', get_string('editnote', 'artefact.internal'));

$note = param_integer('id');
$artefact = new ArtefactTypeHtml($note);
if (!$USER->can_edit_artefact($artefact) || $artefact->get('locked')) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$goto = get_config('wwwroot') . 'artefact/internal/notes.php';
if ($group = $artefact->get('group')) {
    define('MENUITEM', 'groups');
    define('GROUP', $group);
    $goto .= '?group=' . $group;
}
else if ($institution = $artefact->get('institution')) {
    define('INSTITUTIONALADMIN', 1);
    define('MENUITEM', 'manageinstitutions');
    $goto .= '?institution=' . $institution;
}
else {
    define('MENUITEM', 'content/notes');
}

$folder = param_integer('folder', 0);
$browse = (int) param_variable('browse', 0);
$highlight = null;
if ($file = param_integer('file', 0)) {
    $highlight = array($file);
}


$form = array(
    'name'              => 'editnote',
    'method'            => 'post',
    'jsform'            => true,
    'newiframeonsubmit' => true,
    'jssuccesscallback' => 'editnote_callback',
    'jserrorcallback'   => 'editnote_callback',
    'plugintype'        => 'artefact',
    'pluginname'        => 'internal',
    'configdirs'        => array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/file/form/'),
    'elements' => array(
        'title' => array(
            'type'         => 'text',
            'title'        => get_string('Title', 'artefact.internal'),
            'defaultvalue' => $artefact->get('title'),
        ),
        'description' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('Note', 'artefact.internal'),
            'rows'         => 10,
            'cols'         => 70,
            'defaultvalue' => $artefact->get('description'),
        ),
        'tags' => array(
            'type'         => 'tags',
            'title'        => get_string('tags'),
            'description'  => get_string('tagsdescprofile'),
            'defaultvalue' => $artefact->get('tags'),
        ),
        'license' => license_form_el_basic($artefact),
        'licensing_advanced' => license_form_el_advanced($artefact),
        'filebrowser' => array(
            'type'         => 'filebrowser',
            'title'        => get_string('attachments', 'artefact.blog'),
            'folder'       => $folder,
            'highlight'    => $highlight,
            'browse'       => $browse,
            'page'         => get_config('wwwroot') . 'artefact/internal/editnote.php?id=' . $note . '&browse=1',
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
            'selectcallback'     => 'add_note_attachment',
            'unselectcallback'   => 'delete_note_attachment',
        ),
        'allowcomments' => array(
            'type'         => 'checkbox',
            'title'        => get_string('allowcomments', 'artefact.comment'),
            'defaultvalue' => $artefact->get('allowcomments'),
        ),
        'perms' => array(
            'type'         => 'rolepermissions',
            'title'        => get_string('Permissions'),
            'defaultvalue' => $artefact->get('rolepermissions'),
            'group'        => $group,
            'ignore'       => !$group,
        ),
        'submitnote' => array(
            'type'         => 'submitcancel',
            'value'        => array(get_string('save'), get_string('cancel')),
            'goto'         => $goto,
        ),
    ),
);
if (!get_config('licensemetadata')) {
    unset($form['elements']['license']);
    unset($form['elements']['licensing_advanced']);
}
$form = pieform($form);

/*
 * Javascript specific to this page.  Creates the list of files
 * attached to the note.
 */
$wwwroot = get_config('wwwroot');
$noimagesmessage = json_encode(get_string('noimageshavebeenattachedtothispost', 'artefact.blog'));
$javascript = <<<EOF

// Override the image button on the tinyMCE editor.  Rather than the
// normal image popup, open up a modified popup which allows the user
// to select an image from the list of image files attached to the
// note.

// Get all the files in the attached files list that have been
// recognised as images.  This function is called by the the popup
// window, but needs access to the attachment list on this page
function attachedImageList() {
    var images = [];
    var attachments = editnote_filebrowser.selecteddata;
    for (var a in attachments) {
        if (attachments[a].artefacttype == 'image' || attachments[a].artefacttype == 'profileicon') {
            images.push({
                'id': attachments[a].id,
                'name': attachments[a].title,
                'description': attachments[a].description ? attachments[a].description : ''
            });
        }
    }
    return images;
}

function imageSrcFromId(imageid) {
    return config.wwwroot + 'artefact/file/download.php?file=' + imageid;
}

function imageIdFromSrc(src) {
    var artefactstring = 'download.php?file=';
    var ind = src.indexOf(artefactstring);
    if (ind != -1) {
        return src.substring(ind+artefactstring.length, src.length);
    }
    return '';
}

var imageList = {};

function noteImageWindow(ui, v) {
    var t = tinyMCE.activeEditor;

    imageList = attachedImageList();

    var template = new Array();

    template['file'] = '{$wwwroot}artefact/blog/image_popup.php';
    template['width'] = 355;
    template['height'] = 275 + (tinyMCE.isMSIE ? 25 : 0);

    // Language specific width and height addons
    template['width'] += t.getLang('lang_insert_image_delta_width', 0);
    template['height'] += t.getLang('lang_insert_image_delta_height', 0);
    template['inline'] = true;

    t.windowManager.open(template);
}

function editnote_callback(form, data) {
    editnote_filebrowser.callback(form, data);
};

EOF;

$smarty = smarty(array(), array(), array(), array(
    'tinymcesetup' => "ed.addCommand('mceImage', noteImageWindow);",
    'sideblocks' => array(
        array(
            'name'   => 'quota',
            'weight' => -10,
            'data'   => array(),
        ),
    ),
));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign_by_ref('form', $form);
$smarty->assign('PAGEHEADING', $artefact->get('title'));
$smarty->display('form.tpl');


function editnote_submit(Pieform $form, array $values) {
    global $SESSION, $artefact, $goto;

    db_begin();
    $artefact->set('title', $values['title']);
    $artefact->set('description', $values['description']);
    $artefact->set('tags', $values['tags']);
    $artefact->set('allowcomments', (int) $values['allowcomments']);
    if (isset($values['perms'])) {
        $artefact->set('rolepermissions', $values['perms']);
        $artefact->set('dirty', true);
    }
    if (get_config('licensemetadata')) {
        $artefact->set('license', $values['license']);
        $artefact->set('licensor', $values['licensor']);
        $artefact->set('licensorurl', $values['licensorurl']);
    }
    $artefact->commit();

    // Attachments
    $old = $artefact->attachment_id_list();
    $new = is_array($values['filebrowser']) ? $values['filebrowser'] : array();
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
        'message' => get_string('noteupdated', 'artefact.internal'),
        'goto'    => $goto,
    );
    if ($form->submitted_by_js()) {
        // Redirect back to the note page from within the iframe
        $SESSION->add_ok_msg($result['message']);
        $form->json_reply(PIEFORM_OK, $result, false);
    }
    $form->reply(PIEFORM_OK, $result);
}

function add_note_attachment($attachmentid) {
    global $artefact;
    if ($artefact) {
        $artefact->attach($attachmentid);
    }
}

function delete_note_attachment($attachmentid) {
    global $artefact;
    if ($artefact) {
        $artefact->detach($attachmentid);
    }
}
