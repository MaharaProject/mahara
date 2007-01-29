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

define('BYTESERVING_BOUNDARY', 's1k2o3d4a5k6s7'); //unique string constant

/**
 * @return List of information about file types based on extensions. 
 *   Associative array of extension (lower-case) to associative array
 *   from 'element name' to data. Current element names are 'type' and 'icon'.
 *   Unknown types should use the 'xxx' entry which includes defaults. 
 */
//function get_mimetypes_array() {
//    return array (
//        'xxx'  => array ('type'=>'document/unknown', 'icon'=>'unknown.gif'),
//        '3gp'  => array ('type'=>'video/quicktime', 'icon'=>'video.gif'),
//        'ai'   => array ('type'=>'application/postscript', 'icon'=>'image.gif'),
//        'aif'  => array ('type'=>'audio/x-aiff', 'icon'=>'audio.gif'),
//        'aiff' => array ('type'=>'audio/x-aiff', 'icon'=>'audio.gif'),
//        'aifc' => array ('type'=>'audio/x-aiff', 'icon'=>'audio.gif'),
//        'applescript'  => array ('type'=>'text/plain', 'icon'=>'text.gif'),
//        'asc'  => array ('type'=>'text/plain', 'icon'=>'text.gif'),
//        'asm'  => array ('type'=>'text/plain', 'icon'=>'text.gif'),
//        'au'   => array ('type'=>'audio/au', 'icon'=>'audio.gif'),
//        'avi'  => array ('type'=>'video/x-ms-wm', 'icon'=>'avi.gif'),
//        'bmp'  => array ('type'=>'image/bmp', 'icon'=>'image.gif'),
//        'c'    => array ('type'=>'text/plain', 'icon'=>'text.gif'),
//        'cct'  => array ('type'=>'shockwave/director', 'icon'=>'flash.gif'),
//        'cpp'  => array ('type'=>'text/plain', 'icon'=>'text.gif'),
//        'cs'   => array ('type'=>'application/x-csh', 'icon'=>'text.gif'),
//        'css'  => array ('type'=>'text/css', 'icon'=>'text.gif'),
//        'dv'   => array ('type'=>'video/x-dv', 'icon'=>'video.gif'),
//        'dmg'  => array ('type'=>'application/octet-stream', 'icon'=>'dmg.gif'),
//        'doc'  => array ('type'=>'application/msword', 'icon'=>'word.gif'),
//        'dcr'  => array ('type'=>'application/x-director', 'icon'=>'flash.gif'),
//        'dif'  => array ('type'=>'video/x-dv', 'icon'=>'video.gif'),
//        'dir'  => array ('type'=>'application/x-director', 'icon'=>'flash.gif'),
//        'dxr'  => array ('type'=>'application/x-director', 'icon'=>'flash.gif'),
//        'eps'  => array ('type'=>'application/postscript', 'icon'=>'pdf.gif'),
//        'gif'  => array ('type'=>'image/gif', 'icon'=>'image.gif'),
//        'gtar' => array ('type'=>'application/x-gtar', 'icon'=>'zip.gif'),
//        'tgz'   => array ('type'=>'application/g-zip', 'icon'=>'zip.gif'),
//        'gz'   => array ('type'=>'application/g-zip', 'icon'=>'zip.gif'),
//        'gzip' => array ('type'=>'application/g-zip', 'icon'=>'zip.gif'),
//        'h'    => array ('type'=>'text/plain', 'icon'=>'text.gif'),
//        'hpp'  => array ('type'=>'text/plain', 'icon'=>'text.gif'),
//        'hqx'  => array ('type'=>'application/mac-binhex40', 'icon'=>'zip.gif'),
//        'html' => array ('type'=>'text/html', 'icon'=>'html.gif'),
//        'htm'  => array ('type'=>'text/html', 'icon'=>'html.gif'),
//        'java' => array ('type'=>'text/plain', 'icon'=>'text.gif'),
//        'jcb'  => array ('type'=>'text/xml', 'icon'=>'jcb.gif'),
//        'jcl'  => array ('type'=>'text/xml', 'icon'=>'jcl.gif'),
//        'jcw'  => array ('type'=>'text/xml', 'icon'=>'jcw.gif'),
//        'jmt'  => array ('type'=>'text/xml', 'icon'=>'jmt.gif'),
//        'jmx'  => array ('type'=>'text/xml', 'icon'=>'jmx.gif'),
//        'jpe'  => array ('type'=>'image/jpeg', 'icon'=>'image.gif'),
//        'jpeg' => array ('type'=>'image/jpeg', 'icon'=>'image.gif'),
//        'jpg'  => array ('type'=>'image/jpeg', 'icon'=>'image.gif'),
//        'jqz'  => array ('type'=>'text/xml', 'icon'=>'jqz.gif'),
//        'js'   => array ('type'=>'application/x-javascript', 'icon'=>'text.gif'),
//        'latex'=> array ('type'=>'application/x-latex', 'icon'=>'text.gif'),
//        'm'    => array ('type'=>'text/plain', 'icon'=>'text.gif'),
//        'mov'  => array ('type'=>'video/quicktime', 'icon'=>'video.gif'),
//        'movie'=> array ('type'=>'video/x-sgi-movie', 'icon'=>'video.gif'),
//        'm3u'  => array ('type'=>'audio/x-mpegurl', 'icon'=>'audio.gif'),
//        'mp3'  => array ('type'=>'audio/mp3', 'icon'=>'audio.gif'),
//        'mp4'  => array ('type'=>'video/mp4', 'icon'=>'video.gif'),
//        'mpeg' => array ('type'=>'video/mpeg', 'icon'=>'video.gif'),
//        'mpe'  => array ('type'=>'video/mpeg', 'icon'=>'video.gif'),
//        'mpg'  => array ('type'=>'video/mpeg', 'icon'=>'video.gif'),
//
//        'odt'  => array ('type'=>'application/vnd.oasis.opendocument.text', 'icon'=>'odt.gif'),
//        'ott'  => array ('type'=>'application/vnd.oasis.opendocument.text-template', 'icon'=>'odt.gif'),
//        'oth'  => array ('type'=>'application/vnd.oasis.opendocument.text-web', 'icon'=>'odt.gif'),
//        'odm'  => array ('type'=>'application/vnd.oasis.opendocument.text-master', 'icon'=>'odt.gif'),
//        'odg'  => array ('type'=>'application/vnd.oasis.opendocument.graphics', 'icon'=>'odt.gif'),
//        'otg'  => array ('type'=>'application/vnd.oasis.opendocument.graphics-template', 'icon'=>'odt.gif'),
//        'odp'  => array ('type'=>'application/vnd.oasis.opendocument.presentation', 'icon'=>'odt.gif'),
//        'otp'  => array ('type'=>'application/vnd.oasis.opendocument.presentation-template', 'icon'=>'odt.gif'),
//        'ods'  => array ('type'=>'application/vnd.oasis.opendocument.spreadsheet', 'icon'=>'odt.gif'),
//        'ots'  => array ('type'=>'application/vnd.oasis.opendocument.spreadsheet-template', 'icon'=>'odt.gif'),
//        'odc'  => array ('type'=>'application/vnd.oasis.opendocument.chart', 'icon'=>'odt.gif'),
//        'odf'  => array ('type'=>'application/vnd.oasis.opendocument.formula', 'icon'=>'odt.gif'),
//        'odb'  => array ('type'=>'application/vnd.oasis.opendocument.database', 'icon'=>'odt.gif'),
//        'odi'  => array ('type'=>'application/vnd.oasis.opendocument.image', 'icon'=>'odt.gif'),
//
//        'pct'  => array ('type'=>'image/pict', 'icon'=>'image.gif'),
//        'pdf'  => array ('type'=>'application/pdf', 'icon'=>'pdf.gif'),
//        'php'  => array ('type'=>'text/plain', 'icon'=>'text.gif'),
//        'pic'  => array ('type'=>'image/pict', 'icon'=>'image.gif'),
//        'pict' => array ('type'=>'image/pict', 'icon'=>'image.gif'),
//        'png'  => array ('type'=>'image/png', 'icon'=>'image.gif'),
//        'pps'  => array ('type'=>'application/vnd.ms-powerpoint', 'icon'=>'powerpoint.gif'),
//        'ppt'  => array ('type'=>'application/vnd.ms-powerpoint', 'icon'=>'powerpoint.gif'),
//        'ps'   => array ('type'=>'application/postscript', 'icon'=>'pdf.gif'),
//        'qt'   => array ('type'=>'video/quicktime', 'icon'=>'video.gif'),
//        'ra'   => array ('type'=>'audio/x-realaudio', 'icon'=>'audio.gif'),
//        'ram'  => array ('type'=>'audio/x-pn-realaudio', 'icon'=>'audio.gif'),
//        'rhb'  => array ('type'=>'text/xml', 'icon'=>'xml.gif'),
//        'rm'   => array ('type'=>'audio/x-pn-realaudio', 'icon'=>'audio.gif'),
//        'rtf'  => array ('type'=>'text/rtf', 'icon'=>'text.gif'),
//        'rtx'  => array ('type'=>'text/richtext', 'icon'=>'text.gif'),
//        'sh'   => array ('type'=>'application/x-sh', 'icon'=>'text.gif'),
//        'sit'  => array ('type'=>'application/x-stuffit', 'icon'=>'zip.gif'),
//        'smi'  => array ('type'=>'application/smil', 'icon'=>'text.gif'),
//        'smil' => array ('type'=>'application/smil', 'icon'=>'text.gif'),
//        'sqt'  => array ('type'=>'text/xml', 'icon'=>'xml.gif'),
//        'svg'  => array ('type'=>'image/svg+xml', 'icon'=>'image.gif'),
//        'svgz' => array ('type'=>'image/svg+xml', 'icon'=>'image.gif'),
//        'swa'  => array ('type'=>'application/x-director', 'icon'=>'flash.gif'),
//        'swf'  => array ('type'=>'application/x-shockwave-flash', 'icon'=>'flash.gif'),
//        'swfl' => array ('type'=>'application/x-shockwave-flash', 'icon'=>'flash.gif'),
//
//        'sxw'  => array ('type'=>'application/vnd.sun.xml.writer', 'icon'=>'odt.gif'),
//        'stw'  => array ('type'=>'application/vnd.sun.xml.writer.template', 'icon'=>'odt.gif'),
//        'sxc'  => array ('type'=>'application/vnd.sun.xml.calc', 'icon'=>'odt.gif'),
//        'stc'  => array ('type'=>'application/vnd.sun.xml.calc.template', 'icon'=>'odt.gif'),
//        'sxd'  => array ('type'=>'application/vnd.sun.xml.draw', 'icon'=>'odt.gif'),
//        'std'  => array ('type'=>'application/vnd.sun.xml.draw.template', 'icon'=>'odt.gif'),
//        'sxi'  => array ('type'=>'application/vnd.sun.xml.impress', 'icon'=>'odt.gif'),
//        'sti'  => array ('type'=>'application/vnd.sun.xml.impress.template', 'icon'=>'odt.gif'),
//        'sxg'  => array ('type'=>'application/vnd.sun.xml.writer.global', 'icon'=>'odt.gif'),
//        'sxm'  => array ('type'=>'application/vnd.sun.xml.math', 'icon'=>'odt.gif'),
//
//        'tar'  => array ('type'=>'application/x-tar', 'icon'=>'zip.gif'),
//        'tif'  => array ('type'=>'image/tiff', 'icon'=>'image.gif'),
//        'tiff' => array ('type'=>'image/tiff', 'icon'=>'image.gif'),
//        'tex'  => array ('type'=>'application/x-tex', 'icon'=>'text.gif'),
//        'texi' => array ('type'=>'application/x-texinfo', 'icon'=>'text.gif'),
//        'texinfo'  => array ('type'=>'application/x-texinfo', 'icon'=>'text.gif'),
//        'tsv'  => array ('type'=>'text/tab-separated-values', 'icon'=>'text.gif'),
//        'txt'  => array ('type'=>'text/plain', 'icon'=>'text.gif'),
//        'wav'  => array ('type'=>'audio/wav', 'icon'=>'audio.gif'),
//        'wmv'  => array ('type'=>'video/x-ms-wmv', 'icon'=>'avi.gif'),
//        'asf'  => array ('type'=>'video/x-ms-asf', 'icon'=>'avi.gif'),
//        'xls'  => array ('type'=>'application/vnd.ms-excel', 'icon'=>'excel.gif'),
//        'xml'  => array ('type'=>'application/xml', 'icon'=>'xml.gif'),
//        'xsl'  => array ('type'=>'text/xml', 'icon'=>'xml.gif'),
//        'zip'  => array ('type'=>'application/zip', 'icon'=>'zip.gif')
//    );
//}

