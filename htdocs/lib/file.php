<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage core
 * @author     Martin Dougiamas <martin@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2001-3001 Martin Dougiamas http://dougiamas.com
 * @copyright  additional modifications (c) Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

define('BYTESERVING_BOUNDARY', 'm1i2k3e40516'); //unique string constant

/**
 * Serves a file from dataroot.
 *
 * This function checks that the file is inside dataroot, but does not perform
 * any other checks. Authors using this function should make sure that their
 * scripts perform appropriate authentication.
 *
 * As an example: If the file is an artefact, you could ask for an artefact and
 * view ID, and check that the artefact is in the view and that the user can
 * view the view.
 *
 * @param string $path     The file to send. Must include the dataroot path.
 * @param string $filename The name of the file as the browser should use to
 *                         serve it.
 * @param string $mimetype Mime type to be sent in header
 * @param array  $options  Any options to use when serving the file. Currently
 *                         lifetime = 0 for no cache
 *                         forcedownload - force application rather than inline
 *                         overridecontenttype - send this instead of the mimetype
 *                         there are none.
 */
function serve_file($path, $filename, $mimetype, $options=array()) {
    $dataroot = realpath(get_config('dataroot'));
    $path = realpath($path);
    $options = array_merge(array(
        'lifetime' => 86400
    ), $options);

    if (!get_config('insecuredataroot') && substr($path, 0, strlen($dataroot)) != $dataroot) {
        throw new AccessDeniedException();
    }

    if (!file_exists($path)) {
        throw new NotFoundException();
    }

    session_write_close(); // unlock session during fileserving

    $lastmodified = filemtime($path);
    $filesize     = filesize($path);

    if ($mimetype == 'text/html' || $mimetype == 'text/xml') {
        if (isset($options['downloadurl']) && $filesize < 1024 * 1024) {
            display_cleaned_html(file_get_contents($path), $filename, $options);
            exit;
        }
        $options['forcedownload'] = true;
        $mimetype = 'application/octet-stream';
    }

    if (!$mimetype) {
        $mimetype = 'application/forcedownload';
    }

    if (ini_get('zlib.output_compression')) {
        ini_set('zlib.output_compression', 'Off');
    }

    // Try to disable automatic sid rewrite in cookieless mode
    @ini_set('session.use_trans_sid', 'false');

    header('Last-Modified: '. gmdate('D, d M Y H:i:s', $lastmodified) .' GMT');

    // @todo possibly need addslashes on the filename, but I'm unsure on exactly
    // how the browsers will handle it.
    if ($mimetype == 'application/forcedownload' || isset($options['forcedownload'])) {
        header('Content-Disposition: attachment; filename="' . $filename . '"');
    }
    else {
        header('Content-Disposition: inline; filename="' . $filename . '"');
    }

    if ($options['lifetime'] > 0) {
        header('Cache-Control: max-age=' . $options['lifetime']);
        header('Expires: '. gmdate('D, d M Y H:i:s', time() + $options['lifetime']) .' GMT');
        header('Pragma: ');

        if ($mimetype != 'text/plain' && $mimetype != 'text/html' && !isset($fileoutput)) {
            @header('Accept-Ranges: bytes');

            if (!empty($_SERVER['HTTP_RANGE']) && strpos($_SERVER['HTTP_RANGE'],'bytes=') !== FALSE) {
                // Byteserving stuff - for Acrobat Reader and download accelerators
                // see: http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.35
                // inspired by: http://www.coneural.org/florian/papers/04_byteserving.php
                $ranges = false;
                if (preg_match_all('/(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $ranges, PREG_SET_ORDER)) {
                    foreach ($ranges as $key => $value) {
                        if ($ranges[$key][1] == '') {
                            // Suffix case
                            $ranges[$key][1] = $filesize - $ranges[$key][2];
                            $ranges[$key][2] = $filesize - 1;
                        }
                        else if ($ranges[$key][2] == '' || $ranges[$key][2] > $filesize - 1) {
                            // Fix range length
                            $ranges[$key][2] = $filesize - 1;
                        }
                        if ($ranges[$key][2] != '' && $ranges[$key][2] < $ranges[$key][1]) {
                            // Invalid byte-range ==> ignore header
                            $ranges = false;
                            break;
                        }

                        // Prepare multipart header
                        $ranges[$key][0] =  "\r\n--" . BYTESERVING_BOUNDARY . "\r\nContent-Type: $mimetype\r\n";
                        $ranges[$key][0] .= "Content-Range: bytes {$ranges[$key][1]}-{$ranges[$key][2]}/$filesize\r\n\r\n";
                    }
                } else {
                    $ranges = false;
                }
                if ($ranges) {
                    byteserving_send_file($path, $mimetype, $ranges);
                }
            }
        }
        else {
            // Do not byteserve (disabled, strings, text and html files).
            header('Accept-Ranges: none');
        }
    }
    else { // Do not cache files in proxies and browsers
        if (strpos(get_config('wwwroot'), 'https://') === 0) { //https sites - watch out for IE! KB812935 and KB316431
            header('Cache-Control: max-age=10');
            header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
            header('Pragma: ');
        }
        else { //normal http - prevent caching at all cost
            header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
            header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
            header('Pragma: no-cache');
        }
        header('Accept-Ranges: none'); // Do not allow byteserving when caching disabled
    }

    if ($mimetype == 'text/plain') {
        // Add encoding
        header('Content-Type: Text/plain; charset=utf-8');
    }
    else {
        if (isset($options['overridecontenttype'])) {
            header('Content-Type: ' . $options['overridecontenttype']);
        }
        else {
            header('Content-Type: ' . $mimetype);
        }
    }
    header('Content-Length: ' . $filesize);
    while (@ob_end_flush()); //flush the buffers - save memory and disable sid rewrite
    readfile_chunked($path);
    perf_to_log();
    exit;
}

/**
 * Improves memory consumptions and works around buggy readfile() in PHP 5.0.4 (2MB readfile limit).
 */
function readfile_chunked($filename, $retbytes=true) {
    $chunksize = 1 * (1024 * 1024); // 1MB chunks - must be less than 2MB!
    $buffer = '';
    $cnt =0;
    $handle = fopen($filename, 'rb');
    if ($handle === false) {
        return false;
    }


    while (!feof($handle)) {
        @set_time_limit(60 * 60); //reset time limit to 60 min - should be enough for 1 MB chunk
        $buffer = fread($handle, $chunksize);
        echo $buffer;
        flush();
        if ($retbytes) {
            $cnt += strlen($buffer);
        }
    }
    $status = fclose($handle);
    if ($retbytes && $status) {
        return $cnt; // return num. bytes delivered like readfile() does.
    }
    return $status;
}

/**
 * Send requested byterange of file.
 */
function byteserving_send_file($filename, $mimetype, $ranges) {
    $chunksize = 1 * (1024 * 1024); // 1MB chunks - must be less than 2MB!
    $handle = fopen($filename, 'rb');
    if ($handle === false) {
        die;
    }
    if (count($ranges) == 1) { //only one range requested
        $length = $ranges[0][2] - $ranges[0][1] + 1;
        header('HTTP/1.1 206 Partial content');
        header('Content-Length: ' . $length);
        header('Content-Range: bytes ' . $ranges[0][1] . '-' . $ranges[0][2] . '/' . filesize($filename));
        header('Content-Type: ' . $mimetype);
        while (@ob_end_flush()); //flush the buffers - save memory and disable sid rewrite
        $buffer = '';
        fseek($handle, $ranges[0][1]);
        while (!feof($handle) && $length > 0) {
            @set_time_limit(60*60); //reset time limit to 60 min - should be enough for 1 MB chunk
            $buffer = fread($handle, ($chunksize < $length ? $chunksize : $length));
            echo $buffer;
            flush();
            $length -= strlen($buffer);
        }
        fclose($handle);
        exit;
    }
    else { // multiple ranges requested - not tested much
        $totallength = 0;
        foreach($ranges as $range) {
            $totallength += strlen($range[0]) + $range[2] - $range[1] + 1;
        }
        $totallength += strlen("\r\n--" . BYTESERVING_BOUNDARY . "--\r\n");
        header('HTTP/1.1 206 Partial content');
        header('Content-Length: ' . $totallength);
        header('Content-Type: multipart/byteranges; boundary=' . BYTESERVING_BOUNDARY);
        //TODO: check if "multipart/x-byteranges" is more compatible with current readers/browsers/servers
        while (@ob_end_flush()); //flush the buffers - save memory and disable sid rewrite
        foreach($ranges as $range) {
            $length = $range[2] - $range[1] + 1;
            echo $range[0];
            $buffer = '';
            fseek($handle, $range[1]);
            while (!feof($handle) && $length > 0) {
                @set_time_limit(60 * 60); //reset time limit to 60 min - should be enough for 1 MB chunk
                $buffer = fread($handle, ($chunksize < $length ? $chunksize : $length));
                echo $buffer;
                flush();
                $length -= strlen($buffer);
            }
        }
        echo "\r\n--" . BYTESERVING_BOUNDARY . "--\r\n";
        fclose($handle);
        exit;
    }
}


/**
 * Given a file path, retrieves the mime type of the file using the
 * unix 'file' program.
 *
 * This is only implemented for non-windows based operating systems. Mahara
 * does not support windows at this time.
 *
 * Sometimes file will be unable to detect the mimetype, in which case
 * it will return the empty string.
 *
 *
 * This function should no longer be required.  Mime types are now
 * stored along with files in the artefact tables, and passed directly
 * to serve_file.  Left in place for the upgrade to initially populate
 * the mime type of existing files.
 * See htdocs/artefact/file/db/upgrade.php.
 *
 *
 * @param string $file The file to check
 * @return string      The mime type of the file, or false if file is not available.
 */
function get_mime_type($file) {
    switch (strtolower(PHP_OS)) {
    case 'win' :
        throw new SystemException('retrieving filetype not supported in windows');
    default : 
        $filepath = get_config('pathtofile');
        if (!empty($filepath)) {
            list($output,) = preg_split('/[\s;]/', exec($filepath . ' -ib ' . escapeshellarg($file)));
            return $output;
        }
        return false;
    }
}


/**
 * Given a file path, guesses the mime type of the file using the
 * php functions finfo_file, mime_content_type, or looking for the
 * file extension in the artefact_file_mime_types table
 *
 * @param string $file The file to check
 * @return string      The mime type of the file
 */
function file_mime_type($file) {
    static $mimetypes = null;

    if (class_exists('finfo')) {
        // upstream bug in php #54714
        // http://bugs.php.net/bug.php?id=54714
        //
        // according to manual (http://www.php.net/manual/en/function.finfo-open.php)
        // default option is /usr/share/misc/magic, then /usr/share/misc/magic.mgc
        //
        // if /usr/share/misc/magic is a directory then finfo still succeeds and 
        // doesn't fall back onto the .mcg magic_file
        // force /usr/share/misc/magic.mgc instead in this case

        $magicfile = !is_file('/usr/share/misc/magic') ? '/usr/share/misc/magic.mgc' : null;

        if (defined('FILEINFO_MIME_TYPE')) {
            if ($finfo = @new finfo(FILEINFO_MIME_TYPE, $magicfile)) {
                $type = @$finfo->file($file);
            }
        }
        else if ($finfo = @new finfo(FILEINFO_MIME, $magicfile)) {
            if ($typecharset = @$finfo->file($file)) {
                if ($bits = explode(';', $typecharset)) {
                    $type = $bits[0];
                }
            }
        }
    }
    else if (function_exists('mime_content_type')) {
        $type = mime_content_type($file);
    }

    if (!empty($type) && $type != 'application/octet-stream') {
        return $type;
    }

    // Try the filename extension in case it's a file that Mahara
    // cares about.  For now, use the artefact_file_mime_types table,
    // even though it's in a plugin and the description column doesn't
    // really contain filename extensions.
    $basename = basename($file);
    if (strpos($basename, '.', 1)) {
        if (is_null($mimetypes)) {
            $mimetypes = get_records_assoc('artefact_file_mime_types', '', '', '', 'description,mimetype');
        }
        $ext = strtolower(array_pop(explode('.', $basename)));
        if (isset($mimetypes[$ext])) {
            return $mimetypes[$ext]->mimetype;
        }
    }

    return 'application/octet-stream';
}


/**
 * Given a mimetype (perhaps returned by {@link get_mime_type}, returns whether
 * Mahara thinks it is a valid image file.
 *
 * Not all image types are valid for Mahara. Mahara supports JPEG, PNG, GIF
 * and BMP.
 *
 * @param string $type The mimetype to check
 * @return boolean     Whether the type is a valid image type for Mahara
 */
function is_image_mime_type($type) {
    $supported = array(
        'image/jpeg', 'image/jpg',
        'image/gif',
        'image/png'
    );
    if (extension_loaded('imagick')) {
        $supported = array_merge($supported, array(
            'image/bmp', 'image/x-bmp', 'image/ms-bmp', 'image/x-ms-bmp'
        ));
    }
    return in_array($type, $supported);
}


/**
 * Given an image type returned by getimagesize or exif_imagetype, returns whether
 * Mahara thinks it is a valid image type.
 *
 * Not all image types are valid for Mahara. Mahara supports JPEG, PNG, GIF
 * and BMP.
 *
 * @param string $type The type to check
 * @return boolean     Whether the type is a valid image type for Mahara
 */
function is_image_type($type) {
    $supported = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);
    if (extension_loaded('imagick')) {
        $supported[] = IMAGETYPE_BMP;
    }
    return $type && in_array($type, $supported);
}


