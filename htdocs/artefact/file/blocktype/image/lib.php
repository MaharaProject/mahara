<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
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
        return array('file', 'images');
    }

    public static function render_instance(BlockInstance $instance) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us
        $configdata['viewid'] = $instance->get('view');

        // This can be either an image or profileicon. They both implement 
        // render_self
        $result = '';
        if (isset($configdata['artefactid'])) {
            $image = artefact_instance_from_id($configdata['artefactid']);

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
                $result .= '<p>' . $description . '</p>';
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
        return array(
            self::artefactchooser_element((isset($configdata['artefactid'])) ? $configdata['artefactid'] : null),
            'showdescription' => array(
                'type'  => 'checkbox',
                'title' => get_string('showdescription', 'blocktype.file/image'),
                'defaultvalue' => $configdata['showdescription'],
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

    public static function artefactchooser_element($default=null) {
        return array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('image'),
            'defaultvalue' => $default,
            'rules' => array(
                'required' => true,
            ),
            'blocktype' => 'image',
            'limit' => 10,
            'artefacttypes' => array('image', 'profileicon'),
            'template' => 'artefact:file:artefactchooser-element.tpl',
        );
    }

    /**
     * Optional method. If specified, allows the blocktype class to munge the 
     * artefactchooser element data before it's templated
     *
     * Note: this method is the same as the one for the 'filedownload' blocktype
     */
    public static function artefactchooser_get_element_data($artefact) {
        $artefact->icon = call_static_method(generate_artefact_class_name($artefact->artefacttype), 'get_icon', array('id' => $artefact->id));
        if ($artefact->artefacttype == 'profileicon') {
            $artefact->hovertitle  =  $artefact->note;
            if ($artefact->title) {
                $artefact->hovertitle .= ': ' . $artefact->title;
            }
        }
        else {
            $artefact->hovertitle  =  $artefact->title;
            if ($artefact->description) {
                $artefact->hovertitle .= ': ' . $artefact->description;
            }
        }
        $artefact->title       = str_shorten($artefact->title, 20);
        $artefact->description = ($artefact->artefacttype == 'profileicon') ? $artefact->title : $artefact->description;

        return $artefact;
    }

}

?>
