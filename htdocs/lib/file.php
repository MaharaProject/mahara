<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Martin Dougiamas <martin@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2001-3001 Martin Dougiamas http://dougiamas.com
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

    if ($mimetype == 'text/html'
        || $mimetype == 'text/xml'
        || $mimetype == 'application/xml'
        || $mimetype == 'application/xhtml+xml'
        || $mimetype == 'image/svg+xml') {
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
        header('Content-Disposition: attachment; filename="' . str_replace('"', '\"', $filename) . '"');
    }
    else {
        header('Content-Disposition: inline; filename="' . str_replace('"', '\"', $filename) . '"');
    }
    header('X-Content-Type-Options: nosniff');

    if ($options['lifetime'] > 0 && !get_config('nocache')) {
        header('Cache-Control: max-age=' . $options['lifetime']);
        header('Expires: '. gmdate('D, d M Y H:i:s', time() + $options['lifetime']) .' GMT');
        header('Pragma: ');
    }
    else { // Do not cache files in proxies and browsers
        if (is_https() === true) { //https sites - watch out for IE! KB812935 and KB316431
            header('Cache-Control: max-age=10');
            header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
            header('Pragma: ');
        }
        else { //normal http - prevent caching at all cost
            header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
            header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
            header('Pragma: no-cache');
        }
    }

    // Allow byteranges
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
        }
        else {
            $ranges = false;
        }
        if ($ranges) {
            byteserving_send_file($path, $mimetype, $ranges);
        }
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
 * Given a file path, guesses the mime type of the file using the
 * php functions finfo_file, mime_content_type, or looking for the
 * file extension in the artefact_file_mime_types table
 *
 * @param string $file The file to check
 * @param string $originalfilename The original name of the file (so we can check its extension)
 * @return string      The mime type of the file
 */
function file_mime_type($file, $originalfilename=false) {
    static $mimetypes = null;

    // Try the filename extension in case it's a file that Mahara
    // cares about.  For now, use the artefact_file_mime_types table,
    // even though it's in a plugin and the description column doesn't
    // really contain filename extensions.
    if ($originalfilename) {
        $basename = $originalfilename;
    }
    else {
        $basename = basename($file);
    }
    if (strpos($basename, '.', 1)) {
        if (is_null($mimetypes)) {
            $mimetypes = get_records_assoc('artefact_file_mime_types', '', '', '', 'description,mimetype');
        }
        $ext = strtolower(array_pop(explode('.', $basename)));
        if (isset($mimetypes[$ext])) {
            return $mimetypes[$ext]->mimetype;
        }
    }

    // Try detecting it with the magic.mgc file
    if (get_config('pathtomagicdb') !== null) {
        // Manually specified magicdb path in config.php
        $magicfile = get_config('pathtomagicdb');
    }
    else {
        // Using one of the system presets (or if no matching system preset, this
        // will return false, indicating we shouldn't bother with fileinfo
        $magicfile = standard_magic_paths(get_config('defaultmagicdb'));
    }

    if ($magicfile !== false && class_exists('finfo') ) {
        if ($finfo = @new finfo(FILEINFO_MIME_TYPE, $magicfile)) {
            $type = @$finfo->file($file);
        }
    }
    else if (function_exists('mime_content_type')) {
        $type = mime_content_type($file);
    }

    if (!empty($type)) {
        return $type;
    }

    return 'application/octet-stream';
}


/**
 * The standard locations we would expect the magicdb to be. The keys of the array returned
 * by this value, are the values stored in the config
 * @param int $key (optional)
 * @return multitype:string If a key is supplied, return the path matching that key. If no
 * key is supplied, return the full array of possible magic locations.
 */
function standard_magic_paths($key = 'fullarray') {
    static $standardmagicpaths = array(
        1=>'',
        2=>'/usr/share/misc/magic',
        3=>'/usr/share/misc/magic.mgc',
    );

    if ($key === 'fullarray') {
        return $standardmagicpaths;
    }

    if (array_key_exists($key, $standardmagicpaths)) {
        return $standardmagicpaths[$key];
    }
    else {
        return false;
    }
}


/**
 * Try a few different likely possibilities for the magicdb and see which of them returns
 * the correct response. Then store that configuration option for later use, in the config
 * setting 'defaultmagicdb'. Because this is a DB-settable setting, we don't store the file
 * path directly in it, but instead just store a key corresponding to a path specified in
 * standard_magic_paths().
 */
