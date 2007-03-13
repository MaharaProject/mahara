<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
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
 * @param array  $options  Any options to use when serving the file. Currently
 *                         there are none.
 */
function serve_file($path, $filename, $options=array()) {
    $dataroot = get_config('dataroot');
    $path = realpath($path);
    $options = array_merge(array(
        'lifetime' => 86400
    ), $options);

    if (substr($path, 0, strlen($dataroot)) != $dataroot) {
        throw new AccessDeniedException();
    }

    if (!file_exists($path)) {
        throw new NotFoundException();
    }

    session_write_close(); // unlock session during fileserving

    $mimetype     = get_mime_type($path);
    if (!$mimetype || (!is_image_mime_type($mimetype) && false !== strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))) {
        $mimetype = 'application/forcedownload';
    }
    $lastmodified = filemtime($path);
    $filesize     = filesize($path);

    if (ini_get('zlib.output_compression')) {
        ini_set('zlib.output_compression', 'Off');
    }

    // Try to disable automatic sid rewrite in cookieless mode
    @ini_set('session.use_trans_sid', 'false');

    header('Last-Modified: '. gmdate('D, d M Y H:i:s', $lastmodified) .' GMT');

    // @todo possibly need addslashes on the filename, but I'm unsure on exactly
    // how the browsers will handle it.
    if ($mimetype == 'application/forcedownload') {
        header('Content-Disposition: attachment; filename="' . $filename . '"');
    }
    else {
        header('Content-Disposition: inline; filename="' . $filename . '"');
    }

    if ($options['lifetime'] > 0) {
        header('Cache-Control: max-age=' . $options['lifetime']);
        header('Expires: '. gmdate('D, d M Y H:i:s', time() + $options['lifetime']) .' GMT');
        header('Pragma: ');

        if ($mimetype != 'text/plain' && $mimetype != 'text/html') {
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
        header('Content-Type: ' . $mimetype);
    }
    header('Content-Length: ' . $filesize);
    while (@ob_end_flush()); //flush the buffers - save memory and disable sid rewrite
    readfile_chunked($path);
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

    @set_time_limit(60 * 60); //reset time limit to 60 min - should be enough for 1 MB chunk

    while (!feof($handle)) {
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
        @set_time_limit(60*60); //reset time limit to 60 min - should be enough for 1 MB chunk
        $buffer = '';
        fseek($handle, $ranges[0][1]);
        while (!feof($handle) && $length > 0) {
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
            @set_time_limit(60 * 60); //reset time limit to 60 min - should be enough for 1 MB chunk
            while (!feof($handle) && $length > 0) {
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
 * Given a path under dataroot, an ID and a size, return the path to a file
 * matching all criteria.
 *
 * If the file with the ID exists but not of the correct size, this function
 * will make a copy that is resized to the correct size.
 */
function get_dataroot_image_path($path, $id, $size) {
    $dataroot = get_config('dataroot');
    $imagepath = $dataroot . $path;

    if (!is_dir($imagepath) || !is_readable($imagepath)) {
        return false;
    }
    //$imagepath .= "/$id";

    if ($size && !preg_match('/\d+x\d+/', $size)) {
        throw new UserException('Invalid size for image specified');
    }

    // If the image is already available, return the path to it
    $path = $imagepath . '/' . ($size ? "$size/" : '') . ($id % 256) . "/$id";
    if (is_readable($path)) {
        return $path;
    }

    if ($size) {
        // Image is not available in this size. If there is a base image for
        // it, we can make one however.
        $originalimage = $imagepath . '/' . ($id % 256) . "/$id";
        if (is_readable($originalimage)) {

            list($width, $height) = explode('x', $size);

            switch (get_mime_type($originalimage)) {
                case 'image/jpeg':
                case 'image/jpg':
                    $oldih = imagecreatefromjpeg($originalimage);
                    break;
                case 'image/png':
                    $oldih = imagecreatefrompng($originalimage);
                    break;
                case 'image/gif':
                    $oldih = imagecreatefromgif($originalimage);
                    break;
                case 'image/bmp':
                case 'image/x-bmp':
                case 'image/ms-bmp':
                case 'image/x-ms-bmp':
                    if (!extension_loaded('imagick')) {
                        return false;
                    }
                    // Nightmare...
                    $oldih = imagick_readimage($originalimage);
                    imagick_resize($oldih, $width, $height, IMAGICK_FILTER_UNDEFINED, 1);
                    $newpath = $imagepath . "/$size/" . ($id % 256);
                    check_dir_exists($newpath);
                    $newpath .= "/$id";
                    $result = imagick_writeimage($oldih, $newpath);
                    return $newpath;
                    break;
                default:
                    return false;
            }

            if (!$oldih) {
                return false;
            }

            $oldx = imagesx($oldih);
            $oldy = imagesy($oldih);

            if ($oldy > $oldx) {
                $newy = $height;
                $newx = ($oldx * $newy) / $oldy;
            }
            else {
                $newx = $width;
                $newy = ($oldx * $newx) / $oldx;
            }

            $newih = imagecreatetruecolor($newx, $newy);
            imagecopyresized($newih, $oldih, 0, 0, 0, 0, $newx, $newy, $oldx, $oldy);
            imageinterlace($newih);
            $newpath = $imagepath . "/$size/" . ($id % 256);
            check_dir_exists($newpath);
            $newpath .= "/$id";
            $result = imagepng($newih, $newpath);
            return $newpath;
        }
    }

    // Image not available in any size
    return false;
}

/**
 * Deletes an image, including all resized versions of it, from dataroot.
 *
 * This function does not delete anything from anywhere else - it is your
 * responsibility to delete any database records.
 *
 * @param string $path The path in dataroot of the base directory where the
 *                     image resides.
 * @param int $id      The id of the image to delete.
 * @return boolean     Whether the image was deleted successfully.
 */
function delete_image($path, $id) {
    // Check that the image exists.
    $dataroot = get_config('dataroot');
    $imagepath = $dataroot . $path;

    if (!is_dir($imagepath) || !is_readable($imagepath)) {
        return false;
    }

    $originalimage = $imagepath . '/' . ($id % 256) . "/$id";
    if (!is_readable($originalimage)) {
        return false;
    }

    unlink($originalimage);

    // Check the size subdirectories
    $dh = opendir($imagepath);
    while (false !== ($file = readdir($dh))) {
        $path = $imagepath . '/' . $file;
        if (!preg_match('/\d+x\d+/', $file) || !is_dir($path)) {
            continue;
        }

        $image = $path . '/' . ($id % 256) . '/' . $id;
        if (is_readable($image)) {
            unlink($image);
        }
    }

    return true;
}

?>
