<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @subpackage artefact-file-export-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/*
 * For more information about file LEAP export, see:
 * http://wiki.mahara.org/Developer_Area/Import//Export/LEAP_Export/File_Artefact_Plugin
 */

defined('INTERNAL') || die();

class LeapExportElementFile extends LeapExportElement {

    private $filename;

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
        $this->filename = $this->exporter->add_attachment($this->artefact->get_path(), $this->artefact->get('title'));
        $this->add_enclosure_link($this->filename, $this->get_content_type());
    }

    public function get_content_type() {
        return $this->artefact->get('filetype');
    }

    public function get_content() {
        return '';
    }
}

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

class LeapExportElementImage extends LeapExportElementFile { }
class LeapExportElementProfileIcon extends LeapExportElementFile {

    public function add_links() {
        parent::add_links();
        $this->add_generic_link('artefactinternal', 'related');
    }

}

class LeapExportElementArchive extends LeapExportElementFile { }
