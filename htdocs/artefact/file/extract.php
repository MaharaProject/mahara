<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'content/files');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

safe_require('artefact', 'file');

$fileid = param_integer('file');
// @todo provide upload form when fileid not set.
$file = artefact_instance_from_id($fileid);

$smartyconfig = array(
    'sideblocks' => array(
        array(
            'name'   => ($file->get('group') ? 'groupquota' : 'quota'),
            'weight' => -10,
            'data'   => array(),
        ),
    ),
);

if ($group = $file->get('group')) {
    require_once(get_config('libroot') . 'group.php');
    define('GROUP', $group);
    $group = group_current_group();
    define('TITLE', $group->name);
}
else {
    define('TITLE', get_string('Decompress', 'artefact.file'));
}

if (!($file instanceof ArtefactTypeArchive)) {
    throw new NotFoundException();
}

if (!$USER->can_edit_artefact($file)) {
    throw new AccessDeniedException();
}

if ($file->get('locked')) {
    throw new AccessDeniedException(get_string('cannotextractfilesubmitted', 'artefact.file'));
}

$folderid = $file->get('parent');
if (!empty($folderid)) {
    $folder = artefact_instance_from_id($folderid);
    if (($folder->get('artefacttype') == 'folder') && $folder->get('locked')) {
        throw new AccessDeniedException(get_string('cannotextractfileinfoldersubmitted', 'artefact.file'));
    }
}

// Read the archive information, throw an ArchiveException if error
$zipinfo = $file->read_archive();

if ($zipinfo) {
    $quotaallowed = false;
    if ($file->get('owner')) {
        $quotaallowed = $USER->quota_allowed($zipinfo->totalsize);
    }
    else if ($file->get('group')) {
        $quotaallowed = group_quota_allowed($file->get('group'), $zipinfo->totalsize);
    }
    else {
        // no institution quotas yet
        $quotaallowed = true;
    }
    $message = $quotaerror = false;
    if ($quotaallowed) {
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
                    'class' => 'btn btn-primary',
                    'value' => array(get_string('Decompress', 'artefact.file'), get_string('cancel')),
                    'goto' => $goto,
                )
            ),
        ));
    }
    else {
        $form = '';
        $quotaerror = '<div class="error alert alert-danger">' . get_string('insufficientquotaforunzip', 'artefact.file') . "</div>";
    }
}

$smarty = smarty(array(), array(), array(), $smartyconfig);
$smarty->assign('file', $file);
$smarty->assign('zipinfo', $zipinfo);
$smarty->assign('message', $message);
$smarty->assign('quotaerror', $quotaerror);
$smarty->assign('form', $form);
$smarty->display('artefact:file:extract.tpl');

function files_page($file) {
    $url = get_config('wwwroot') . 'artefact/file/';
    if ($owner = $file->get('owner')) {
        $url .= 'index.php';
    }
    else if ($group = $file->get('group')) {
        $url .= 'groupfiles.php?group=' . $group;
    }
    else if ($institution = $file->get('institution')) {
        if ($institution == 'mahara') {
            $url .= 'sitefiles.php';
        }
        else {
            $url .= 'institutionfiles.php?institution=' . $institution;
        }
    }
    else {
        $url .= 'index.php';
    }
    return $url;
}

function unzip_artefact_submit(Pieform $form, $values) {
    global $file, $SESSION;

    $zipinfo = $file->read_archive();

    $from = files_page($file);

    if (count($zipinfo->names) > 10) {
        $SESSION->set('unzip', array('file' => $file->get('id'),
                                     'from' => $from,
                                     'artefacts' => count($zipinfo->names),
                                     'zipinfo' => $zipinfo
                                    )
                     );
        $smarty = smarty();
        $smarty->display('artefact:file:extract-progress.tpl');
        exit;
    }

    $status = $file->extract();

    $message = get_string('createdtwothings', 'artefact.file',
        get_string('nfolders', 'artefact.file', $status['folderscreated']),
        get_string('nfiles', 'artefact.file', $status['filescreated'])
    );
    $SESSION->add_ok_msg($message);
    $redirect = $from . (strpos($from, '?') === false ? '?' : '&') . 'folder=' . $status['basefolderid'];
    redirect($redirect);
}
