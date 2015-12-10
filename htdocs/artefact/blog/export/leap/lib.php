<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-blog-export-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/*
 * For more information about blog LEAP export, see:
 * https://wiki.mahara.org/wiki/Developer_Area/Import//Export/LEAP_Export/Blog_Artefact_Plugin
 */

defined('INTERNAL') || die();

class LeapExportElementBlogpost extends LeapExportElement {

    public function get_content_type() {
        return 'html';
    }

    public function get_categories() {
        if (!$this->artefact->get('published')) {
            return array(
                array(
                    'scheme' => 'readiness',
                    'term'   => 'Unready',
                )
            );
        }
        return array();
    }
}

class LeapExportElementBlog extends LeapExportElement {

    public function get_leap_type() {
        return 'selection';
    }

    public function get_categories() {
        return array(
            array(
                'scheme' => 'selection_type',
                'term'   => 'Blog',
            )
        );
    }

    public function get_content_type() {
        return 'html';
    }
}