/** 
 * Obtains information about a filetype based on its extension. Will
 * use a default if no information is present about that particular
 * extension.
 * @param string $element Desired information (usually 'icon' 
 *   for icon filename or 'type' for MIME type)
 * @param string $filename Filename we're looking up  
 * @return string Requested piece of information from array
 */
//function mimeinfo($element, $filename) {
//    static $mimeinfo;
//    $mimeinfo=get_mimetypes_array();
//
//    if (eregi('\.([a-z0-9]+)$', $filename, $match)) {
//        if (isset($mimeinfo[strtolower($match[1])][$element])) {
//            return $mimeinfo[strtolower($match[1])][$element];
//        } else {
//            return $mimeinfo['xxx'][$element];   // By default
//        }
//    } else {
//        return $mimeinfo['xxx'][$element];   // By default
//    }
//}

/** 
 * Obtains information about a filetype based on the MIME type rather than
 * the other way around.
 * @param string $element Desired information (usually 'icon')
 * @param string $mimetype MIME type we're looking up  
 * @return string Requested piece of information from array
 */
//function mimeinfo_from_type($element, $mimetype) {
//    static $mimeinfo;
//    $mimeinfo=get_mimetypes_array();
//    
//    foreach($mimeinfo as $values) {
//        if($values['type']==$mimetype) {
//            if(isset($values[$element])) {
//                return $values[$element];
//            }
//            break;
//        }
//    }
//    return $mimeinfo['xxx'][$element]; // Default
//}