/**
 * Given path to a file, returns whether Mahara thinks it is a valid image file.
 *
 * Not all image types are valid for Mahara. Mahara supports JPEG, PNG, GIF
 * and BMP.
 *
 * @param string $path The file to check
 * @return boolean     Whether the file is a valid image file for Mahara
 */
function is_image_file($path) {
    if (function_exists('exif_imagetype')) {
        // exif_imagetype is faster
        // surpressing errors because exif_imagetype spews "read error!" to the logs on small files
        // http://nz.php.net/manual/en/function.exif-imagetype.php#79283
        if (!$type = @exif_imagetype($path)) {
            return false;
        }
    }
    else {
        // getimagesize returns the same answer
        if (!list ($width, $height, $type) = getimagesize($path)) {
            return false;
        }
    }
    return is_image_type($type);
}


/**
 * Given a path under dataroot, an ID and a size, return the path to a file
 * matching all criteria.
 *
 * If the file with the ID exists but not of the correct size, this function
 * will make a copy that is resized to the correct size.
 *
 * @param string $path The base path in dataroot where the image is stored. For 
 *                     example, 'artefact/file/profileicons/' for profile 
 *                     icons
 * @param int $id      The ID of the image to return. Is typically the ID of an 
 *                     artefact
 * @param mixed $size  The size the image should be.
 *
 *                      As a two element hash with 'w' and 'h' keys:
 *                     - If 'w' and 'h' are not empty, the image will be 
 *                       exactly that size
 *                     - If just 'w' is not empty, the image will be that wide, 
 *                       and the height will be set to make the image scale 
 *                       correctly
 *                     - If just 'h' is not empty, the image will be that high, 
 *                       and the width will be set to make the image scale 
 *                       correctly
 *                     - If neither are set or the parameter is not set, the 
 *                       image will not be resized
 *
 *                     As a number, the path returned will have the largest side being 
 *                     the length specified.
 * @return string The path on disk where the appropriate file resides, or false 
 *                if an appropriate file could not be located or generated
 */
