<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage blocktype-resumefield
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeResumefield extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.resume/resumefield');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.resume/resumefield');
    }

    public static function get_categories() {
        return array('resume');
    }

    public static function get_viewtypes() {
        return array('portfolio', 'profile');
    }

     /**
     * Optional method. If exists, allows this class to decide the title for
     * all blockinstances of this type
     */
    public static function get_instance_title(BlockInstance $bi) {
        $configdata = $bi->get('configdata');

        if (!empty($configdata['artefactid'])) {
            require_once(get_config('docroot') . 'artefact/lib.php');
            $resumefield = artefact_instance_from_id($configdata['artefactid']);
            return $resumefield->get('title');
        }
        return '';
    }

    public static function render_instance(BlockInstance $instance) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        $smarty = smarty_core();
        $configdata = $instance->get('configdata');
        $configdata['viewid'] = $instance->get('view');

        // Get data about the resume field in this blockinstance
        if (!empty($configdata['artefactid'])) {
            $resumefield = $instance->get_artefact_instance($configdata['artefactid']);
            $rendered = $resumefield->render_self($configdata);
            $result = $rendered['html'];
            if (!empty($rendered['javascript'])) {
                $result .= '<script type="text/javascript">' . $rendered['javascript'] . '</script>';
            }
            return $result;
        }
        return '';
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');

        $form = array();

        // Which resume field does the user want
        $form[] = self::artefactchooser_element((isset($configdata['artefactid'])) ? $configdata['artefactid'] : null);
        $form['message'] = array(
            'type' => 'html',
            'value' => get_string('filloutyourresume', 'blocktype.resume/resumefield', '<a href="' . get_config('wwwroot') . 'artefact/resume/">', '</a>'),
        );

        return $form;
    }

    public static function instance_config_save($values) {
        unset($values['message']);
        return $values;
    }

    // TODO: make decision on whether this should be abstract or not
    public static function artefactchooser_element($default=null) {
        safe_require('artefact', 'resume');
        return array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('fieldtoshow', 'blocktype.resume/resumefield'),
            'defaultvalue' => $default,
            'blocktype' => 'resumefield',
            'limit'     => 655360, // 640K profile fields is enough for anyone!
            'selectone' => true,
            'search'    => false,
            'artefacttypes' => PluginArtefactResume::get_artefact_types(),
            'template'  => 'artefact:resume:artefactchooser-element.tpl',
        );
    }

    /**
     * Deliberately enforce _no_ sort order. The database will return them in 
     * the order they were inserted, which means roughly the order that they 
     * are listed in the profile screen
     */
    public static function artefactchooser_get_sort_order() {
        return '';
    }

    public static function copy_allowed($ownertype=null) {
        return $ownertype == 'user';
    }

    public static function copy_artefacts_allowed($newowner=null) {
        return false;
    }

    public static function default_artefact_config($ownertype=null, $ownerid=null, $configdata) {
        $artefactid = null;
        if ($ownertype == 'user') {
            if (!empty($configdata['artefactid'])) {
                $artefacttype = get_field('artefact', 'artefacttype', 'id', $configdata['artefactid']);
            }
            // @todo get artefacttype from a different field when copying from institution or group view.
            if ($artefacttype) {
                $artefactid = get_field('artefact', 'id', 'artefacttype', $artefacttype, 'owner', $ownerid);
            }
        }
        $configdata['artefactid'] = $artefactid;
        return $configdata;
    }

}

?>
