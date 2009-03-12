<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage artefact-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Browser for files area.
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_filebrowser(Pieform $form, $element) {
    global $USER;
    $smarty = smarty_core();

    $prefix = $form->get_name() . '_' . $element['name'];
    $smarty->assign('prefix', $prefix);

    $group = $element['group'];
    $institution = $element['institution'];
    $userid = ($group || $institution) ? null : $USER->get('id');

    if ($group) {
        $queryparams = '&group=' . $group;
        $groupinfo = array(
            'roles' => group_get_role_info($group),
            'perms' => group_get_default_artefact_permissions($group),
            'perm'  => array(),
        );
        foreach (current($groupinfo['perms']) as $k => $v) {
            $groupinfo['perm'][$k] = get_string($k);
        }
        $smarty->assign('groupinfo', $groupinfo);
    }
    else if ($institution) {
        $queryparams = '&institution=' . $institution;
    }
    else {
        $queryparams = '';
    }


    $folder = $element['folder'];
    $path = pieform_element_filebrowser_get_path($folder);

    $smarty->assign('folder', $folder);
    $smarty->assign('foldername', $path[0]->title);
    $smarty->assign('path', array_reverse($path));
    $smarty->assign('group', $group);
    $smarty->assign('institution', $institution);
    $smarty->assign('queryparams', $queryparams);
    $smarty->assign('highlight', $element['highlight'][0]);
    $smarty->assign('edit', $element['edit'] ? $element['edit'] : -1);
    $config = array_map('intval', $element['config']);
    $smarty->assign('config', $config);
    $smarty->assign('agreementtext', get_field('site_content', 'content', 'name', 'uploadcopyright'));
    $filedata = ArtefactTypeFileBase::get_my_files_data($folder, $userid, $group, $institution);
    $smarty->assign('filelist', $filedata);

    $formid = $form->get_name();
    $configstr = json_encode($config);
    $fileliststr = json_encode($filedata);

    $initjs = "
var {$prefix} = new FileBrowser('{$prefix}', {$folder}, {$configstr});
{$prefix}.formname = '{$formid}';
{$prefix}.filedata = {$fileliststr};
addLoadEvent({$prefix}.init);
";

    $smarty->assign('initjs', $initjs);

    return $smarty->fetch('artefact:file:form/filebrowser.tpl');
}

function pieform_element_filebrowser_get_path($folder) {
    $path = array();
    if ($folder) {
        $folders = ArtefactTypeFileBase::artefactchooser_folder_data(artefact_instance_from_id($folder))->data;
        $f = $folder;
        while ($f) {
            $path[] = (object) array('title' => $folders[$f]->title, 'id' => $f);
            $f = $folders[$f]->parent;
        }
    }

    $path[] = (object) array('title' => get_string('home'), 'id' => 0);
    return $path;
}

function pieform_element_filebrowser_build_path($element, $folder) {
    if ($element['group']) {
        $queryparams = '&group=' . $group;
    }
    else if ($element['institution']) {
        $queryparams = '&institution=' . $institution;
    }
    else {
        $queryparams = '';
    }
    $path = pieform_element_filebrowser_get_path($folder);
    $foldername = $path[0]->title;

    $smarty = smarty_core();
    $smarty->assign('queryparams', $queryparams);
    $smarty->assign('path', array_reverse($path));
    return array('html' => $smarty->fetch('artefact:file:form/folderpath.tpl'), 'foldername' => $foldername);
}

function pieform_element_filebrowser_build_filelist($element, $folder, $highlight=null) {
    global $USER;

    $group = $element['group'];
    $institution = $element['institution'];
    $userid = ($group || $institution) ? null : $USER->get('id');
    if ($group) {
        $queryparams = '&group=' . $group;
    }
    else if ($institution) {
        $queryparams = '&institution=' . $institution;
    }
    else {
        $queryparams = '';
    }

    $config = array_map('intval', $element['config']);

    $smarty = smarty_core();
    $smarty->assign('config', $config);
    $smarty->assign('edit', -1);
    $smarty->assign('highlight', $highlight);
    $smarty->assign('queryparams', $queryparams);
    $filedata = ArtefactTypeFileBase::get_my_files_data($folder, $userid, $group, $institution);
    $smarty->assign('filelist', $filedata);

    return array(
        'data' => $filedata,
        'html' => $smarty->fetch('artefact:file:form/filelist.tpl'),
    );
}

