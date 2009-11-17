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

class PluginBlocktypeFiledownload extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.file/filedownload');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.file/filedownload');
    }

    public static function get_categories() {
        return array('fileimagevideo');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        $configdata = $instance->get('configdata');

        $result = '';
        if (isset($configdata['artefactids']) && is_array($configdata['artefactids'])) {
            foreach ($configdata['artefactids'] as $artefactid) {
                try {
                    $artefact = $instance->get_artefact_instance($artefactid);
                }
                catch (ArtefactNotFoundException $e) {
                    continue;
                }

                $icondata = array(
                    'id'     => $artefactid,
                    'viewid' => $instance->get('view'),
                );

                $detailsurl = get_config('wwwroot') . 'view/artefact.php?artefact=' . $artefactid . '&view=' . $instance->get('view');
                if ($artefact instanceof ArtefactTypeProfileIcon) {
                    require_once('file.php');
                    $downloadurl = get_config('wwwroot') . 'thumb.php?type=profileiconbyid&id=' . $artefactid;
                    $size = filesize(get_dataroot_image_path('artefact/file/profileicons/', $artefactid));
                }
                else if ($artefact instanceof ArtefactTypeFile) {
                    $downloadurl = get_config('wwwroot') . 'artefact/file/download.php?file=' . $artefactid . '&view=' . $icondata['viewid'];
                    $size = $artefact->get('size');
                }

                $result .= '<div title="' . hsc($artefact->get('title')) . '">';
                $result .= '<div class="fl"><a href="' . hsc($downloadurl) . '" target="_blank">';
                $result .= '<img src="' . hsc(call_static_method(generate_artefact_class_name($artefact->get('artefacttype')), 'get_icon', $icondata))
                    . '" alt=""></a></div>';
                $result .= '<div style="margin-left: 30px;">';

                $result .= '<h4><a href="' . hsc($downloadurl) . '" target="_blank">' . str_shorten_text($artefact->get('title'), 20) . '</a></h4>';

                $description = $artefact->get('description');
                if ($description) {
                    $result .= '<p style="margin: 0;"><strong>' . hsc($description) . '</strong></p>';
                }
                $result .= '' . display_size($size) . ' | ' . strftime(get_string('strftimedaydate'),$artefact->get('ctime'));
                $result .= ' | <a href="' . hsc($detailsurl) . '">' . get_string('Details', 'artefact.file') . '</a>';
                $result .= '</div>';


                $result .= '</div>';
            }
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
            'artefactids' => self::filebrowser_element($instance, (isset($configdata['artefactids'])) ? $configdata['artefactids'] : null),
        );
    }

    public static function artefactchooser_element($default=null) {
        return array(
            'name' => 'artefactids',
            'type'  => 'artefactchooser',
            'title' => get_string('Files', 'blocktype.file/filedownload'),
            'defaultvalue' => $default,
            'blocktype' => 'filedownload',
            'limit' => 10,
            'selectone' => false,
            'artefacttypes' => array('file', 'image', 'profileicon'),
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

    public static function filebrowser_element(&$instance, $default=array()) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('Files', 'blocktype.file/filedownload');
        $element['name'] = 'artefactids';
        $element['config']['selectone'] = false;
        return $element;
    }

    public static function default_copy_type() {
        return 'full';
    }

}

?>
