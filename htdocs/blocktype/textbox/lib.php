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
 * @subpackage blocktype-textbox
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeTextbox extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.textbox');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.textbox');
    }

    public static function get_categories() {
        return array('general');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        $text = (isset($configdata['text'])) ? $configdata['text'] : '';
        safe_require('artefact', 'file');
        $text = ArtefactTypeFolder::append_view_url($text,$instance->get('view'));
        return clean_html($text);
    }

    /**
     * Returns a list of artefact IDs that are in this blockinstance.
     *
     * People may embed artefacts as images etc. They show up as links to the 
     * download script, which isn't much to go on, but should be enough for us 
     * to detect that the artefacts are therefore 'in' this blocktype.
     */
    public static function get_artefacts(BlockInstance $instance) {
        $artefacts = array();
        $configdata = $instance->get('configdata');
        if (isset($configdata['text'])) {
            require_once(get_config('docroot') . 'artefact/lib.php');
            $artefacts = artefact_get_references_in_html($configdata['text']);
        }
        return $artefacts;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        if (!$height = get_config('blockeditorheight')) {
            $cfheight = param_integer('cfheight', 0);
            $height = $cfheight ? $cfheight * 0.7 : 150;
        }
        return array(
            'text' => array(
                'type' => 'wysiwyg',
                'title' => get_string('blockcontent', 'blocktype.textbox'),
                'width' => '100%',
                'height' => $height . 'px',
                'defaultvalue' => isset($configdata['text']) ? $configdata['text'] : '',
                'rules' => array('maxlength' => 65536),
            ),
        );
    }

    public static function default_copy_type() {
        return 'full';
    }

}