/**
 * Obtains descriptions for file types (e.g. 'Microsoft Word document') from the 
 * mimetypes.php language file. 
 * @param string $mimetype MIME type (can be obtained using the mimeinfo function)
 * @param bool $capitalise If true, capitalises first character of result
 * @return string Text description 
 */
//function get_mimetype_description($mimetype,$capitalise=false) {
//    $result=get_string($mimetype,'mimetypes');
//    // Surrounded by square brackets indicates that there isn't a string for that
//    // (maybe there is a better way to find this out?)
//    if(strpos($result,'[')===0) {
//        $result=get_string('document/unknown','mimetypes');
//    } 
//    if($capitalise) {
//        $result=ucfirst($result);
//    }
//    return $result;
//}


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
    if (!$mimetype) {
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
 * Recursively delete the file or folder with path $location. That is, 
 * if it is a file delete it. If it is a folder, delete all its content
 * then delete it. If $location does not exist to start, that is not 
 * considered an error. 
 * 
 * @param $location the path to remove.
 */
//function fulldelete($location) {
//    if (is_dir($location)) {
//        $currdir = opendir($location);
//        while (false !== ($file = readdir($currdir))) {
//            if ($file <> ".." && $file <> ".") {
//                $fullfile = $location."/".$file;
//                if (is_dir($fullfile)) {
//                    if (!fulldelete($fullfile)) {
//                        return false;
//                    }
//                } else {
//                    if (!unlink($fullfile)) {
//                        return false;
//                    }
//                }
//            }
//        }
//        closedir($currdir);
//        if (! rmdir($location)) {
//            return false;
//        }
//
//    } else if (file_exists($location)) {
//        if (!unlink($location)) {
//            return false;
//        }
//    }
//    return true;
//}

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
 * Given a file path, retrieves the mime type of the file.
 *
 * This is only implemented for non-windows based operating systems. Mahara
 * does not support windows at this time.
 *
 * @param string $file The file to check
 * @return string      The mimetype of the file
 */
function get_mime_type($file) {
    switch (strtolower(PHP_OS)) {
        case 'win' :
            throw new SystemException('retrieving filetype not supported in windows');
        default : 
            list($output,) = split(';', exec(get_config('pathtofile') . ' -ib ' . escapeshellarg($file)));
        }
    return $output;
}

/**
 * Given a mimetype (perhaps returned by {@link get_mime_type}, returns whether
 * Mahara thinks it is a valid image file.
 *
 * Not all image types are valid for Mahara. Mahara supports JPEG, PNG and GIF.
 *
 * @param string $type The mimetype to check
 * @return boolean     Whether the type is a valid image type for Mahara
 */
function is_image_mime_type($type) {
    return in_array($type, array('image/jpeg', 'image/jpg', 'image/gif', 'image/png'));
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
