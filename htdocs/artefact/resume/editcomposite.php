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
define('MENUITEM', 'create/resume');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'resume');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('docroot') . 'artefact/lib.php');

safe_require('artefact', 'resume');
safe_require('artefact', 'file');

if (!PluginArtefactResume::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('resume','artefact.resume')));
}

define('TITLE', get_string('resume', 'artefact.resume'));

$id = param_integer('id');
$artefact = param_integer('artefact');

$a = artefact_instance_from_id($artefact);
$type = $a->get('artefacttype');

$tabs = PluginArtefactResume::composite_tabs();
define('MENUITEM_SUBPAGE', $tabs[$type]);
define('SUBSECTIONHEADING', get_string($type, 'artefact.resume'));

if ($a->get('owner') != $USER->get('id')) {
    throw new AccessDeniedException(get_string('notartefactowner', 'error'));
}

$folder = param_integer('folder', 0);
$browse = (int) param_variable('browse', 0);
$highlight = null;
if ($file = param_integer('file', 0)) {
    $highlight = array($file);
}

$elements = call_static_method(generate_artefact_class_name($type), 'get_addform_elements');
// Replace 'files' pieform element with 'filebrowser' one.
unset($elements['attachments']);
$elements['filebrowser'] = array(
    'type'         => 'filebrowser',
    'title'        => get_string('attachments', 'artefact.blog'),
    'folder'       => $folder,
    'highlight'    => $highlight,
    'browse'       => $browse,
    'page'         => get_config('wwwroot') . 'artefact/resume/editcomposite.php?id=' . $id . '&artefact=' . $artefact . '&browse=1',
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
    'defaultvalue'       => $a->attachment_id_list_with_item($id),
    'selectlistcallback' => 'artefact_get_records_by_id',
    'selectcallback'     => 'add_resume_attachment',
    'unselectcallback'   => 'delete_resume_attachment',
);
// Add other necessary pieform elements
$elements['submitform'] = array(
    'type' => 'submitcancel',
    'class' => 'btn-primary',
    'value' => array(get_string('save'), get_string('cancel')),
    'goto' => get_config('wwwroot') . 'artefact/resume/' . $tabs[$type] . '.php',
);
$elements['compositetype'] = array(
    'type' => 'hidden',
    'value' => $type,
);
$cform = array(
    'name'              => 'editcomposite',
    'method'            => 'post',
    'jsform'            => true,
    'newiframeonsubmit' => true,
    'jssuccesscallback' => 'editcomposite_callback',
    'jserrorcallback'   => 'editcomposite_callback',
    'plugintype'        => 'artefact',
    'pluginname'        => 'resume',
    'successcallback'   => 'compositeformedit_submit',
    'configdirs'        => array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/file/form/'),
    'elements'          => $elements,
);

$a->populate_form($cform, $id, $type);
$compositeform = pieform($cform);

$javascript = <<<EOF
function editcomposite_callback(form, data) {
    editcomposite_filebrowser.callback(form, data);
    if (data.error) {
        formError(form, data);
    }
};
$(function($) {
    $('#page-modal').on('hidden.bs.modal', function (e) {
        // check if the upload file modal is still visible and if so put the body class back to allow scrolling
        if ($('#editcomposite_filebrowser_upload_browse').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });
});
EOF;

$smarty = smarty(array('js/switchbox.js'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('compositeform', $compositeform);
$smarty->assign('SUBPAGENAV', PluginArtefactResume::submenu_items());
$smarty->display('artefact:resume:editcomposite.tpl');
