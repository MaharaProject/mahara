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

if (!get_config_plugin('artefact', 'file', 'folderdownloadzip')) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$folderid = param_integer('folder', 0);
$viewid = param_integer('view', 0);

/*
* Function to check if the specified artefact should be downloadable
* (ie. whether it or one of its parents is in the view and whether the
* current user can view the view)
*/
function can_download_artefact($artefact) {
    global $viewid;

    if (artefact_in_view($artefact->get('id'), $viewid)) {
        return can_view_view($viewid);
    }

    $parent = $artefact->get('parent');
    while ($parent !== null) {
        $parentobj = artefact_instance_from_id($parent);
        $parent = $parentobj->get('parent');
        if (artefact_in_view($parentobj->get('id'), $viewid)) {
            return can_view_view($viewid);
        }
    }
    return false;
}

/*
* Function to clean up zip file created by this script
* from the temp directory in the dataroot.
*/
function zip_clean_temp_dir() {
    global $USER;

    $temp_path = get_config('dataroot').'temp/';
    $regex = '#(^directory-([0-9]+)-([A-Za-z0-9\-_]+))-([0-9]+)\.zip#';
    $zipfiles = glob($temp_path.'*.zip');
    $zips = array();

    // Create an array of zip files that have been created by this script for the current user.
    foreach ($zipfiles as $zipfile) {
        $zip = str_replace($temp_path, '', $zipfile);
        if (preg_match($regex, $zip, $matches)) {
            if ((int) $matches[2] == $USER->get('id')) {
                $filename = $matches[1];

                if (!isset($zips[$filename])) {
                    $zips[$filename] = array();
                    $zips[$filename]['count'] = 0;
                }

                $zips[$filename][] = $zipfile;
                $zips[$filename]['count']++;
                $zips[$filename]['timecreated'] = (int) $matches[4];
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
* Function to recursively add directories and files
* to the zip file.
*/
function zip_process_directory(&$zip, $folderid, $path) {
    global $USER;
    $folderinfo = get_record('artefact', 'id', $folderid);

    if (empty($folderinfo)) {
        throw new NotFoundException(get_string('folderdownloadnofolderfound', 'artefact.file', $folderid));
    }

    $folder = artefact_instance_from_id($folderinfo->id);
    $foldercontent = $folder->folder_contents();

    $zip->addEmptyDir(rtrim($path, '/'));

    if (!empty($foldercontent)) {
        $folders = array();
        foreach ($foldercontent as $content) {
            $item = artefact_instance_from_id($content->id);
            if ($content->artefacttype === 'folder') {
                if (!isset($folders[$content->id]) && $content->title != get_string('parentfolder', 'artefact.file')) {
                    $folders[$content->id] = $content;
                }
            }
            else {
                if (can_download_artefact($item)) {
                    $zip->addFile($item->get_path(), $path.$item->download_title());
                }
            }
        }

        foreach ($folders as $folder) {
            $dir = artefact_instance_from_id($folder->id);
            if (can_download_artefact($dir)) {
                zip_process_directory($zip, $folder->id, $path.$folder->title.'/');
            }
        }
    }
}

$forcedl = param_boolean('download');

$options = array();
if ($forcedl) {
    $options['forcedownload'] = true;
}
else {
    $options['downloadurl'] = get_config('wwwroot')
        . substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], 'artefact/file/downloadfolder.php'))
        . '&download=1';
}

// Clean up the temp directory before creating anymore zip files.
zip_clean_temp_dir();

$folderinfo = get_record('artefact', 'id', $folderid);

if (empty($folderinfo)) {
    throw new NotFoundException();
}

if (function_exists('zip_open')) {
    $folder = artefact_instance_from_id($folderinfo->id);

    if (can_download_artefact($folder)) {
        $zip = new ZipArchive();

        $name = preg_replace('#\s+#', '_', strtolower($folderinfo->title));
        $name = preg_replace('#[^\pL0-9_\-]+#', '', $name);
        if ($name != '') {
            $name = '-' . $name;
        }
        $name = get_string('zipfilenameprefix', 'artefact.file') . $name;

        $filename = 'directory-'.$USER->get('id').'-'.$name.'-'.time().'.zip';
        $filepath = get_config('dataroot').'temp/'.$filename;

        if ($zip->open($filepath, ZIPARCHIVE::CREATE) !== true) {
            throw new NotFoundException();
        }

        zip_process_directory($zip, $folderid, $folderinfo->title.'/');

        $zip->close();

        $downloadname = $name . '.zip';
        serve_file($filepath, $downloadname, 'application/zip', $options);
    }
    else {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
}
else {
    throw new SystemException(get_string('phpzipneeded', 'artefact.file'));
}
