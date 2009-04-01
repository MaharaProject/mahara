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

    $group       = $form->get_property('group');
    $institution = $form->get_property('institution');
    $userid      = ($group || $institution) ? null : $USER->get('id');

    if ($group) {
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


    $folder = $element['folder'];
    $path = pieform_element_filebrowser_get_path($folder);
    $smarty->assign('folder', $folder);
    $smarty->assign('foldername', $path[0]->title);
    $smarty->assign('path', array_reverse($path));
    $smarty->assign('highlight', $element['highlight'][0]);
    $smarty->assign('edit', !empty($element['edit']) ? $element['edit'] : -1);
    if (isset($element['browse'])) {
        $smarty->assign('browse', (int) $element['browse']);
    }
    $config = array_map('intval', $element['config']);
    $smarty->assign('config', $config);
    if ($config['select']) {
        $selected = $element['selectlistcallback']();
        $smarty->assign('selectedlist', $selected);
        $selectedliststr = json_encode($selected);
    }
    if ($config['uploadagreement']) {
        if (get_config_plugin('artefact', 'file', 'usedefaultagreement')) {
            $smarty->assign('agreementtext', get_string('uploadcopyrightdefaultcontent', 'install'));
        }
        else {
            $smarty->assign('agreementtext', get_field('site_content', 'content', 'name', 'uploadcopyright'));
        }
    }
    $filedata = ArtefactTypeFileBase::get_my_files_data($folder, $userid, $group, $institution);
    $smarty->assign('filelist', $filedata);

    $configstr = json_encode($config);
    $fileliststr = json_encode($filedata);

    $formid = $form->get_name();
    $prefix = $formid . '_' . $element['name'];

    $smarty->assign('prefix', $prefix);

    $initjs = "var {$prefix} = new FileBrowser('{$prefix}', {$folder}, {$configstr}, config);
{$prefix}.formname = '{$formid}';
{$prefix}.filedata = {$fileliststr};";
    if ($config['select']) {
        $initjs .= "{$prefix}.selecteddata = {$selectedliststr};";
    }
    $initjs .= "addLoadEvent({$prefix}.init);";

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

function pieform_element_filebrowser_build_path($form, $element, $folder) {
    if (!$form->submitted_by_js()) {
        return;
    }

    $path = pieform_element_filebrowser_get_path($folder);
    $foldername = $path[0]->title;

    $smarty = smarty_core();
    $smarty->assign('path', array_reverse($path));
    return array('html' => $smarty->fetch('artefact:file:form/folderpath.tpl'), 'foldername' => $foldername);
}

function pieform_element_filebrowser_build_filelist($form, $element, $folder, $highlight=null) {
    if (!$form->submitted_by_js()) {
        // We're going to rebuild the page from scratch anyway.
        return;
    }

    global $USER;

    $group = $form->get_property('group');
    $institution = $form->get_property('institution');
    $userid = ($group || $institution) ? null : $USER->get('id');

    $smarty = smarty_core();
    $smarty->assign('edit', -1);
    $smarty->assign('highlight', $highlight);
    $smarty->assign('editable', (int) $element['config']['edit']);
    $smarty->assign('selectable', (int) $element['config']['select']);
    $filedata = ArtefactTypeFileBase::get_my_files_data($folder, $userid, $group, $institution);
    $smarty->assign('filelist', $filedata);

    return array(
        'data' => $filedata,
        'html' => $smarty->fetch('artefact:file:form/filelist.tpl'),
    );
}


function pieform_element_filebrowser_get_value(Pieform $form, $element) {
    // Check if the user tried to make a change to the filebrowser element
    if ($form->is_submitted()) {

        if (isset($_POST['folder'])) {
            $folder = (int) $_POST['folder'];
        }

        $result = pieform_element_filebrowser_doupdate($form, $element, $folder);

        if (is_array($result)) {
            // We did something.  If js, replace the filebrowser now and
            // don't continue form submission.
            if ($form->submitted_by_js()) {
                $replacehtml = false; // Don't replace the entire form when replying with json data.
                $form->json_reply(empty($result['error']) ? PIEFORM_OK : PIEFORM_ERR, $result, $replacehtml);
            }
            // Not js. Remember this change and submit it with the
            // rest of the form.
            return $result;
        }

        $result = array('folder' => $folder);

        if (!empty($_POST['select']) && is_array($_POST['select']) && is_callable($element['selectcallback'])) {
            $keys = array_keys($_POST['select']);
            // try
            $element['selectcallback']((int) $keys[0]);
            $result['message'] = get_string('fileadded', 'artefact.file');
            $result['browse'] = 1;
        }
        else if (!empty($_POST['unselect']) && is_array($_POST['unselect']) && is_callable($element['unselectcallback'])) {
            $keys = array_keys($_POST['unselect']);
            // try
            $element['unselectcallback']((int) $keys[0]);
            $result['message'] = get_string('fileremoved', 'artefact.file');
        }
        else if (!empty($_POST['edit']) && is_array($_POST['edit'])) {
            // Non-js update that needs to be passed back as a parameter
            $keys = array_keys($_POST['edit']);
            $result['edit'] = (int) $keys[0];
        }
        else if (!empty($_POST['browse'])) {
            $result['browse'] = 1;
        }
        else if (!empty($_POST['selected']) && is_array($_POST['selected'])) {
            // When files are being selected, this element has a real value
            $result['selected'] = array_keys($_POST['selected']);
        }

        return $result;
    }
}


function pieform_element_filebrowser_doupdate(Pieform $form, $element, $folder) {
    $result = null;

    if (!empty($_POST['delete']) && is_array($_POST['delete'])) {
        $keys = array_keys($_POST['delete']);
        $result = pieform_element_filebrowser_delete($form, $element, (int) ($keys[0]));
    }
    else if (!empty($_POST['update']) && is_array($_POST['update'])) {
        if (!isset($_POST['edit_title']) || !strlen($_POST['edit_title'])) {
            return array(
                'error'   => true,
                'message' => get_string('filenamefieldisrequired', 'artefact.file')
            );
        }
        $keys = array_keys($_POST['update']);
        $data = array(
            'artefact'    => (int) ($keys[0]),
            'title'       => $_POST['edit_title'],
            'description' => $_POST['edit_description'],
            'tags'        => $_POST['edit_tags'],
            'folder'      => $folder,
        );
        if ($form->get_property('group')) {
            $data['permissions']  = array('admin' => (object) array('view' => true, 'edit' => true, 'republish' => true));
            foreach ($_POST as $k => $v) {
                if (preg_match('/^permission:([a-z]+):([a-z]+)$/', $k, $m)) {
                    $data['permissions'][$m[1]]->{$m[2]} = (bool) $v;
                }
            }
        }
        $result = pieform_element_filebrowser_update($form, $element, $data);
    }
    else if (!empty($_POST['move'])) {
        $result = pieform_element_filebrowser_move($form, $element, array(
            'artefact'  => (int) $_POST['move'],
            'newparent' => (int) $_POST['moveto'],
            'folder'    => $folder,
        ));
    }
    else if (!empty($_POST['createfolder'])) {
        if (!isset($_POST['createfolder_name']) || !strlen($_POST['createfolder_name'])) {
            return array(
                'error'   => true,
                'message' => get_string('foldernamerequired', 'artefact.file'),
            );
        }
        $result = pieform_element_filebrowser_createfolder($form, $element, array(
            'title'  => $_POST['createfolder_name'],
            'folder' => $folder,
        ));
    }
    else if (!empty($_POST['upload'])) {
        if (!isset($_FILES['userfile']['name'])) {
            return array(
                'error'   => true,
                'message' => get_string('filenamefieldisrequired', 'artefact.file'),
                'browse'  => 1,
            );
        }
        else if ($element['config']['uploadagreement'] && empty($_POST['notice'])) {
            return array(
                'error'   => true,
                'message' => get_string('youmustagreetothecopyrightnotice', 'artefact.file'),
                'browse'  => 1,
            );
        }
        $result = pieform_element_filebrowser_upload($form, $element, array(
            'userfile'         => $_FILES['userfile'],
            'uploadnumber'     => (int) $_POST['uploadnumber'],
            'uploadfolder'     => $folder ? $folder : null,
            'uploadfoldername' => $_POST['foldername'],
        ));
        // If it's a non-js upload, automatically select the newly uploaded file.
        $result['browse'] = 1;
        if (!$form->submitted_by_js() && !$result['error'] && is_callable($element['selectcallback'])) {
            $element['selectcallback']($result['highlight']);
        }
    }
    else if (!empty($_POST['changefolder']) && is_array($_POST['changefolder'])) {
        $keys = array_keys($_POST['changefolder']);
        $newfolder = (int) $keys[0];
        $result = pieform_element_filebrowser_changefolder($form, $element, $newfolder);
        $result['browse'] = 1;
        $folder = $newfolder;
    }

    if (is_array($result)) {
        $result['folder'] = $folder;
    }

    return $result;
}


function pieform_element_filebrowser_upload(Pieform $form, $element, $data) {
    global $USER;

    $parentfolder     = $data['uploadfolder'] ? (int) $data['uploadfolder'] : null;
    $parentfoldername = $data['uploadfoldername'];
    $institution      = $form->get_property('institution');
    $group            = $form->get_property('group');

    $result = array('error' => false, 'uploadnumber' => (int) $data['uploadnumber']);

    $data             = new StdClass;
    $data->parent     = $parentfolder;
    $data->owner      = null;

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
    $result['uploaded'] = true;
    $result['newlist'] = pieform_element_filebrowser_build_filelist($form, $element, $parentfolder, $newid);
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

function pieform_element_filebrowser_createfolder(Pieform $form, $element, $data) {
    global $USER;

    $parentfolder     = $data['folder'] ? (int) $data['folder'] : null;
    $institution      = $form->get_property('institution');
    $group            = $form->get_property('group');

    $result = array();

    $data = (object) array(
        'parent'      => $parentfolder,
        'owner'       => null,
        'title'       => $data['title'],
    );

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
        'newlist'   => pieform_element_filebrowser_build_filelist($form, $element, $parentfolder, $f->get('id')),
    );
}

function pieform_element_filebrowser_update(Pieform $form, $element, $data) {
    global $USER;
    $collide = !empty($data['collide']) ? $data['collide'] : 'fail';

    $artefact = artefact_instance_from_id($data['artefact']);
    if (!$USER->can_edit_artefact($artefact)) {
        return array('error' => true, 'message' => get_string('noeditpermission', 'mahara'));
    }

    if ($existingid = ArtefactTypeFileBase::file_exists($data['title'], $artefact->get('owner'), $data['folder'],
                                                        $artefact->get('institution'), $artefact->get('group'))) {
        if ($existingid != $data['artefact']) {
            if ($collide == 'replace') {
                log_debug('deleting ' . $existingid);
                $copy = artefact_instance_from_id($existingid);
                $copy->delete();
            }
            else {
                return array('error' => true, 'message' => get_string('fileexists', 'artefact.file'));
            }
        }
    }

    $artefact->set('title', $data['title']);
    $artefact->set('description', $data['description']);
    $artefact->set('tags', preg_split("/\s*,\s*/", trim($data['tags'])));
    if ($form->get_property('group') && $data['permissions']) {
        $artefact->set('rolepermissions', $data['permissions']);
    }
    $artefact->commit();

    return array(
        'error' => false,
        'message' => get_string('changessaved', 'artefact.file'),
        'newlist' => pieform_element_filebrowser_build_filelist($form, $element, $artefact->get('parent')),
    );
}

function pieform_element_filebrowser_delete(Pieform $form, $element, $artefact) {
    global $USER;
    $artefact = artefact_instance_from_id($artefact);
    if (!$USER->can_edit_artefact($artefact)) {
        return array('error' => true, get_string('nodeletepermission', 'mahara'));
    }
    $parentfolder = $artefact->get('parent');
    $artefact->delete();
    return array(
        'error' => false, 
        'deleted' => true, 
        'message' => get_string('filethingdeleted', 'artefact.file', 
                                get_string($artefact->get('artefacttype'), 'artefact.file')),
        'quotaused' => $USER->get('quotaused'),
        'quota' => $USER->get('quota'),
        'newlist' => pieform_element_filebrowser_build_filelist($form, $element, $parentfolder),
    );
}

function pieform_element_filebrowser_move(Pieform $form, $element, $data) {
    global $USER;
    $artefactid  = $data['artefact'];    // Artefact being moved
    $newparentid = $data['newparent'];   // Folder to move it to

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
            'newlist' => pieform_element_filebrowser_build_filelist($form, $element, $data['folder']),
        );
    }
    return array('error' => true, 'message' => get_string('movefailed', 'artefact.file'));
}

