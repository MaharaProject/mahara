<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Mike Kelly UAL <m.f.kelly@arts.ac.uk> Adapted from a class by Jarrod Oberto
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class ImageResizer {

    private $image;
    private $width;
    private $height;
    private $imageresized;

    function __construct($filename, $mimetype){
        $this->image = $this->open_image($filename, $mimetype);
        if (!empty($this->image)) {
            $this->width  = imagesx($this->image);
            $this->height = imagesy($this->image);
        }
    }

    private function open_image($file, $mimetype){
        if (!$this->set_memory_for_image($file)) {
            return;
        }
        switch ($mimetype) {
            case 'image/jpg':
            case 'image/jpeg':
                $img = imagecreatefromjpeg($file);
                break;
            case 'image/gif':
                $img = imagecreatefromgif($file);
                break;
            case 'image/png':
                $img = imagecreatefrompng($file);
                break;
            default:
                $img = false;
            break;
        }
        return $img;
    }

    public function resize_image($size, $mimetype, $option='auto'){
        // Get optimal width and height based on $size
        $newwidth = $size['w'];
        $newheight = $size['h'];
        $optionarray = $this->get_dimensions($newwidth, $newheight, $option);
        $optimalwidth  = $optionarray['optimalwidth'];
        $optimalheight = $optionarray['optimalheight'];

        $this->imageresized = imagecreatetruecolor($optimalwidth, $optimalheight);
        if ($mimetype == 'image/png' || $mimetype == 'image/gif') {
           // Create a new destination image which is completely
           // transparent and turn off alpha blending for it, so that when
           // the PNG source file is copied, the alpha channel is retained
           // Thanks to http://alexle.net/archives/131
           $background = imagecolorallocate($this->imageresized, 0, 0, 0);
           imagecolortransparent($this->imageresized, $background);
           imagealphablending($this->imageresized, false);
           imagecopyresampled($this->imageresized, $this->image, 0, 0, 0, 0, $optimalwidth, $optimalheight, $this->width, $this->height);
           imagesavealpha($this->imageresized, true);
        }
        else {
           imagecopyresampled($this->imageresized, $this->image, 0, 0, 0, 0, $optimalwidth, $optimalheight, $this->width, $this->height);
        }

        if ($option == 'crop') {
            $this->crop($optimalwidth, $optimalheight, $newwidth, $newheight);
        }
        imagedestroy($this->image);
    }

    private function get_dimensions($newwidth, $newheight, $option){

        switch ($option) {
            case 'exact':
                $optimalwidth = $newwidth;
                $optimalheight= $newheight;
                break;
            case 'byheight':
                $optimalwidth = $this->get_size_by_fixed_height($newheight);
                $optimalheight= $newheight;
                break;
            case 'bywidth':
                $optimalwidth = $newwidth;
                $optimalheight= $this->get_size_by_fixed_width($newwidth);
                break;
            case 'crop':
                $optionarray = $this->get_optimal_crop($newwidth, $newheight);
                $optimalwidth = $optionarray['optimalwidth'];
                $optimalheight = $optionarray['optimalheight'];
                break;
            case 'auto':
                default:
                $optionarray = $this->get_size_by_auto($newwidth, $newheight);
                $optimalwidth = $optionarray['optimalwidth'];
                $optimalheight = $optionarray['optimalheight'];
                break;
        }

        if ($option == 'auto' && ($optimalheight > $newheight || $optimalwidth > $newwidth)) {
            // First attempt resize calculation not within requested limits. Find errant dimension and resize by that dimension.
            if ($optimalheight > $newheight) {
                $optionarray = $this->get_dimensions($newwidth, $newheight, 'byheight');
            }
            else {
                $optionarray = $this->get_dimensions($newwidth, $newheight, 'bywidth');
            }
            $optimalwidth  = $optionarray['optimalwidth'];
            $optimalheight = $optionarray['optimalheight'];
        }
        return array('optimalwidth' => $optimalwidth, 'optimalheight' => $optimalheight);
    }

    private function get_size_by_fixed_height($newheight){
        $ratio = $this->width / $this->height;
        $newwidth = $newheight * $ratio;
        return $newwidth;
    }

    private function get_size_by_fixed_width($newwidth){
        $ratio = $this->height / $this->width;
        $newheight = $newwidth * $ratio;
        return $newheight;
    }

    private function get_size_by_auto($newwidth, $newheight){
        if ($this->height < $this->width) {
            // Image to be resized is wider (landscape)
            $optimalwidth = $newwidth;
            $optimalheight= $this->get_size_by_fixed_width($newwidth);
        }
        else if ($this->height > $this->width) {
            // Image to be resized is taller (portrait)
            $optimalwidth = $this->get_size_by_fixed_height($newheight);
            $optimalheight= $newheight;
        }
        else {
            // Image to be resized is a square
            if ($newheight < $newwidth) {
                $optimalwidth = $newwidth;
                $optimalheight= $this->get_size_by_fixed_width($newwidth);
            }
            else if ($newheight > $newwidth) {
                $optimalwidth = $this->get_size_by_fixed_height($newheight);
                $optimalheight= $newheight;
            }
            else {
                // Square being resized to a square
                $optimalwidth = $newwidth;
                $optimalheight= $newheight;
            }
        }

        return array('optimalwidth' => $optimalwidth, 'optimalheight' => $optimalheight);
    }

    private function get_optimal_crop($newwidth, $newheight){

        $heightratio = $this->height / $newheight;
        $widthRatio  = $this->width /  $newwidth;

        if ($heightratio < $widthRatio) {
            $optimalratio = $heightratio;
        }
        else {
            $optimalratio = $widthRatio;
        }

        $optimalheight = $this->height / $optimalratio;
        $optimalwidth  = $this->width  / $optimalratio;

        return array('optimalwidth' => $optimalwidth, 'optimalheight' => $optimalheight);
    }

    private function crop($optimalwidth, $optimalheight, $newwidth, $newheight){
        // Find center - this will be used for the crop
        $cropstartx = ($optimalwidth / 2) - ($newwidth /2);
        $cropstarty = ($optimalheight/ 2) - ($newheight/2);

        $crop = $this->imageresized;

        // Now crop from center to exact requested size
        $this->imageresized = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($this->imageresized, $crop , 0, 0, $cropstartx, $cropstarty, $newwidth, $newheight , $newwidth, $newheight);
    }

    public function save_image($savepath, $mimetype, $imagequality='100') {
        $saved = false;
        switch ($mimetype) {
            case 'image/jpg':
            case 'image/jpeg':
                if (imagetypes() & IMG_JPG) {
                    $saved = imagejpeg($this->imageresized, $savepath, $imagequality);
                }
            break;
            case 'image/gif':
                if (imagetypes() & IMG_GIF) {
                    $saved = imagegif($this->imageresized, $savepath);
                }
            break;
            case 'image/png':
                // Scale quality from 0-100 to 0-9
                $scalequality = round(($imagequality/100) * 9);
                // Invert quality setting as 0 is best, not 9
                $invertscalequality = 9 - $scalequality;
                if (imagetypes() & IMG_PNG) {
                    $saved = imagepng($this->imageresized, $savepath, $invertscalequality);
                }
                break;
                default:
                // No extension, no save.
                break;
        }
        imagedestroy($this->imageresized);
        return $saved;
    }

    private function set_memory_for_image($filename){
        // See http://uk3.php.net/manual/en/function.imagecreatefromjpeg.php#64155
        $imageinfo = getimagesize($filename);
        $mimetype = $imageinfo['mime'];
        if (isset($imageinfo['bits'])) {
            $bits = $imageinfo['bits'];
        }
        else if ($mimetype == 'image/gif') {
            $bits = 8;
        }
        if (isset($imageinfo['channels'])) {
            $channels = $imageinfo['channels'];
        }
        else {
            // possible vals are 3 or 4
            $channels = 4;
        }

        if (isset($imageinfo[0]) && isset($imageinfo[1]) && !empty($bits)) {
            $MB = 1048576;  // number of bytes in 1M
            $K64 = 65536;   // number of bytes in 64K
            $TWEAKFACTOR = 1.8;
            $memoryneeded = round(( $imageinfo[0] * $imageinfo[1]
                                                  * $bits
                                                  * $channels / 8
                                    + $K64
                                  ) * $TWEAKFACTOR
                                 );

            if ($memoryneeded > get_config('maximageresizememory')) {
                log_debug("Refusing to set memory for resize of large image $filename $mimetype "
                . $imageinfo[0] . 'x' .  $imageinfo[1] . ' ' . $imageinfo['bits'] . '-bit');
                return false;
            }
        }

        if (function_exists('memory_get_usage') && memory_get_usage() && !empty($memoryneeded)) {
            $newlimit = memory_get_usage() + $memoryneeded;
            if ($newlimit > get_config('maximageresizememory')) {
                log_debug("Refusing to set memory for resize of large image $filename $mimetype "
                . $imageinfo[0] . 'x' .  $imageinfo[1] . ' ' . $imageinfo['bits'] . '-bit');
                return false;
            }
            $newlimitMB = ceil((memory_get_usage() + $memoryneeded) / $MB);
            raise_memory_limit($newlimitMB . 'M');
            return true;
        }
        else {
            return false;
        }
    }

    public function get_image(){
        return $this->image;
    }
}
