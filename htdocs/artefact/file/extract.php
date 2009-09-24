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
 * @subpackage artefact-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/files');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

safe_require('artefact', 'file');

$fileid = param_integer('file');
// @todo provide upload form when fileid not set.

$smartyconfig = array(
    'sideblocks' => array(
        array(
            'name'   => 'quota',
            'weight' => -10,
            'data'   => array(),
        ),
    ),
);

if ($fileid) {
    $file = artefact_instance_from_id($fileid);

    if ($group = $file->get('group')) {
        require_once(get_config('libroot') . 'group.php');
        define('GROUP', $group);
        $group = group_current_group();
        define('TITLE', $group->name);
    }
    else {
        define('TITLE', get_string('Unzip', 'artefact.file'));
    }

    if (!($file instanceof ArtefactTypeArchive)) {
        throw new NotFoundException();
    }

    if (!$USER->can_edit_artefact($file)) {
        throw new AccessDeniedException();
    }

    $zip = zip_open($file->get_path());
    if (!is_resource($zip)) {
        throw new NotFoundException();
    }
    $zipinfo = zip_file_info($zip);
    zip_close($zip);

    if (!$file->get('owner') || $USER->quota_allowed($zipinfo->totalsize)) {
        $name = get_unzip_directory_name($file);
        $message = get_string('fileswillbeextractedintofolder', 'artefact.file', $name['fullname']);

        $goto = files_page($file);
        if ($parent = $file->get('parent')) {
            $goto .= (strpos($goto, '?') === false ? '?' : '&') . 'folder=' . $parent;
        }

        $form = pieform(array(
            'name' => 'unzip_artefact',
            'elements' => array(
                'fileid' => array(
                    'type' => 'hidden',
                    'value' => $fileid,
                ),
                'submit' => array(
                    'type' => 'submitcancel',
                    'value' => array(get_string('Unzip', 'artefact.file'), get_string('cancel')),
                    'goto' => $goto,
                )
            ),
        ));
    }
    else {
        $message = get_string('insufficientquotaforunzip', 'artefact.file');
    }

    $smarty = smarty(array(), array(), array(), $smartyconfig);
    $smarty->assign('file', $file);
    $smarty->assign('zipinfo', $zipinfo);
    $smarty->assign('message', $message);
    $smarty->assign('form', $form);
    $smarty->assign('PAGEHEADING', hsc(TITLE));
    $smarty->display('artefact:file:extract.tpl');
}

function zip_file_info($zip) {
    $info = (object) array(
        'files'     => 0,
        'folders'   => 0,
        'totalsize' => 0,
        'names'     => array(),
    );
    while ($entry = zip_read($zip)) {
        $name = zip_entry_name($entry);
        $info->names[] = $name;
        if (substr($name, -1) == '/') {
            $info->folders++;
        }
        else {
            $info->files++;
            if ($size = zip_entry_filesize($entry)) {
                $info->totalsize += $size;
            }
        }
    }
    $info->displaysize = ArtefactTypeFile::short_size($info->totalsize);
    return $info;
}

function get_unzip_directory_name($file) {
    $folderdata = ArtefactTypeFileBase::artefactchooser_folder_data($file);
    $parent = $file->get('parent');
    $strpath = ArtefactTypeFileBase::get_full_path($parent, $folderdata->data);
    $extn = $file->get('oldextension');
    $name = $file->get('title');
    if (substr($name, -1-strlen($extn)) == '.' . $extn) {
        $name = substr($name, 0, strlen($name)-1-strlen($extn));
    }
    $name = ArtefactTypeFileBase::get_new_file_title($name, $parent, $file->get('owner'), $file->get('group'), $file->get('institution'));
    return array('basename' => $name, 'fullname' => $strpath . $name);
}

function files_page($file) {
    $url = get_config('wwwroot') . 'artefact/file/';
    if ($owner = $file->get('owner')) {
        $url .= 'index.php';
    }
    else if ($group = $file->get('group')) {
        $url .= 'groupfiles.php?group=' . $group;
    }
    if ($institution = $file->get('institution')) {
        if ($institution == 'mahara') {
            $url .= 'sitefiles.php';
        }
        else {
            $url .= 'institutionfiles.php?institution=' . $institution;
        }
    }
    return $url;
}

function unzip_artefact_submit(Pieform $form, $values) {
    global $file, $USER, $SESSION;

    $foldername = get_unzip_directory_name($file);
    $foldername = $foldername['basename'];

    $data = (object) array(
        'owner' => $file->get('owner'),
        'group' => $file->get('group'),
        'institution' => $file->get('institution'),
        'title' => $foldername,
        'description' => get_string('filesextractedfromziparchive', 'artefact.file'),
        'parent' => $file->get('parent'),
    );

    $user = $data->owner ? $USER : null;

    $basefolder = new ArtefactTypeFolder(0, $data);
    $basefolder->commit();
    $folders = array('.' => $basefolder->get('id'));
    $created = (object) array('folders' => 1, 'files' => 0);

    unset($data->description);

    $tempdir = get_config('dataroot') . 'artefact/file/temp';
    check_dir_exists($tempdir);

    $zip = zip_open($file->get_path());
    $tempfile = tempnam($tempdir, '');

    while ($entry = zip_read($zip)) {
        $name = zip_entry_name($entry);
        $folder = dirname($name);
        $data->title = basename($name);
        $data->parent = $folders[$folder];
        if (substr($name, -1) == '/') {
            $newfolder = new ArtefactTypeFolder(0, $data);
            $newfolder->commit();
            $created->folders++;
            $folderindex = ($folder == '.' ? '' : ($folder . '/')) . $data->title;
            $folders[$folderindex] = $newfolder->get('id');
        }
        else {
            $h = fopen($tempfile, 'w');
            $size = zip_entry_filesize($entry);
            $contents = zip_entry_read($entry, $size);
            fwrite($h, $contents);
            fclose($h);
            ArtefactTypeFile::save_file($tempfile, $data, $user, true);
            $created->files++;
        }
    }

    $SESSION->add_ok_msg(get_string('extractfilessuccess', 'artefact.file', $created->folders, $created->files));
    $redirect = files_page($file);
    $redirect .= (strpos($redirect, '?') === false ? '?' : '&') . 'folder=' . $basefolder->get('id');
    redirect($redirect);
}

?>
