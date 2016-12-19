<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('NOCHECKREQUIREDFIELDS', 1);
require('init.php');
require_once('file.php');
require_once('user.php');

$type = param_alpha('type');

switch ($type) {
    // A profile icon identified by user ID
    case 'profileicon':
        safe_require('artefact', 'file');
        $userid = param_integer('id');
        ArtefactTypeProfileIcon::download_thumbnail_for_user($userid);
        exit();
    // A profile icon identified by artefact ID
    case 'profileiconbyid':
        safe_require('artefact', 'file');
        $artefactid = param_integer('id');
        ArtefactTypeProfileIcon::download_thumbnail($artefactid, $type);
        exit();
    case 'logobyid':
        $filedata = get_record('artefact_file_files', 'artefact', param_integer('id'));
        if ($path = get_dataroot_image_path('artefact/file/profileicons', $filedata->fileid, get_imagesize_parameters())) {
            if ($filedata->filetype) {
                header('Content-type: ' . $filedata->filetype);
                if (!get_config('nocache')) {
                    $maxage = 604800;
                    header('Expires: '. gmdate('D, d M Y H:i:s', time() + $maxage) .' GMT');
                    header('Cache-Control: max-age=' . $maxage);
                    header('Pragma: public');
                }

                readfile_exit($path);
            }
        }

        // Nothing found, use the site logo.
        header('Content-type: ' . 'image/png');
        readfile_exit($THEME->get_path('images/site-logo.png'));

    case 'blocktype':
        $bt = param_alpha('bt'); // blocktype
        $ap = param_alpha('ap', null); // artefact plugin (optional)

        $basepath = 'blocktype/' . $bt;
        if (!empty($ap)) {
            $basepath = 'artefact/' . $ap . '/' . $basepath;
        }
        header('Content-type: image/png');
        if (!get_config('nocache')) {
            $maxage = 604800;
            header('Expires: '. gmdate('D, d M Y H:i:s', time() + $maxage) .' GMT');
            header('Cache-Control: max-age=' . $maxage);
            header('Pragma: public');
        }
        $path = $THEME->get_path('images/thumb.png', false, $basepath);
        if (is_readable($path)) {
            readfile_exit($path);
        }
        $path = get_config('docroot') . $basepath . '/thumb.png';
        if (is_readable($path)) {
            readfile_exit($path);
        }
        readfile_exit($THEME->get_path('images/no_thumbnail.png'));
}

function readfile_exit($path) {
    readfile($path);
    perf_to_log();
    exit;
}
