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
require_once('layoutpreviewimage.php');

$type = param_alpha('type');

switch ($type) {
    case 'profileiconbyid':
    case 'profileicon':
        $id = param_integer('id', 0);
        $size = get_imagesize_parameters();
        $earlyexpiry = param_boolean('earlyexpiry');
        $useremail = null;

        if ($id) {
            if ($type == 'profileicon') {
                // Convert ID of user to the ID of a profileicon
                $data = get_record_sql('
                    SELECT u.profileicon, u.email, f.filetype
                    FROM {usr} u LEFT JOIN {artefact_file_files} f ON u.profileicon = f.artefact
                    WHERE u.id = ?', array($id));
                if (!empty($data->profileicon)) {
                    $id = $data->profileicon;
                    $mimetype = $data->filetype;
                }
                else {
                    $useremail = $data->email;
                    $id = null;
                }
            }
            else {
                $mimetype = get_field('artefact_file_files', 'filetype', 'artefact', $id);
            }
        }

        if ($id && $fileid = get_field('artefact_file_files', 'fileid', 'artefact', $id)) {
            if ($path = get_dataroot_image_path('artefact/file/profileicons', $fileid, $size)) {
                if ($mimetype) {
                    header('Content-type: ' . $mimetype);

                    if (!get_config('nocache')) {
                        // We can't cache 'profileicon' for as long, because the
                        // user can change it at any time. But we can cache
                        // 'profileiconbyid' for quite a while, because it will
                        // never change
                        if ($type == 'profileiconbyid' and !$earlyexpiry) {
                            $maxage = 604800; // 1 week
                        }
                        else {
                            $maxage = 600; // 10 minutes
                        }
                        header('Expires: '. gmdate('D, d M Y H:i:s', time() + $maxage) .' GMT');
                        header('Cache-Control: max-age=' . $maxage);
                        header('Pragma: public');
                    }

                    readfile_exit($path);
                }
            }
        }

        // Look for an appropriate image on gravatar.com
        if ($useremail and $gravatarurl = remote_avatar_url($useremail, $size)) {
            redirect($gravatarurl);
        }

        // We couldn't find an image for this user. Attempt to use the 'no user
        // photo' image for the current theme

        if (!get_config('nocache')) {
            // We can cache such images
            $maxage = 604800; // 1 week
            if ($earlyexpiry) {
                $maxage = 600; // 10 minutes
            }
            header('Expires: '. gmdate('D, d M Y H:i:s', time() + $maxage) .' GMT');
            header('Cache-Control: max-age=' . $maxage);
            header('Pragma: public');
        }

        if ($path = get_dataroot_image_path('artefact/file/profileicons/no_userphoto/' . $THEME->basename, 0, $size)) {
            header('Content-type: ' . 'image/png');
            readfile_exit($path);
        }

        // If we couldn't find the no user photo picture, we put it into
        // dataroot if we can
        $nouserphotopic = $THEME->get_path('images/no_userphoto.png');
        if ($nouserphotopic) {
            // Move the file into the correct place.
            $directory = get_config('dataroot') . 'artefact/file/profileicons/no_userphoto/' . $THEME->basename . '/originals/0/';
            check_dir_exists($directory);
            copy($nouserphotopic, $directory . '0');
            // Now we can try and get the image in the correct size
            if ($path = get_dataroot_image_path('artefact/file/profileicons/no_userphoto/' . $THEME->basename, 0, $size)) {
                header('Content-type: ' . 'image/png');
                readfile_exit($path);
            }
        }


        // Emergency fallback
        header('Content-type: ' . 'image/png');
        readfile_exit($THEME->get_path('images/no_userphoto.png'));

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
    case 'viewlayout':
        header('Content-type: image/png');
        $vl = param_integer('vl');
        $rows = get_records_sql_assoc('
                SELECT vlrc.row, vlc.widths
                FROM {view_layout_rows_columns} vlrc
                INNER JOIN {view_layout_columns} vlc ON (vlrc.columns = vlc.id)
                WHERE vlrc.viewlayout = ?
                ORDER BY vlrc.row ASC',
                array($vl));

        if ($rows) {
                $filename = 'vl-';
                foreach ($rows as $key => $row) {
                    $filename .= str_replace(',', '-', $row->widths);
                    $filename .= ($key == count($rows))? '.png' : '_';
                }
                if (($path = get_config('dataroot') . LayoutPreviewImage::$destinationfolder . '/' . $filename)
                    && (is_readable($path))) {
                        readfile_exit($path);
                }
                // look in theme folder for default layout thumbs, or dataroot folder for custom layout thumbs
                else if (($path = $THEME->get_path('images/' . $filename))
                        && (is_readable($path))) {
                        readfile_exit($path);
                }
        }
        readfile_exit($THEME->get_path('images/no_thumbnail.png'));
    case 'customviewlayout':
           header('Content-type: image/png');
           $cvl = param_variable('cvl');
           // dataroot folder for custom layout thumbs
           if (($path = get_config('dataroot') . LayoutPreviewImage::$destinationfolder . '/' . $cvl . '.png')
               && (is_readable($path))) {
                   readfile_exit($path);
           }
        readfile_exit($THEME->get_path('images/no_thumbnail.png'));
}

function readfile_exit($path) {
    readfile($path);
    perf_to_log();
    exit;
}
