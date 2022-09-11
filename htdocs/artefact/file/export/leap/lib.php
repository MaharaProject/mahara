<?php
/**
 * Leap file exports
 *
 * @package    mahara
 * @subpackage artefact-file-export-leap
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/*
 * For more information about file LEAP export, see:
 * https://wiki.mahara.org/wiki/Developer_Area/Import//Export/LEAP_Export/File_Artefact_Plugin
 */

defined('INTERNAL') || die();

/**
 * Leap Export File class
 */
class LeapExportElementFile extends LeapExportElement {

    private $filename;

    /**
     * {@inheritDoc}
     */
    public function get_leap_type() {
        return 'resource';
    }

    public function get_categories() {
        return array(
            array(
                'scheme' => 'resource_type',
                'term'   => 'Offline',
                'label'  => 'File',
            )
        );
    }

    public function assign_smarty_vars() {
        parent::assign_smarty_vars();
        $this->smarty->assign('summary', $this->artefact->get('description'));
    }

    public function add_attachments() {
        $this->filename = $this->exporter->add_attachment($this->artefact->get_path(), $this->artefact->get('id') . '-' . $this->artefact->get('title'));
        $this->add_enclosure_link($this->filename, $this->get_content_type());
    }

    public function get_content_type() {
        return $this->artefact->get('filetype');
    }

    public function get_content() {
        return '';
    }
}

/**
 * Leap Export Folder class
 */
class LeapExportElementFolder extends LeapExportElement {

    public function get_leap_type() {
        return 'selection';
    }

    public function get_categories() {
        return array(
            array(
                'scheme' => 'selection_type',
                'term'   => 'Folder',
            )
        );
    }
}

/**
 * LeapExport Image class
 */
class LeapExportElementImage extends LeapExportElementFile { }
/**
 * LeapExport Video class
 */
class LeapExportElementVideo extends LeapExportElementFile { }
/**
 * LeapExport Audio class
 */
class LeapExportElementAudio extends LeapExportElementFile { }
/**
 * LeapExport ProfileIcon class
 */
class LeapExportElementProfileIcon extends LeapExportElementFile {

    public function add_links() {
        parent::add_links();
        $this->add_generic_link('artefactinternal', 'related');
    }

}

/**
 * LeapExport Archive class
 */
class LeapExportElementArchive extends LeapExportElementFile { }
