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
define('MENUITEM', 'content/files');
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

    try {
        $zipinfo = $file->read_archive();
    }
    catch (SystemException $e) {
        $message = get_string('invalidarchive', 'artefact.file');
    }

    if ($zipinfo) {
        if (!$file->get('owner') || $USER->quota_allowed($zipinfo->totalsize)) {
            $name = $file->unzip_directory_name();
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
    }

    $smarty = smarty(array(), array(), array(), $smartyconfig);
    $smarty->assign('file', $file);
    $smarty->assign('zipinfo', $zipinfo);
    $smarty->assign('message', $message);
    $smarty->assign('form', $form);
    $smarty->assign('PAGEHEADING', TITLE);
    $smarty->display('artefact:file:extract.tpl');
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
    global $file, $SESSION;

    $zipinfo = $file->read_archive();

    $from = files_page($file);

    if (count($zipinfo->names) > 10) {
        $SESSION->set('unzip', array('file' => $file->get('id'), 'from' => $from, 'artefacts' => count($zipinfo->names), 'zipinfo' => $zipinfo));
        $smarty = smarty();
        $smarty->display('artefact:file:extract-progress.tpl');
        exit;
    }

    $status = $file->extract();

    $SESSION->add_ok_msg(get_string('extractfilessuccess', 'artefact.file', $status['folderscreated'], $status['filescreated']));
    $redirect = $from . (strpos($from, '?') === false ? '?' : '&') . 'folder=' . $status['basefolderid'];
    redirect($redirect);
}
