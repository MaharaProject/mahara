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
 * @subpackage artefact-internal
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginArtefactFile extends PluginArtefact {

    public static function get_artefact_types() {
        return array(
            'file',
            'folder',
            'image',
        );
    }

    public static function get_plugin_name() {
        return 'file';
    }

    public static function menu_items() {
        return array(
            array(
                'name' => 'myfiles',
                'link' => '',
            )
        );
    }
    
    public static function get_toplevel_artefact_types() {
        return array('file');
    }

    public static function postinst() {
    }
    
    public static function sort_child_data($a, $b) {
        if ($a->container && !$b->container) {
            return -1;
        }
        else if (!$a->container && $b->container) {
            return 1;
        }
        return strnatcasecmp($a->text, $b->text);
    }

}

class ArtefactTypeFileBase extends ArtefactType {

    public function render($format, $options) {
        switch ($format) {
        case FORMAT_ARTEFACT_LISTSELF:
            return $this->title;
        case FORMAT_ARTEFACT_RENDERMETADATA:
            return $this->render_metadata($options);
        }
        //@todo: This should be an invalid render format exception
        throw new Exception('invalid render format');
    }

    public static function get_render_list() {
        return array(FORMAT_ARTEFACT_LISTSELF, FORMAT_ARTEFACT_RENDERMETADATA);
    }

    public static function is_0_or_1() {
        return false;
    }

    public function get_icon() {

    }

    public static function collapse_config() {
        return 'file';
    }

    public function delete() {
        if (empty($this->id)) {
            return; 
        }
        delete_records('artefact_file_files', 'artefact', $this->id);
        // @todo: Delete the file from the filesystem 
        parent::delete();
    }

}

class ArtefactTypeFile extends ArtefactTypeFileBase {

    public static function has_config() {
        return true;
    }

    public function get_icon() {

    }

    public static function get_config_options() {
        return array(); // @todo  
    }
}

class ArtefactTypeFolder extends ArtefactTypeFileBase {

    public function render($format, $options) {
        switch ($format) {
        case FORMAT_ARTEFACT_LISTSELF:
            return $this->title;
        case FORMAT_ARTEFACT_LISTCHILDREN:
            return $this->listchildren($options);
        case FORMAT_ARTEFACT_RENDERMETADATA:
            return $this->render_metadata($options);
        case FORMAT_ARTEFACT_RENDERFULL:
            return '';
        }
        //@todo: This should be an invalid render format exception
        throw new Exception('invalid render format');
    }

    public function get_icon() {

    }

    public static function collapse_config() {
        return 'file';
    }
    
    public static function get_render_list() {
        return array(FORMAT_ARTEFACT_LISTSELF, FORMAT_ARTEFACT_LISTCHILDREN,
                     FORMAT_ARTEFACT_RENDERFULL, FORMAT_ARTEFACT_RENDERMETADATA);
    }
    
}

class ArtefactTypeImage extends ArtefactTypeFile {
    
    public static function collapse_config() {
        return 'file';
    }

    public function render($format, $options) {
        switch ($format) {
        case FORMAT_ARTEFACT_LISTSELF:
            return $this->title;
        case FORMAT_ARTEFACT_RENDERMETADATA:
            return $this->render_metadata($options);
        case FORMAT_ARTEFACT_RENDERFULL:
            return 'image';
        }
        //@todo: This should be an invalid render format exception
        throw new Exception('invalid render format');
    }

    public static function get_render_list() {
        return array(FORMAT_ARTEFACT_LISTSELF, FORMAT_ARTEFACT_RENDERFULL, 
                     FORMAT_ARTEFACT_RENDERMETADATA);
    }

}

?>