function update_magicdb_path() {
    // Determine where the server's "magic" db is\
    if (class_exists('finfo')) {
        $file = get_config('docroot') . 'theme/raw/images/powered_by_mahara.png';

        $magicpathstotry = standard_magic_paths();
        $workingpath = false;
        foreach ($magicpathstotry as $i=>$magicfile) {
            $type = false;
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
            if ($type == 'image/png') {
                $workingpath = $i;
                break;
            }
        }
        if (!$workingpath) {
            log_debug('Could not locate the path to your fileinfo magic db. Please set it manually using $cfg->pathtomagicdb.');
            $workingpath = 0;
        }
        set_config('defaultmagicdb', $workingpath);
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
    global $THEME;
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
        if (is_readable($originalimage) && filesize($originalimage)) {

            $imageinfo = getimagesize($originalimage);
            $originalmimetype = $imageinfo['mime'];

            // gd can eat a lot of memory shrinking large images, so use a placeholder image
            // here if necessary
            if (isset($imageinfo['bits'])) {
                $bits = $imageinfo['bits'];
            }
            else if ($imageinfo['mime'] == 'image/gif') {
                $bits = 8;
            }
            if (isset($imageinfo[0]) && isset($imageinfo[1]) && !empty($bits)) {
                $approxmem = $imageinfo[0] * $imageinfo[1] * ($bits / 8)
                    * (isset($imageinfo['channels']) ? $imageinfo['channels'] : 3);
            }
            if (empty($approxmem) || $approxmem > get_config('maximageresizememory')) {
                log_debug("Refusing to resize large image $originalimage $originalmimetype "
                    . $imageinfo[0] . 'x' .  $imageinfo[1] . ' ' . $imageinfo['bits'] . '-bit');
                $originalimage = $THEME->get_path('images/no_thumbnail.png');
                if (empty($originalimage) || !is_readable($originalimage)) {
                    return false;
                }
                $imageinfo = getimagesize($originalimage);
                $originalmimetype = $imageinfo['mime'];
            }

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
 * @license     Public Domain
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function copyr($source, $dest)
{
    $dest = trim($dest);
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
        mkdir($dest, get_config('directorypermissions'));
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

function file_cleanup_old_cached_files() {
    global $THEME;
    $dirs = array('', '/profileicons');
    foreach (get_all_theme_objects() as $basename => $theme) {
        $dirs[] = '/profileicons/no_userphoto/' . $basename;
    }
    foreach ($dirs as $dir) {
        $basedir = get_config('dataroot') . 'artefact/file' . $dir . '/resized/';
        if (!check_dir_exists($basedir, false)) {
            continue;
        }

        $mintime = time() - (12 * 7 * 24 * 60 * 60); // delete caches older than 12 weeks

        // Cached files are stored in a three tier md5sum layout
        // The actual files are stored in the third directory
        // This loops through all three directories, then checks the files for age
        // It cleans up any empty directories on the way down again

        $iter1 = new DirectoryIterator($basedir);
        foreach ($iter1 as $dir1) {
            if ($dir1->isDot()) continue;
            $dir1path = $dir1->getPath() . '/' . $dir1->getFilename();
            $iter2 = new DirectoryIterator($dir1path);
            foreach ($iter2 as $dir2) {
                if ($dir2->isDot()) continue;
                $dir2path = $dir2->getPath() . '/' . $dir2->getFilename();
                $iter3 = new DirectoryIterator($dir2path);
                foreach ($iter3 as $dir3) {
                    if ($dir3->isDot()) continue;
                    $dir3path = $dir3->getPath() . '/' . $dir3->getFilename();
                    $fileiter = new DirectoryIterator($dir3path);
                    foreach ($fileiter as $file) {
                        if ($file->isFile() && $file->getCTime() < $mintime) {
                            unlink($file->getPath() . '/' . $file->getFilename());
                        }
                    }
                    if (sizeof(scandir($dir3path)) <= 2) {   // first 2 entries are . and ..
                        rmdir($dir3path);
                    }
                }
                if (sizeof(scandir($dir2path)) <= 2) {   // first 2 entries are . and ..
                    rmdir($dir2path);
                }
            }
            if (sizeof(scandir($dir1path)) <= 2) {   // first 2 entries are . and ..
                rmdir($dir1path);
            }
        }
    }
}

/**
 * Create a directory and make sure it is writable.
 *
 * @private
 * @param string $dir  the full path of the directory to be created
 * @param bool $exceptiononerror throw exception if error encountered
 * @return string|false Returns full path to directory if successful, false if not; may throw exception
 */
function make_writable_directory($dir, $exceptiononerror = true) {
    global $CFG;

    if (file_exists($dir) && !is_dir($dir)) {
        if ($exceptiononerror) {
            throw new SystemException($dir . ' directory can not be created, file with the same name already exists.');
        }
        else {
            return false;
        }
    }

    if (!file_exists($dir)) {
        if (!mkdir($dir, $CFG->directorypermissions, true)) {
            clearstatcache();
            // There might be a race condition when creating directory.
            if (!is_dir($dir)) {
                if ($exceptiononerror) {
                    throw new SystemException($dir . ' can not be created, check permissions.');
                }
                else {
                    debugging('Can not create directory: ' . $dir, DEBUG_DEVELOPER);
                    return false;
                }
            }
        }
    }

    if (!is_writable($dir)) {
        if ($exceptiononerror) {
            throw new SystemException($dir . ' is not writable, check permissions.');
        }
        else {
            return false;
        }
    }

    return $dir;
}


/**
 * A regex that can be used with preg_replace to filter out all the characters which are not
 * allowed in XML.
 *
 * Example: $xmlstring = preg_replace(xml_filter_regex(), '', $xmlstring);
 *
 * @return string
 */
function xml_filter_regex() {
    static $regex = null;
    if ($regex !== null) {
        return $regex;
    }
    // See https://en.wikipedia.org/wiki/Valid_characters_in_XML
    $regex = '/[^'
    . '\x{0009}\x{000A}\x{000D}'
    . '\x{0020}-\x{007E}'
    . '\x{0085}'
    . '\x{00A0}-\x{D7FF}\x{E000}-\x{FDCF}\x{FDE0}-\x{FFFD}'
    . '\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}'
    . '\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}'
    . '\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E0000}-\x{EFFFD}\x{F0000}-\x{FFFFD}\x{100000}-\x{10FFFD}'
    .']/u';
    return $regex;
}