function get_dataroot_image_path($path, $id, $size=null) {
    $dataroot = get_config('dataroot');
    $imagepath = $dataroot . $path;
    if (substr($imagepath, -1) == '/') {
        $imagepath = substr($imagepath , 0, -1);
    }

    if (!is_dir($imagepath) || !is_readable($imagepath)) {
        return false;
    }

    // Work out the location of the original image
    $originalimage = $imagepath . '/originals/' . ($id % 256) . "/$id";

    // If the original has been deleted, then don't show any image, even a cached one. 
    // delete_image only deletes the original, not any cached ones, so we have 
    // to make sure the original is still around
    if (!is_readable($originalimage)) {
        return false;
    }

    if (!$size) {
        // No size has been asked for. Return the original
        return $originalimage;
    }
    else {
        // Check if the image is available in the size requested
        $sizestr = serialize($size);
        $md5     = md5("{$id}.{$sizestr}");

        $resizedimagedir = $imagepath . '/resized/';
        check_dir_exists($resizedimagedir);
        for ($i = 0; $i <= 2; $i++) {
           $resizedimagedir .= substr($md5, $i, 1) . '/';
            check_dir_exists($resizedimagedir);
        }
        $resizedimagefile = "{$resizedimagedir}{$md5}.$id";//.$sizestr";

        if (is_readable($resizedimagefile)) {
            return $resizedimagefile;
        }

        // Image is not available in this size. If there is a base image for
        // it, we can make one however.
        if (is_readable($originalimage)) {

            $imageinfo = getimagesize($originalimage);
            $originalmimetype = $imageinfo['mime'];
            $format = 'png';
            switch ($originalmimetype) {
                case 'image/jpeg':
                case 'image/jpg':
                    $format = 'jpeg';
                    $oldih = imagecreatefromjpeg($originalimage);
                    break;
                case 'image/png':
                    $oldih = imagecreatefrompng($originalimage);
                    break;
                case 'image/gif':
                    $format = 'gif';
                    $oldih = imagecreatefromgif($originalimage);
                    break;
                case 'image/bmp':
                case 'image/x-bmp':
                case 'image/ms-bmp':
                case 'image/x-ms-bmp':
                    if (!extension_loaded('imagick') || !class_exists('Imagick')) {
                        log_info('Bitmap image detected for resizing, but imagick extension is not available');
                        return false;
                    }

                    $ih = new Imagick($originalimage);
                    if (!$newdimensions = image_get_new_dimensions($ih->getImageWidth(), $ih->getImageHeight(), $size)) {
                        return false;
                    }
                    $ih->resizeImage($newdimensions['w'], $newdimensions['h'], imagick::FILTER_LANCZOS, 1);
                    if ($ih->writeImage($resizedimagefile)) {
                        return $resizedimagefile;
                    }
                    return false;
                default:
                    return false;
            }

            if (!$oldih) {
                return false;
            }

            $oldx = imagesx($oldih);
            $oldy = imagesy($oldih);

            if (!$newdimensions = image_get_new_dimensions($oldx, $oldy, $size)) {
                return false;
            }

            $newih = imagecreatetruecolor($newdimensions['w'], $newdimensions['h']);

            if ($originalmimetype == 'image/png' || $originalmimetype == 'image/gif') {
                // Create a new destination image which is completely 
                // transparent and turn off alpha blending for it, so that when 
                // the PNG source file is copied, the alpha channel is retained.
                // Thanks to http://alexle.net/archives/131

                $background = imagecolorallocate($newih, 0, 0, 0);
                imagecolortransparent($newih, $background);
                imagealphablending($newih, false);
                imagecopyresampled($newih, $oldih, 0, 0, 0, 0, $newdimensions['w'], $newdimensions['h'], $oldx, $oldy);
                imagesavealpha($newih, true);
            }
            else {
                // imagecopyresized is faster, but results in noticeably worse image quality. 
                // Given the images are resized only once each time they're 
                // made, I suggest you just leave the good quality one in place
                imagecopyresampled($newih, $oldih, 0, 0, 0, 0, $newdimensions['w'], $newdimensions['h'], $oldx, $oldy);
                //imagecopyresized($newih, $oldih, 0, 0, 0, 0, $newdimensions['w'], $newdimensions['h'], $oldx, $oldy);
            }

            $outputfunction = "image$format";
            $result = $outputfunction($newih, $resizedimagefile);
            if ($result) {
                return $resizedimagefile;
            }
        } // end attempting to build a resized image
    }

    // Image not available in any size
    return false;
}

