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
 * @subpackage blocktype-textbox
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
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

    public static function render_instance(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $text = (isset($configdata['text'])) ? $configdata['text'] : '';
        return $text;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        return array(
            'text' => array(
                'type' => 'tinywysiwyg',
                'width' => '90%',
                'height' => '150px',
                'defaultvalue' => $configdata['text'],
            ),
        );
    }

    public static function instance_config_save($values) {
        global $USER;
        if (!get_account_preference($USER->get('id'), 'wysiwyg')) {
            $values['text'] = format_whitespace($values['text']);
        }
        else {
            $values['text'] = clean_text($values['text']);
        }
        return $values;
    }

}

?>