function pieform_element_filebrowser_changefolder(Pieform $form, $element, $folder) {
    return array(
        'error'         => false, 
        'changedfolder' => true,
        'folder'        => $folder,
        'newlist'       => pieform_element_filebrowser_build_filelist($form, $element, $folder),
        'newpath'       => pieform_element_filebrowser_build_path($form, $element, $folder),
    );
}

/* function pieform_element_filebrowser_views_js(Pieform $form, $element) {
    $prefix = $form->get_name() . '_' . $element['name'];
    $parentfolder = json_encode($element['parentfolder']);
    log_debug($prefix);
    $js = "var {$prefix} = new Uploader('{$prefix}', {$parentfolder}); {$prefix}.init();";
    $js .= "{$prefix}.uploadscript = '" . get_config('wwwroot') . "artefact/file/upload2.php';";
    $js .= "window.{$prefix} = {$prefix};";
    if (get_config_plugin('artefact', 'file', 'uploadagreement')) {
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

    $strings = array();
    if ($element['config']['edit']) {
        $strings = array(
            'artefact.file' => array(
                'editfile',
                'editfolder',
                'filewithnameexists',
                'namefieldisrequired',
                'detachfilewarning',
            ),
        );
    }
    if ($element['config']['upload']) {
        $strings['artefact.file'][] = 'uploadingfiletofolder';
        if ($element['config']['uploadagreement']) {
            $strings['artefact.file'][] = 'youmustagreetothecopyrightnotice';
        }
    }
    if ($element['config']['createfolder']) {
        $strings['artefact.file'][] = 'foldernamerequired';
    }
    if ($element['config']['select']) {
        $strings['mahara'][] = 'remove';
    }
    $jsstrings = '';
    foreach ($strings as $section => $sectionstrings) {
        foreach ($sectionstrings as $s) {
            $jsstrings .= "strings.$s=" . json_encode(get_raw_string($s, $section)) . ';';
        }
    }
    $headdata[] = '<script type="text/javascript">' . $jsstrings . '</script>';
    return $headdata;
}

function pieform_element_filebrowser_set_attributes($element) {/*{{{*/
    $element['needsmultipart'] = true;
    return $element;
}/*}}}*/

?>
