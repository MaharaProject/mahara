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

        $viewid = $instance->get('view');
        $wwwroot = get_config('wwwroot');
        $files = array();

        if (isset($configdata['artefactids']) && is_array($configdata['artefactids'])) {
            foreach ($configdata['artefactids'] as $artefactid) {
                try {
                    $artefact = $instance->get_artefact_instance($artefactid);
                }
                catch (ArtefactNotFoundException $e) {
                    continue;
                }

                $file = array(
                    'id' => $artefactid,
                    'title' => $artefact->get('title'),
                    'description' => $artefact->get('description'),
                    'size' => $artefact->get('size'),
                    'ctime' => $artefact->get('ctime'),
                    'iconsrc' => call_static_method(
                        generate_artefact_class_name($artefact->get('artefacttype')),
                        'get_icon',
                        array('id' => $artefactid, 'viewid' => $viewid)
                    ),
                    'downloadurl' => $wwwroot,
                );

                if ($artefact instanceof ArtefactTypeProfileIcon) {
                    $file['downloadurl'] .= 'thumb.php?type=profileiconbyid&id=' . $artefactid;
                }
                else if ($artefact instanceof ArtefactTypeFile) {
                    $file['downloadurl'] .= 'artefact/file/download.php?file=' . $artefactid . '&view=' . $viewid;
                }

                $files[] = $file;
            }
        }

        $smarty = smarty_core();
        $smarty->assign('viewid', $instance->get('view'));
        $smarty->assign('files', $files);
        return $smarty->fetch('blocktype:filedownload:filedownload.tpl');
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