function pieform_element_filebrowser_get_value(Pieform $form, $element) {
    $value = array();
    if (isset($_POST['folder'])) {
        $value['folder']           = (int) $_POST['folder'];
    }
    if (isset($_POST['group'])) {
        $value['group']            = $_POST['group'];
    }
    if (isset($_POST['institution'])) {
        $value['institution']      = $_POST['institution'];
    }

    if (!empty($_POST['delete'])) {
        $value['action']           = 'delete';
        $value['artefact']         = (int) $_POST['delete'];
    }
    else if (!empty($_POST['edit'])) {
        $value['action']           = 'edit';
        $value['artefact']         = (int) $_POST['edit'];
    }
    else if (!empty($_POST['update'])) {
        $value['action']           = 'update';
        $value['artefact']         = (int) $_POST['update'];
        $value['title']            = $_POST['edit_title'];
        $value['description']      = $_POST['edit_description'];
        $value['tags']             = $_POST['edit_tags'];
        if ($element['group']) {
            $value['permissions']  = array('admin' => (object) array('view' => true, 'edit' => true, 'republish' => true));
            foreach ($_POST as $k => $v) {
                if (preg_match('/^permission:([a-z]+):([a-z]+)$/', $k, $m)) {
                    $value['permissions'][$m[1]]->{$m[2]} = (bool) $v;
                }
            }
        }
    }
    else if (!empty($_POST['canceledit'])) {
        $value['action']           = 'cancel';
    }
    else if (!empty($_POST['move'])) {
        $value['action']           = 'move';
        $value['artefact']         = (int) $_POST['move'];
        $value['newparent']        = (int) $_POST['moveto'];
    }
    else if (!empty($_POST['createfolder'])) {
        $value['action']           = 'createfolder';
        $value['title']            = $_POST['createfolder_name'];
    }
    else if (!empty($_POST['upload']) || ($_FILES['userfile'] && $_FILES['userfile']['name'])) {
        $value['action']           = 'upload';
        $value['userfile']         = $_FILES['userfile'];
        $value['filename']         = isset($_FILES['userfile']['name']) ? $_FILES['userfile']['name'] : null;
        $value['uploadnumber']     = (int) $_POST['uploadnumber'];
        $value['uploadfolder']     = $_POST['folder'] ? (int) $_POST['folder'] : null;
        $value['uploadfoldername'] = $_POST['foldername'];
        $value['uploadagreement']  = $element['config']['uploadagreement'];
        if ($element['config']['uploadagreement']) {
            $value['notice']       = $_POST['notice'];
        }
    } else {
        $value['action']           = 'changefolder';
    }
    return $value;
}


function pieform_element_filebrowser_validate($values) {
    $message = '';
    if ($values['action'] == 'upload') {
        if (!isset($values['filename']) || !strlen($values['filename'])) {
            $message = get_string('filenamefieldisrequired', 'artefact.file');
        }
        else if ($values['uploadagreement'] && empty($values['notice'])) {
            $message = get_string('youmustagreetothecopyrightnotice', 'artefact.file');
        }
    }
    if (($values['action'] == 'createfolder' || $values['action'] == 'update') && (!isset($values['title']) || !strlen($values['title']))) {
        $message = get_string('foldernamerequired', 'artefact.file');
    }
    if (($values['action'] == 'delete' || $values['action'] == 'update') && !$values['artefact']) {
        $message = get_string('nofilespecified', 'artefact.file');
    }
    return array('error' => (bool) ($message != ''), 'message' => $message);
}


function pieform_element_filebrowser_submit($element, $values) {
    $function = 'pieform_element_filebrowser_' . $values['action'];
    if (function_exists($function)) {
        $result = $function($element, $values);
        // Return the parent folder here so the page can tell if the user
        // has changed folder in the meantime.
        $result['folder'] = $values['folder'];
        return $result;
    }
    return array('error' => false);
}


