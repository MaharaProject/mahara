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
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'file');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

safe_require('artefact', 'file');

$fileid = param_integer('file');
// @todo provide upload form when fileid not set.
$file = artefact_instance_from_id($fileid);

$extra = array();
if ($institution = $file->get('institution')) {
    if ($institution == 'mahara') {
        define('MENUITEM', 'configsite/sitefiles');
        define('SECTION_PAGE', 'sitefiles');
        define('TITLE', get_string('Decompress', 'artefact.file'));
    }
    else {
        define('MENUITEM', 'manageinstitutions/institutionfiles');
        define('SECTION_PAGE', 'institutionfiles');
        define('TITLE', get_string('Decompress', 'artefact.file'));
    }
}
else if ($group = $file->get('group')) {
    require_once(get_config('libroot') . 'group.php');
    define('MENUITEM', 'engage/index');
    define('MENUITEM_SUBPAGE', 'files');
    define('SECTION_PAGE', 'groupfiles');
    define('GROUP', $group);
    $group = group_current_group();
    $extra = array('sideblocks' => array(quota_sideblock(true)));
    define('TITLE', $group->name);
}
else {
    define('MENUITEM', 'create/files');
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
$message = $error = false;
if ($zipinfo) {
    $badzipfile = false;
    if (get_config('viruschecking')) {
        // Need to double-check the contents of zip file for viruses in case file
        // was uploaded before virus check turned on.
        require_once('uploadmanager.php');
        $path = $file->get_path();
        if ($errormsg = mahara_clam_scan_file($path)) {
            $badzipfile = true;
            $file->delete();
        }
    }

    $quotaallowed = false;
    if (!$badzipfile) {
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
    }

    $goto = files_page($file);
    if ($parent = $file->get('parent')) {
        $goto .= (strpos($goto, '?') === false ? '?' : '&') . 'folder=' . $parent;
    }

    if ($quotaallowed && !$badzipfile) {
        $name = $file->unzip_directory_name();
        $message = get_string('fileswillbeextractedintofolder', 'artefact.file', $name['fullname']);

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
    else if ($badzipfile) {
        $form = '<a class="btn btn-primary" href="' . $goto . '">' . get_string('back') . '</a>';
        $error = get_string('viruszipfile', 'artefact.file');
    }
    else {
        $form = '<a class="btn btn-primary" href="' . $goto . '">' . get_string('back') . '</a>';
        $error = get_string('insufficientquotaforunzip', 'artefact.file');
    }
}

$smarty = smarty(array(), array(), array(), $extra);
$smarty->assign('file', $file);
$smarty->assign('zipinfo', $zipinfo);
$smarty->assign('message', $message);
$smarty->assign('error', $error);
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
