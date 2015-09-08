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
define('PUBLIC', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');
require_once('file.php');

$folderid = param_integer('folder', 0);
$viewid = param_integer('view', 0);

$groupid = param_integer('group', 0);
$institution = param_alpha('institution', 0);

/*
* Function to check if the specified artefact should be downloadable
* (ie. whether it or one of its parents is in the view and whether the
* current user can view the view)
*/
function can_download_artefact($artefact) {
    global $USER, $viewid;

    if ($USER->can_view_artefact($artefact)) {
        return true;
    }
    else if (artefact_in_view($artefact, $viewid)) {
        return can_view_view($viewid);
    }

    $parent = $artefact->get('parent');
    while ($parent !== null) {
        $parentobj = artefact_instance_from_id($parent);
        $parent = $parentobj->get('parent');
        if (artefact_in_view($parentobj, $viewid)) {
            return can_view_view($viewid);
        }
    }
    return false;
}

/*
* Function to clean up a string so that it can be used as a filename
*/
function zip_filename_from($name) {
    $name = preg_replace('#\s+#', '_', strtolower($name));
    // \pL is used to match any letter in any alphabet (http://php.net/manual/en/regexp.reference.unicode.php)
    $name = preg_replace('#[^\pL0-9_\-]+#', '', $name);
    if ($name != '') {
        $name = '-' . $name;
    }
    return get_string('zipfilenameprefix', 'artefact.file') . $name . '.zip';
}

/*
* Function to clean up zip file created by this script
* from the temp directory in the dataroot.
*/
function zip_clean_temp_dir() {
    global $USER;

    $temp_path = get_config('dataroot').'temp/';
    $regex = '#' . '([0-9]+)-([0-9]+)-' . get_string('zipfilenameprefix', 'artefact.file') . '-([\pL0-9_\-]+)\.zip#';
    $zipfiles = glob($temp_path.'*.zip');
    $zips = array();

    // Create an array of zip files that have been created by this script for the current user.
    foreach ($zipfiles as $zipfile) {
        $zip = str_replace($temp_path, '', $zipfile);
        if (preg_match($regex, $zip, $matches)) {
            if ((int) $matches[1] == $USER->get('id')) {
                $filename = $matches[3];

                if (!isset($zips[$filename])) {
                    $zips[$filename] = array();
                    $zips[$filename]['count'] = 0;
                }

                $zips[$filename][] = $zipfile;
                $zips[$filename]['count']++;
                $zips[$filename]['timecreated'] = (int) $matches[2];
            }
        }
    }

    $current_time = time();

    foreach ($zips as $files) {
        $count = $files['count'];
        $created_time = $files['timecreated'];
        unset($files['count'], $files['timecreated']);

        for ($i=0; $i < $count; $i++) {
            $time_present = $current_time - $created_time;

            // Remove all zip files if they have been there for more than the specified time.
            if ($time_present >= get_config_plugin('artefact', 'file', 'folderdownloadkeepzipfor')) {
                unlink($files[$i]);
            }
            else {
                // Only keep 2 copies of the zip file.
                if ($count > 1) {
                    $remove = $count - 1;
                    if ($i < $remove) {
                        unlink($files[$i]);
                    }
                }
            }
        }
    }
}

/*
* Function to start the process of adding directories to the zip file
* @returns an array of all files in the directory and subdirectories
*/
function zip_process_directory(&$zip, $folderid, $path) {
    $folderinfo = get_record('artefact', 'id', $folderid);

    if (empty($folderinfo)) {
        throw new NotFoundException(get_string('folderdownloadnofolderfound', 'artefact.file', $folderid));
    }

    $folder = artefact_instance_from_id($folderinfo->id);
    $foldercontent = $folder->folder_contents();

    return zip_process_contents($zip, $foldercontent, $path);
}

/*
* Function to recursively add directories to the zip file
* @returns an array of all files in this and subdirectories
*/
function zip_process_contents(&$zip, $foldercontent, $path) {
    $files = array();

    $zip->addEmptyDir(rtrim($path, '/'));

    if ($foldercontent) {
        foreach ($foldercontent as $content) {
            $item = artefact_instance_from_id($content->id);
            if (!can_download_artefact($item)) {
                continue;
            }

            if ($content->artefacttype === 'folder') {
                $files = array_merge($files, zip_process_directory($zip, $content->id, $path . $content->title . '/'));
            }
            else {
                $files[] = array($item->get_path(), $path . $item->download_title());
            }
        }
    }

    return $files;
}

/*
* Function to write the given files to the zip file. This assumes all required directories have been created.
* Writes files in chunks since ZipArchive doesn't unlock files until it's closed - large numbers of files could
* exceed the maximum number of files allowed to be open at once (eg. ulimit on Linux)
*/
function zip_write_contents(&$zip, $filepath, $allfiles) {
    $chunks = array_chunk($allfiles, 500);
    foreach ($chunks as $chunk) {
        foreach ($chunk as $file) {
            $zip->addFile($file[0], $file[1]);
        }
        $zip->close();
        $zip->open($filepath);
    }
}

$embedded = param_boolean('embedded', null);

$options = array();
if (empty($embedded)) {
    $options['forcedownload'] = true;
}

// Clean up the temp directory before creating anymore zip files.
zip_clean_temp_dir();

// Home folder
if ($folderid === 0) {
    if (function_exists('zip_open')) {
        global $USER;
        $userid = $USER->get('id');

        $select = '
        SELECT a.id, a.artefacttype, a.title';
        $from = '
        FROM {artefact} a';

        $in = "('".join("','", PluginArtefactFile::get_artefact_types())."')";
        $where = "
        WHERE artefacttype IN $in";

        $phvals = array();

        if ($institution) {
            if ($institution == 'mahara' && !$USER->get('admin')) {
                // If non-admins are browsing site files, only let them see the public folder & its contents
                $publicfolder = ArtefactTypeFolder::admin_public_folder_id();
                $where .= '
                    AND (a.path = ? OR a.path LIKE ?)';
                $phvals = array("/$publicfolder", db_like_escape("/$publicfolder/") . '%');
            }
            $where .= '
            AND a.institution = ? AND a.owner IS NULL';
            $phvals[] = $institution;
        }
        else if ($groupid) {
            $select .= ',
                r.can_edit, r.can_view, r.can_republish, a.author';
            $from .= '
                LEFT OUTER JOIN (
                    SELECT ar.artefact, ar.can_edit, ar.can_view, ar.can_republish
                    FROM {artefact_access_role} ar
                    INNER JOIN {group_member} gm ON ar.role = gm.role
                    WHERE gm.group = ? AND gm.member = ?
                ) r ON r.artefact = a.id';
            $phvals[] = $groupid;
            $phvals[] = $USER->get('id');
            $where .= '
            AND a.group = ? AND a.owner IS NULL AND (r.can_view = 1 OR a.author = ?)';
            $phvals[] = $groupid;
            $phvals[] = $USER->get('id');
        }
        else {
            $where .= '
            AND a.institution IS NULL AND a.owner = ?';
            $phvals[] = $userid;
        }

        $where .= '
        AND a.parent IS NULL';
        $can_edit_parent = true;
        $can_view_parent = true;

        $contents = get_records_sql_assoc($select . $from . $where, $phvals);

        if (!empty($contents)) {
            $zip = new ZipArchive();

            if ($groupid) {
                $group = get_record_sql("SELECT g.name FROM {group} g WHERE g.id = ? AND g.deleted = 0", array($groupid));
                $downloadname = zip_filename_from($group->name);
            }
            else if ($institution) {
                $downloadname = zip_filename_from($institution);
            }
            else {
                $downloadname = zip_filename_from(get_string('home', 'artefact.file'));
            }
            $filename = $USER->get('id') . '-' . time() . '-' . $downloadname;
            $filepath = get_config('dataroot') . 'temp/' . $filename;

            if ($zip->open($filepath, ZIPARCHIVE::CREATE) !== true) {
                throw new NotFoundException();
            }

            $files = zip_process_contents($zip, $contents, '');
            zip_write_contents($zip, $filepath, $files);
            $zip->close();

            serve_file($filepath, $downloadname, 'application/zip', $options);
        }
        else {
            throw new NotFoundException();
        }
    }
    else {
        throw new SystemException(get_string('phpzipneeded', 'artefact.file'));
    }
}
else {
    $folderinfo = get_record('artefact', 'id', $folderid);

    if (empty($folderinfo)) {
        throw new NotFoundException();
    }

    if (function_exists('zip_open')) {
        $folder = artefact_instance_from_id($folderinfo->id);

        if (can_download_artefact($folder)) {
            $zip = new ZipArchive();

            $foldername = $folderinfo->title;

            $filename = 'directory-'.$USER->get('id').'-'.$foldername.'-'.time().'.zip';
            $filepath = get_config('dataroot').'temp/'.$filename;

            if ($zip->open($filepath, ZIPARCHIVE::CREATE) !== true) {
                throw new NotFoundException();
            }

            $files = zip_process_directory($zip, $folderid, $folderinfo->title.'/');
            zip_write_contents($zip, $filepath, $files);
            $zip->close();

            $downloadname = zip_filename_from($foldername);
            serve_file($filepath, $downloadname, 'application/zip', $options);
        }
        else {
            throw new AccessDeniedException(get_string('accessdenied', 'error'));
        }
    }
    else {
        throw new SystemException(get_string('phpzipneeded', 'artefact.file'));
    }
}