function pieform_element_filebrowser_upload($element, $values) {
    global $USER;

    $parentfolder     = $values['uploadfolder'] ? (int) $values['uploadfolder'] : null;
    $parentfoldername = $values['uploadfoldername'];
    $institution      = $values['institution'];
    $group            = $values['group'] ? (int) $values['group'] : null;

    $result = array('error' => false, 'uploadnumber' => (int) $values['uploadnumber']);

    $data = new StdClass;
    $data->parent      = $parentfolder;
    $data->owner       = null;

    if ($parentfolder && !$USER->can_edit_artefact(artefact_instance_from_id($parentfolder))) {
        $result['error'] = true;
        $result['message'] = get_string('cannoteditfolder', 'artefact.file');
        return $result;
    }
    if ($institution) {
        $data->institution = $institution;
    } else if ($group) {
        require_once(get_config('libroot') . 'group.php');
        if (!$parentfolder) {
            $role = group_user_access($group);
            if (!$role) {
                $result['error'] = true;
                $result['message'] = get_string('usernotingroup', 'mahara');
                return $result;
            }
            // Use default grouptype artefact permissions to check if the
            // user can upload a file to the group's root directory
            $permissions = group_get_default_artefact_permissions($group);
            if (!$permissions[$role]->edit) {
                $result['error'] = true;
                $result['message'] = get_string('cannoteditfolder', 'artefact.file');
                return $result;
            }
        }
        $data->group = $group;
    } else {
        $data->owner = $USER->get('id');
    }

    $data->container = 0;
    $data->locked = 0;

    $originalname = $_FILES['userfile']['name'];
    $originalname = $originalname ? basename($originalname) : get_string('file', 'artefact.file');
    $data->title = ArtefactTypeFileBase::get_new_file_title($originalname, $parentfolder, $data->owner, $group, $institution);

    try {
        $newid = ArtefactTypeFile::save_uploaded_file('userfile', $data);
    }
    catch (QuotaExceededException $e) {
        prepare_upload_failed_message($result, $e, $parentfoldername, $originalname);
        return $result;
    }
    catch (UploadException $e) {
        prepare_upload_failed_message($result, $e, $parentfoldername, $originalname);
        return $result;
    }

    // Upload succeeded
    if ($parentfoldername) {
        if ($data->title == $originalname) {
            $result['message'] = get_string('uploadoffiletofoldercomplete', 'artefact.file', 
                                            $originalname, $parentfoldername);
        }
        else {
            $result['message'] = get_string('fileuploadedtofolderas', 'artefact.file', 
                                            $originalname, $parentfoldername, $data->title);
        }
    }
    else if ($data->title == $originalname) {
        $result['message'] = get_string('uploadoffilecomplete', 'artefact.file', $originalname);
    }
    else {
        $result['message'] = get_string('fileuploadedas', 'artefact.file', $originalname, $data->title);
    }

    $result['highlight'] = $newid;
    $result['newlist'] = pieform_element_filebrowser_build_filelist($element, $parentfolder, $newid);
    $result['quota'] = $USER->get('quota');
    $result['quotaused'] = $USER->get('quotaused');
    return $result;
}


/**
 * Helper function used above to minimise code duplication
 */
function prepare_upload_failed_message(&$result, $exception, $parentfoldername, $title) {
    $result['error'] = true;
    if ($parentfoldername) {
        $result['message'] = get_string('uploadoffiletofolderfailed', 'artefact.file', 
                                        $title, $parentfoldername);
    }
    else {
        $result['message'] = get_string('uploadoffilefailed', 'artefact.file',  $title);
    }
    $result['message'] .= ': ' . $exception->getMessage();
}

