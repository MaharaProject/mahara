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
    private $text;
    private static $standard_preview_width = 100;
    private static $one_row_height = 65;
    private static $two_row_height = 31;
    private static $three_row_height = 20;
    private static $spacer = 3;

    /* Constructor.
     * @param data  containing 'layout' (required) that consists of an array of columns per row.
     *              Example:
     *              array('row1' => '30-30-30',
     *                    'row2' => '25-25-25-25');
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

    /* Generates the preview SVG image.
     *
     * @return string SVG of the preview image.
     */
    public function create_preview() {
        $width = $this->get_preview_width();
        $height = $this->get_preview_height();
        $id = uniqid('lid');
        $layout = "<svg xmlns=http://www.w3.org/2000/svg role='img' width='{$width}' height='{$height}' aria-labelledby='title{$id} desc{$id}'>";
        if (!empty($this->text)) {
            $layout .= "<title id='title{$id}' >" . get_string('layoutpreviewimage', 'view') . "</title>";
            $layout .= "<desc id='desc{$id}'>" . hsc($this->text) . "</desc>";
        }

        $x = 0;
        $y = 0;
        $col_height = $this->get_preview_column_height();

        $class = true;
        foreach ($this->layout as $key => $row) {
            $style = 'layout' . (int)$class;
            $columns = explode(',', $row);
            foreach ($columns as $column) {
                $col_width = $this->get_percentage_column_width(count($columns), $column);
                $layout .= "<rect x='" . number_format($x, 2, '.', '') . "' y='" . number_format($y, 2, '.', '') . "' width='" . number_format($col_width, 2, '.', '') . "' height='" . number_format($col_height, 2, '.', '') . "' class='{$style}'/>";
                $x += ($col_width + self::$spacer); // increment x val for next col
            }

            $x = 0;
            $y += ($col_height + self::$spacer); // increment y val for next row
            $class = !$class;
        }

        $layout .= '</svg>';

        return $layout;
    }

    private function get_preview_height() {
        if ($this->rows == 1) {
            return self::$one_row_height;
        }
        if ($this->rows > 2) {
            return $this->rows * self::$three_row_height + ($this->rows - 1) * self::$spacer;
        }
        return $this->rows * self::$two_row_height + ($this->rows - 1) * self::$spacer;
    }

    private function get_preview_column_height() {
        if ($this->rows == 1) {
            return self::$one_row_height;
        }
        if ($this->rows > 2) {
            return self::$three_row_height;
        }
        return self::$two_row_height;
    }

    private function get_preview_width() {
        return self::$standard_preview_width;
    }

    private function get_percentage_column_width($numcols, $percent) {
        return (self::$standard_preview_width - self::$spacer * ($numcols - 1)) * $percent / 100;
    }
}
