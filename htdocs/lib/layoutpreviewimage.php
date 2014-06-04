<?php
/**
 *
 * @package    mahara
 * @subpackage flexible layouts
 * @author     Mike Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2011 Mike Kelly m.f.kelly@arts.ac.uk
 *
 */

defined('INTERNAL') || die();

/* This class is for the construction of a preview image
 * for the various custom layout choices. The image is a visual
 * representation of the layout rows/columns matrix.
 */
class LayoutPreviewImage {

    private $rows = 1;
    private $layout; // contains cols per row data
    private $description; // currently not used
    private $owner; // currently not used
    private static $standard_preview_width = 76;
    private static $one_row_height = 48;
    private static $two_row_height = 23;
    private static $three_row_height = 15;
    private static $spacer = 3;
    public static $destinationfolder = 'images/layoutpreviewthumbs';

    /* Constructor.
     * @param data  containing 'layout' (required) that consists of
     *              an array of columns per row eg.
     *              array('row1' => '30-30-30',
     *                    'row2' => '25-25-25-25');
     *              image manupulation information and place to
     *              save the resulting image file.
     *
     * The image manipulation information can be left out and
     * the pre-defined defaults will be used.
     */
    public function __construct($data = null) {

        if (empty($data)) {
            // we need at least a layout array
            throw new ParamOutOfRangeException("Required data wasn't passed to class " . get_class($this));
        }

        foreach ((array)$data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->{$field} = $value;
            }
        }

        if (!empty($this->layout)) {
            $this->rows = count($this->layout);
        }
    }

    /* Generates the preview image (.png) for a custom layout and
     * saves it in the specified $destinationfolder within
     * the $dataroot directory
     *
     * @return bool true on successful creation and saving of image.
     */
    public function create_preview() {
        global $THEME;

        $width = $this->get_preview_width();
        $height = $this->get_preview_height();
        $im = imagecreate($width,$height); // in pixels

        // maximum rows = $maxlayoutrows in View class
        $white = imagecolorallocate($im,255,255,255);
        $grey1 = imagecolorallocate($im,102,102,102);
        $grey2 = imagecolorallocate($im,77,77,77);
        $grey3 = $grey1;
        $grey4 = $grey2;
        $grey5 = $grey1;
        $grey6 = $grey2;
        $colours = array($grey1, $grey2, $grey3, $grey4, $grey5, $grey6);

        $x = 0;
        $y = 0;
        $col_height = $this->get_preview_column_height();
        $filename = 'vl-';

        foreach ($this->layout as $key => $row) {
            $columns = explode('-', $row);

            foreach ($columns as $column) {
                $col_width = $this->get_percentage_column_width(count($columns), $column);
                imagefilledrectangle($im,$x,$y,$x+$col_width,$y+$col_height,$colours[$key-1]);
                $x += ($col_width + self::$spacer); // increment x val for next col
            }

            $x = 0;
            $y += ($col_height + self::$spacer); // increment y val for next row
            $filename .= $row;    // build filename
            if ($key < count($this->layout)) {
                $filename .= '_';
            }
        }

         $filename .= '.png';

        $maxsize = get_config('maxuploadsize');
        if ($maxsize && filesize($im) > $maxsize) {
            return get_string('uploadedfiletoobig');
        }

        $dataroot = get_config('dataroot');
        $destination = $dataroot . self::$destinationfolder;

        if (!check_dir_exists($destination, true, true)) {
            throw new UploadException('Unable to create upload directory for layout preview images');
        }

        if (self::preview_exists($filename)) {
            imagedestroy($im);
            return true;
        }

        if ($madenewimage = imagepng($im, $destination . '/' . $filename) ) {
            chmod($destination . '/' . $filename, 0700);
            imagedestroy($im);
            return true;
        }

        imagedestroy($im);
        return false;
    }

    public static function preview_exists($filename) {
        return file_exists(self::$destinationfolder . '/' . $filename);
    }

    private function get_preview_height() {
        if ($this->rows == 1) {
            return self::$one_row_height;
        }
        $preview_height = ($this->rows > 2)? $this->rows * self::$three_row_height + ($this->rows-1) * self::$spacer : $this->rows * self::$two_row_height + ($this->rows-1) * self::$spacer;
        return $preview_height;
    }

    private function get_preview_column_height() {
        if ($this->rows == 1) {
            return self::$one_row_height;
        }
        $column_height = ($this->rows > 2)? self::$three_row_height : self::$two_row_height;
        return $column_height;
    }

    private function get_preview_width() {
        return self::$standard_preview_width;
    }

    private function get_equal_column_widths($numcols) {
        return (self::$standard_preview_width - self::$spacer * ($numcols-1)) / $numcols;
    }

    private function get_percentage_column_width($numcols, $percent) {
        return (self::$standard_preview_width - self::$spacer * ($numcols-1)) * $percent/100;
    }
}