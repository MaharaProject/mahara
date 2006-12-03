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

class ArtefactTypeFolder extends ArtefactType {
    public function commit() {
        $this->commit_basic();
    }
    
    public function delete() {
        $this->delete_basic();
    }

    public function render($format, $options) {

    }

    public function get_icon() {

    }

    public static function get_render_list() {

    }
    
    public static function collapse_config() {
        return 'file';
    }
    
}

class ArtefactTypeFile extends ArtefactType {

    public function commit() {
        $this->commit_basic();
    }
    
    public function delete() {
        $this->delete_basic();
    }

    public function render($format, $options) {

    }

    public function get_icon() {

    }

    public static function get_render_list() {
        return array(FORMAT_ARTEFACT_LISTSELF, FORMAT_ARTEFACT_RENDERMETADATA);
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        return array(); // @todo  
    }
}
class ArtefactTypeImage extends ArtefactTypeFile {
    
    public static function collapse_config() {
        return 'file';
    }

    public static function get_render_list() {
        return array(FORMAT_ARTEFACT_LISTSELF, FORMAT_ARTEFACT_RENDERFULL, FORMAT_ARTEFACT_RENDERMETADATA);
    }

}

?>