function pieform_element_filebrowser_createfolder($element, $values) {
    global $USER;

    $parentfolder     = $values['folder'] ? (int) $values['folder'] : null;
    $institution      = $values['institution'];
    $group            = $values['group'] ? (int) $values['group'] : null;

    $result = array();

    $data = new StdClass;
    $data->parent      = $parentfolder;
    $data->owner       = null;
    $data->title       = $values['title'];

    if ($parentfolder && !$USER->can_edit_artefact(artefact_instance_from_id($parentfolder))) {
        return array('error' => true, 'message' => get_string('cannoteditfolder', 'artefact.file'));
    }
    if ($institution) {
        $data->institution = $institution;
    } else if ($group) {
        require_once(get_config('libroot') . 'group.php');
        if (!$parentfolder) {
            $role = group_user_access($group);
            if (!$role) {
                return array('error' => true, 'message' => get_string('usernotingroup', 'mahara'));
            }
            // Use default grouptype artefact permissions to check if the
            // user can create a folder in the group's root directory
            $permissions = group_get_default_artefact_permissions($group);
            if (!$permissions[$role]->edit) {
                return array('error' => true, 'message' => get_string('cannoteditfolder', 'artefact.file'));
            }
        }
        $data->group = $group;
    } else {
        $data->owner = $USER->get('id');
    }

    if ($oldid = ArtefactTypeFileBase::file_exists($data->title, $data->owner, $parentfolder, $institution, $group)) {
        return array('error' => true, 'message' => get_string('fileexists', 'artefact.file'));
    }

    $f = new ArtefactTypeFolder(0, $data);
    $f->set('dirty', true);
    $f->commit();

    return array(
        'error'     => false,
        'message'   => get_string('foldercreated', 'artefact.file'),
        'highlight' => $f->get('id'),
        'newlist'   => pieform_element_filebrowser_build_filelist($element, $parentfolder, $f->get('id')),
    );
}

function pieform_element_filebrowser_update($element, $values) {
    global $USER;
    if (empty($values['collide'])) {
        $values['collide'] = 'fail';
    }

    $artefact = artefact_instance_from_id($values['artefact']);
    if (!$USER->can_edit_artefact($artefact)) {
        return array('error' => true, 'message' => get_string('noeditpermission', 'mahara'));
    }

    if ($existingid = ArtefactTypeFileBase::file_exists($values['title'], $artefact->get('owner'), $values['folder'],
                                                        $artefact->get('institution'), $artefact->get('group'))) {
        if ($existingid != $values['artefact']) {
            if ($values['collide'] == 'replace') {
                log_debug('deleting ' . $existingid);
                $copy = artefact_instance_from_id($existingid);
                $copy->delete();
            }
            else {
                return array('error' => true, 'message' => get_string('fileexists', 'artefact.file'));
            }
        }
    }

    $artefact->set('title', $values['title']);
    $artefact->set('description', $values['description']);
    $artefact->set('tags', preg_split("/\s*,\s*/", trim($values['tags'])));
    if ($values['group'] && $values['permissions']) {
        $artefact->set('rolepermissions', $values['permissions']);
    }
    $artefact->commit();

    return array(
        'error' => false,
        'message' => get_string('changessaved', 'artefact.file'),
        'newlist' => pieform_element_filebrowser_build_filelist($element, $artefact->get('parent')),
    );
}

function pieform_element_filebrowser_delete($element, $values) {
    global $USER;
    $artefact = artefact_instance_from_id($values['artefact']);
    if (!$USER->can_edit_artefact($artefact)) {
        return array('error' => true, get_string('nodeletepermission', 'mahara'));
    }
    $parentfolder = $artefact->get('parent');
    $artefact->delete();
    return array(
        'error' => false, 
        'message' => get_string('filethingdeleted', 'artefact.file', 
                                get_string($artefact->get('artefacttype'), 'artefact.file')),
        'quotaused' => $USER->get('quotaused'),
        'quota' => $USER->get('quota'),
        'newlist' => pieform_element_filebrowser_build_filelist($element, $parentfolder),
    );
}