/**
 * Given the old dimensions of an image and a size object as obtained from 
 * get_imagesize_parameters(), calculates what the new size of the image should 
 * be
 *
 * @param int $oldx   The width of the image to calculate the new size for
 * @param int $oldy   The height of the image to calculate the new size for
 * @param mixed $size The size data
 * @return array      A hash with the new width and height, keyed by 'w' and 'h'
 */
function image_get_new_dimensions($oldx, $oldy, $size) {
    if (is_int($size)) {
        // If just a number (number is width AND height here)
        if ($oldy > $oldx) {
            $newy = $size;
            $newx = ($oldx * $newy) / $oldy;
        }
        else {
            $newx = $size;
            $newy = ($oldy * $newx) / $oldx;
        }
    }
    else if (isset($size['w']) && isset($size['h'])) {
        // If size explicitly X by Y
        $newx = $size['w'];
        $newy = $size['h'];
    }
    else if (isset($size['w'])) {
        // Else if just width
        $newx = $size['w'];
        $newy = ($oldy * $newx) / $oldx;
    }
    else if (isset($size['h'])) {
        // Else if just height
        $newy = $size['h'];
        $newx = ($oldx * $newy) / $oldy;
    }
    else if (isset($size['maxw']) && isset($size['maxh'])) {
        $scale = min(min($size['maxw'], $oldx) / $oldx, min($size['maxh'], $oldy) / $oldy);
        $newx = max(1, $oldx * $scale);
        $newy = max(1, $oldy * $scale);
    }
    else if (isset($size['maxw'])) {
        // Else if just maximum width
        if ($oldx > $size['maxw']) {
            $newx = $size['maxw'];
            $newy = ($oldy * $newx) / $oldx;
        }
        else {
            $newx = $oldx;
            $newy = $oldy;
        }
    }
    else if (isset($size['maxh'])) {
        // Else if just maximum height
        if ($oldy > $size['maxh']) {
            $newy = $size['maxh'];
            $newx = ($oldx * $newy) / $oldy;
        }
        else {
            $newx = $oldx;
            $newy = $oldy;
        }
    }
    else {
        return false;
    }
    return array('w' => $newx, 'h' => $newy);
}

