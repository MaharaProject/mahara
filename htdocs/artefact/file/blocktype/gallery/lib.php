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
 * @subpackage blocktype-slideshow
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeGallery extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.file/gallery');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.file/gallery');
    }

    public static function get_categories() {
        return array('fileimagevideo');
    }

    public static function get_instance_javascript(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        switch ($configdata['style']) {
            case 0: // thumbnails
                return array();
            case 1: // slideshow
                return array('js/slideshow.js');
        }
    }

    public static function get_instance_config_javascript() {
        return array('js/configform.js');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us
        $configdata['viewid'] = $instance->get('view');
        $style = isset($configdata['style']) ? intval($configdata['style']) : 0;
        switch ($style) {
            case 0: // thumbnails
                $template = 'thumbnails';
                break;
            case 1: // slideshow
                $template = 'slideshow';
                break;
        }

        // This can be either an image or profileicon. They both implement
        // render_self
        $images = array();
        if (isset($configdata['artefactids'])) {
            foreach ($configdata['artefactids'] as $artefactid) {
                $image = $instance->get_artefact_instance($artefactid);

                if ($image instanceof ArtefactTypeProfileIcon) {
                    $src = get_config('wwwroot') . 'thumb.php?type=profileiconbyid&id=' . $artefactid;
                    $description = $image->get('title');
                }
                else {
                    $src = get_config('wwwroot') . 'artefact/file/download.php?file=' . $artefactid;
                    $src .= '&view=' . $instance->get('view');
                    $description = $image->get('description');
                }

                if (!empty($configdata['width'])) {
                    $src .= '&maxwidth=' . $configdata['width'];
                }

                $images[] = array(
                    'link' => get_config('wwwroot') . 'view/artefact.php?artefact=' .
                        $artefactid . '&view=' . $instance->get('view'),
                    'source' => hsc($src),
                    'title' => $image->get('title'),
                );
            }
        }

        $smarty = smarty_core();
        $smarty->assign('instanceid', $instance->get('id'));
        $smarty->assign('count', count($images));
        $smarty->assign('images', $images);

        return $smarty->fetch('blocktype:gallery:' . $template . '.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        safe_require('artefact', 'file');
        $instance->set('artefactplugin', 'file');
        $user = $instance->get('view_obj')->get('owner');
        $select_options = array(
            0 => get_string('selectall', 'blocktype.file/gallery'),
            1 => get_string('selectchoose', 'blocktype.file/gallery'),
        );
        $style_options = array(
            0 => get_string('stylethumbs', 'blocktype.file/gallery'),
            1 => get_string('styleslideshow', 'blocktype.file/gallery'),
        );
        return array(
            'user' => array(
                'type' => 'hidden',
                'value' => $user,
            ),
            'select' => array(
                'type' => 'radio',
                'title' => get_string('select', 'blocktype.file/gallery'),
                'options' => $select_options,
                'defaultvalue' => (isset($configdata['select'])) ? $configdata['select'] : 0,
                'separator' => '<br>',
            ),
            'artefactids' => self::filebrowser_element($instance, (isset($configdata['artefactids'])) ? $configdata['artefactids'] : null),
            'style' => array(
                'type' => 'radio',
                'title' => get_string('style', 'blocktype.file/gallery'),
                'options' => $style_options,
                'defaultvalue' => (isset($configdata['style'])) ? $configdata['style'] : 0,
                'separator' => '<br>',
            ),
            'width' => array(
                'type' => 'text',
                'title' => get_string('width', 'blocktype.file/gallery'),
                'size' => 3,
                'description' => get_string('widthdescription', 'blocktype.file/gallery'),
                'rules' => array(
                    'minvalue' => 16,
                    'maxvalue' => get_config('imagemaxwidth'),
                ),
                'defaultvalue' => (isset($configdata['width'])) ? $configdata['width'] : '75',
            ),
        );
    }

    public static function instance_config_save($values) {
        // modify artefactids accordingly if the user wants all their images
        if ($values['select'] == 0) {
            $user = $values['user'];
            $userimages = get_records_sql_array("
                SELECT im.artefact
                FROM {artefact} a, {artefact_file_image} im
                WHERE a.id = im.artefact
                AND a.owner = ?;",
                array($user));
            $values['artefactids'] = array();
            if ($userimages) {
                foreach ($userimages as $image) {
                    $values['artefactids'][] = $image->artefact;
                }
            }
        }
        return $values;
    }

    public static function filebrowser_element(&$instance, $default=array()) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('image');
        $element['name'] = 'artefactids';
        $element['config']['selectone'] = false;
        $element['filters'] = array(
            'artefacttype'    => array('image'),
        );
        return $element;
    }

    public static function artefactchooser_element($default=null) {
    }

    public static function default_copy_type() {
        return 'full';
    }
}
