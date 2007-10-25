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
 * @subpackage blocktype-image
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
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
        return array('file');
    }

    public static function render_instance(BlockInstance $instance) {
        require_once(get_config('docroot') . 'lib/artefact.php');
        $configdata = $instance->get('configdata');

        $result = '';
        if (isset($configdata['artefactids']) && is_array($configdata['artefactids'])) {
            foreach ($configdata['artefactids'] as $artefactid) {
                $artefact = artefact_instance_from_id($artefactid);

                $icondata = array(
                    'id'   => $artefactid,
                    'view' => $instance->get('view'),
                );

                $result .= '<div title="' . hsc($artefact->get('title')) . '">';
                $result .= '<div class="fl"><img src="' . call_static_method(generate_artefact_class_name($artefact->get('artefacttype')), 'get_icon', $icondata) . '" alt=""></div>';
                $result .= '<div style="margin-left: 30px;">';

                if ($artefact instanceof ArtefactTypeProfileIcon) {
                    require_once('file.php');
                    $url = get_config('wwwroot') . 'thumb.php?type=profileiconbyid&id=' . $artefactid;
                    $size = filesize(get_dataroot_image_path('artefact/internal/profileicons/', $artefactid));
                }
                else if ($artefact instanceof ArtefactTypeFile) {
                    $url = get_config('wwwroot') . 'artefact/file/download.php?file=' . $artefactid . '&view=' . $icondata['view'];
                    $size = $artefact->get('size');
                }
                $result .= '<h4><a href="' . hsc($url) . '">' . str_shorten($artefact->get('title'), 20) . '</a></h4>';

                $description = $artefact->get('description');
                if ($description) {
                    $result .= '<p style="margin: 0;"><strong>' . hsc($description) . '</strong></p>';
                }
                $result .= '' . display_size($size) . ' | ' . strftime(get_string('strftimedaydate'),$artefact->get('ctime'));
                $result .= '</div>';


                $result .= '</div>';
            }
        }

        return $result;
    }

    public static function get_artefacts(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        if (isset($configdata['artefactids']) && is_array($configdata['artefactids'])) {
            return $configdata['artefactids'];
        }
        return false;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        return array(
            'artefactids' => array(
                'type'  => 'artefactchooser',
                'title' => get_string('Files', 'blocktype.file/filedownload'),
                'defaultvalue' => (isset($configdata['artefactids'])) ? $configdata['artefactids'] : null,
                'rules' => array(
                    'required' => true,
                ),
                'limit' => 3,
                'selectone' => false,
                'artefacttypes' => array('file', 'image', 'profileicon'),
            ),
            //'showdescription' => array(
            //    'type'  => 'checkbox',
            //    'title' => 'Show Description?',
            //    'defaultvalue' => $configdata['showdescription'],
            //),
        );
    }
}

?>
