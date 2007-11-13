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
 * @subpackage blocktype-resumefield
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
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

    public static function render_instance(BlockInstance $instance) {
        require_once(get_config('docroot') . 'lib/artefact.php');
        $smarty = smarty_core();
        $configdata = $instance->get('configdata');
        $configdata['viewid'] = $instance->get('view');

        // Get data about the resume field in this blockinstance
        if (!empty($configdata['artefactid'])) {
            $resumefield = artefact_instance_from_id($configdata['artefactid']);
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
            'value' => '<a href="' . get_config('wwwroot') . 'artefact/resume/">Fill out your resume</a> in order to add more fields!'
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
}

?>