/**
 * Deletes an image, excluding all resized versions of it, from dataroot.
 *
 * This function does not delete anything from anywhere else - it is your
 * responsibility to delete any database records.
 *
 * This function also does not try to delete all resized versions of this
 * image, as it would take a lot of effort to find them all.
 *
 * @param string $path The path in dataroot of the base directory where the
 *                     image resides.
 * @param int $id      The id of the image to delete.
 * @return boolean     Whether the image was deleted successfully.
 */
function delete_image($path, $id) {
    // Check that the image exists.
    $dataroot = get_config('dataroot');
    $imagepath = $dataroot . $path . '/originals';

    if (!is_dir($imagepath) || !is_readable($imagepath)) {
        return false;
    }

    $originalimage = $imagepath . '/' . ($id % 256) . "/$id";
    if (!is_readable($originalimage)) {
        return false;
    }

    unlink($originalimage);
    return true;
}

/**
 * Delete a file, or a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.3
 * @link        http://aidanlister.com/repos/v/function.rmdirr.php
 * @param       string   $dirname    Directory to delete
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function rmdirr($dirname)
{
    // Sanity check
    if (!file_exists($dirname)) {
        return false;
    }
 
    // Simple delete for a file
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }
 
    // Loop through the folder
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }
 
        // Recurse
        rmdirr($dirname . '/' . $entry);
    }
 
    // Clean up
    $dir->close();
    return rmdir($dirname);
}

/**
 * Copy a file, or recursively copy a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @link        http://aidanlister.com/repos/v/function.copyr.php
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function copyr($source, $dest)
{
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        copyr("$source/$entry", "$dest/$entry");
    }

    // Clean up
    $dir->close();
    return true;
}
