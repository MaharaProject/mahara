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
 * @subpackage blocktype-image
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeImage extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.file/image');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.file/image');
    }

    public static function get_categories() {
        return array('fileimagevideo');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us
        $configdata['viewid'] = $instance->get('view');

        // This can be either an image or profileicon. They both implement 
        // render_self
        $result = '';
        if (isset($configdata['artefactid'])) {
            $image = $instance->get_artefact_instance($configdata['artefactid']);

            if ($image instanceof ArtefactTypeProfileIcon) {
                $src = get_config('wwwroot') . 'thumb.php?type=profileiconbyid&id=' . $configdata['artefactid'];
                $description = $image->get('title');
            }
            else {
                $src = get_config('wwwroot') . 'artefact/file/download.php?file=' . $configdata['artefactid'];
                $src .= '&view=' . $instance->get('view');
                $description = $image->get('description');
            }

            if (!empty($configdata['width'])) {
                $src .= '&maxwidth=' . $configdata['width'];
            }

            $result  = '<div class="center"><div>';
            $result .= '<a href="' . get_config('wwwroot') . 'view/artefact.php?artefact=' . $configdata['artefactid'] . '&view=' . $instance->get('view') . '"><img src="' . hsc($src) . '" alt="' . hsc($description) .'"></a>';
            $result .= '</div>';

            $description = (is_a($image, 'ArtefacttypeImage')) ? $image->get('description') : $image->get('title');
            if (!empty($configdata['showdescription']) && $description) {
                $result .= '<p>' . hsc($description) . '</p>';
            }
            $result .= '</div>';
        }

        return $result;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        safe_require('artefact', 'file');
        $instance->set('artefactplugin', 'file');
        return array(
            'artefactid' => self::filebrowser_element($instance, (isset($configdata['artefactid'])) ? array($configdata['artefactid']) : null),
            'showdescription' => array(
                'type'  => 'checkbox',
                'title' => get_string('showdescription', 'blocktype.file/image'),
                'defaultvalue' => !empty($configdata['showdescription']) ? true : false,
            ),
            'width' => array(
                'type' => 'text',
                'title' => get_string('width', 'blocktype.file/image'),
                'size' => 3,
                'description' => get_string('widthdescription', 'blocktype.file/image'),
                'rules' => array(
                    'minvalue' => 16,
                    'maxvalue' => get_config('imagemaxwidth'),
                ),
                'defaultvalue' => (isset($configdata['width'])) ? $configdata['width'] : '',
            ),
        );
    }

    public static function filebrowser_element(&$instance, $default=array()) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('image');
        $element['name'] = 'artefactid';
        $element['config']['selectone'] = true;
        $element['filters'] = array(
            'artefacttype'    => array('image'),
        );
        return $element;
    }

    public static function artefactchooser_element($default=null) {
        return array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('image'),
            'defaultvalue' => $default,
            'blocktype' => 'image',
            'limit' => 10,
            'artefacttypes' => array('image', 'profileicon'),
            'template' => 'artefact:file:artefactchooser-element.tpl',
        );
    }

    /**
     * Optional method. If specified, allows the blocktype class to munge the 
     * artefactchooser element data before it's templated
     */
    public static function artefactchooser_get_element_data($artefact) {
        return ArtefactTypeFileBase::artefactchooser_get_file_data($artefact);
    }

    public static function default_copy_type() {
        return 'full';
    }

}

?>