function pieform_element_filebrowser_move($element, $values) {
    global $USER;
    $artefactid  = $values['artefact'];    // Artefact being moved
    $newparentid = $values['newparent'];   // Folder to move it to

    $artefact = artefact_instance_from_id($artefactid);

    if (!$USER->can_edit_artefact($artefact)) {
        return array('error' => true, 'message' => get_string('movefailednotowner', 'artefact.file'));
    }
    if (!in_array($artefact->get('artefacttype'), PluginArtefactFile::get_artefact_types())) {
        return array('error' => true, 'message' => get_string('movefailednotfileartefact', 'artefact.file'));
    }

    if ($newparentid > 0) {
        if ($newparentid == $artefactid) {
            return array('error' => true, 'message' => get_string('movefaileddestinationinartefact', 'artefact.file'));
        }
        if ($newparentid == $artefact->get('parent')) {
            return array('error' => false, 'message' => get_string('filealreadyindestination', 'artefact.file'));
        }
        $newparent = artefact_instance_from_id($newparentid);
        if (!$USER->can_edit_artefact($newparent)) {
            return array('error' => true, 'message' => get_string('movefailednotowner', 'artefact.file'));
        }
        $group = $artefact->get('group');
        if ($group && $group !== $newparent->get('group')) {
            return array('error' => true, 'message' => get_string('movefailednotowner', 'artefact.file'));
        }
        if ($newparent->get('artefacttype') != 'folder') {
            return array('error' => true, 'message' => get_string('movefaileddestinationnotfolder', 'artefact.file'));
        }
        $nextparentid = $newparent->get('parent');
        while (!empty($nextparentid)) {
            if ($nextparentid != $artefactid) {
                $ancestor = artefact_instance_from_id($nextparentid);
                $nextparentid = $ancestor->get('parent');
            } else {
                return array('error' => true, 'message' => get_string('movefaileddestinationinartefact', 'artefact.file'));
            }
        }
    } else { // $newparentid === 0
        if ($artefact->get('parent') == null) {
            return array('error' => false, 'message' => get_string('filealreadyindestination', 'artefact.file'));
        }
        $group = $artefact->get('group');
        if ($group) {
            // Use default grouptype artefact permissions to check if the
            // user can move a file to the group's root directory
            require_once(get_config('libroot') . 'group.php');
            $permissions = group_get_default_artefact_permissions($group);
            if (!$permissions[group_user_access($group)]->edit) {
                return array('error' => true, 'message' => get_string('movefailednotowner', 'artefact.file'));
            }
        }
        $newparentid = null;
    }

    if ($artefact->move($newparentid)) {
        return array(
            'error' => false,
            'newlist' => pieform_element_filebrowser_build_filelist($element, $values['folder']),
        );
    }
    return array('error' => true, 'message' => get_string('movefailed', 'artefact.file'));
}

function pieform_element_filebrowser_changefolder($element, $values) {
    return array(
        'error' => false, 
        'newlist' => pieform_element_filebrowser_build_filelist($element, $values['folder']),
        'newpath' => pieform_element_filebrowser_build_path($element, $values['folder']),
    );
}

/* function pieform_element_filebrowser_views_js(Pieform $form, $element) {
    $prefix = $form->get_name() . '_' . $element['name'];
    $parentfolder = json_encode($element['parentfolder']);
    log_debug($prefix);
    $js = "var {$prefix} = new Uploader('{$prefix}', {$parentfolder}); {$prefix}.init();";
    $js .= "{$prefix}.uploadscript = '" . get_config('wwwroot') . "artefact/file/upload2.php';";
    $js .= "window.{$prefix} = {$prefix};";
    if (get_config('uploadagreement')) {
        $js .= <<<JAVASCRIPT
connect('{$prefix}_openbutton', 'onclick', function () {
    addElementClass('{$prefix}_openbutton', 'hidden');
    removeElementClass('{$prefix}_elements', 'hidden');
});
JAVASCRIPT;
    }
    log_debug($js);
    return $js;
}*/


/**
 * When the element exists in a form that's present when the page is
 * first generated the following function gets called and the js file
 * below will be inserted into the head data.  Unfortunately, when
 * this element is present in a form that gets called in an ajax
 * request (currently on the view layout page), the .js file is not
 * loaded and so it's added explicitly to the smarty() call.
 */
function pieform_element_filebrowser_get_headdata($element) {
    $headdata = array('<script type="text/javascript" src="' . get_config('wwwroot') . 'artefact/file/js/filebrowser.js"></script>');

    $strings = array('uploadingfiletofolder', 'editfile', 'editfolder', 'filewithnameexists', 'foldernamerequired', 'namefieldisrequired');
    if ($element['config']['uploadagreement']) {
        $strings[] = 'youmustagreetothecopyrightnotice';
    }
    $jsstrings = '';
    foreach ($strings as $s) {
        $jsstrings .= "strings.$s=" . json_encode(get_raw_string($s, 'artefact.file')) . ';';
    }
    $headdata[] = '<script type="text/javascript">' . $jsstrings . '</script>';
    return $headdata;
}

function pieform_element_filebrowser_set_attributes($element) {/*{{{*/
    $element['needsmultipart'] = true;
    return $element;
}/*}}}*/

?>
