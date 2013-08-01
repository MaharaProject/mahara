<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'content/resume');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'resume');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('docroot') . 'artefact/lib.php');

safe_require('artefact', 'resume');
safe_require('artefact', 'file');

define('TITLE', get_string('resume', 'artefact.resume'));

$id = param_integer('id');
$artefact = param_integer('artefact');

$a = artefact_instance_from_id($artefact);
$type = $a->get('artefacttype');

$tabs = PluginArtefactResume::composite_tabs();
define('RESUME_SUBPAGE', $tabs[$type]);

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
};
EOF;

$smarty = smarty();
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('compositeform', $compositeform);
$smarty->assign('composite', $type);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('SUBPAGENAV', PluginArtefactResume::submenu_items());
$smarty->display('artefact:resume:editcomposite.tpl');
