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
        $style = isset($configdata['style']) ? intval($configdata['style']) : 0;
        switch ($style) {
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
                $width = isset($configdata['width']) ? $configdata['width'] : 75;
                break;
            case 1: // slideshow
                $template = 'slideshow';
                $width = isset($configdata['width']) ? $configdata['width'] : 400;
                break;
        }

        $artefactids = array();
        if (isset($configdata['select']) && $configdata['select'] == 1 && is_array($configdata['artefactids'])) {
            $artefactids = $configdata['artefactids'];
        }
        else if (!empty($configdata['artefactid'])) {
            // Get descendents of this folder.
            $artefactids = artefact_get_descendants(array(intval($configdata['artefactid'])));
        }

        // This can be either an image or profileicon. They both implement
        // render_self
        $images = array();
        foreach ($artefactids as $artefactid) {
            $image = $instance->get_artefact_instance($artefactid);

            if ($image instanceof ArtefactTypeProfileIcon) {
                $src = get_config('wwwroot') . 'thumb.php?type=profileiconbyid&id=' . $artefactid;
                $description = $image->get('title');
            }
            else if ($image instanceof ArtefactTypeImage) {
                $src = get_config('wwwroot') . 'artefact/file/download.php?file=' . $artefactid;
                $src .= '&view=' . $instance->get('view');
                $description = $image->get('description');
            }
            else {
                continue;
            }

            $src .= '&maxwidth=' . $width;

            $images[] = array(
                'link' => get_config('wwwroot') . 'view/artefact.php?artefact=' .
                    $artefactid . '&view=' . $instance->get('view'),
                'source' => $src,
                'title' => $image->get('title'),
            );
        }

        $smarty = smarty_core();
        $smarty->assign('instanceid', $instance->get('id'));
        $smarty->assign('count', count($images));
        $smarty->assign('images', $images);
        $smarty->assign('width', $width);

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
            0 => get_string('selectfolder', 'blocktype.file/gallery'),
            1 => get_string('selectimages', 'blocktype.file/gallery'),
        );
        $style_options = array(
            0 => get_string('stylethumbs', 'blocktype.file/gallery'),
            1 => get_string('styleslideshow', 'blocktype.file/gallery'),
        );
        if (isset($configdata['select']) && $configdata['select'] == 1) {
            $imageids = isset($configdata['artefactids']) ? $configdata['artefactids'] : array();
            $imageselector = self::imageselector($instance, $imageids);
            $folderselector = self::folderselector($instance, null, 'hidden');
        }
        else {
            $imageselector = self::imageselector($instance, null, 'hidden');
            $folderid = !empty($configdata['artefactid']) ? array(intval($configdata['artefactid'])) : null;
            $folderselector = self::folderselector($instance, $folderid);
        }
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
            'images' => $imageselector,
            'folder' => $folderselector,
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
        if ($values['select'] == 0) {
            $values['artefactid'] = $values['folder'];
            unset($values['artefactids']);
        }
        else if ($values['select'] == 1) {
            $values['artefactids'] = $values['images'];
            unset($values['artefactid']);
        }
        unset($values['folder']);
        unset($values['images']);
        return $values;
    }

    public static function imageselector(&$instance, $default=array(), $class=null) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('Images', 'artefact.file');
        $element['name'] = 'images';
        if ($class) {
            $element['class'] = $class;
        }
        $element['config']['selectone'] = false;
        $element['filters'] = array(
            'artefacttype'    => array('image'),
        );
        return $element;
    }

    public static function folderselector(&$instance, $default=array(), $class=null) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('folder', 'artefact.file');
        $element['name'] = 'folder';
        if ($class) {
            $element['class'] = $class;
        }
        $element['config']['upload'] = false;
        $element['config']['selectone'] = true;
        $element['config']['selectfolders'] = true;
        $element['filters'] = array(
            'artefacttype'    => array('folder'),
        );
        return $element;
    }

    public static function artefactchooser_element($default=null) {
    }

    public static function default_copy_type() {
        return 'full';
    }
}
