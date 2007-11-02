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
 * @subpackage blocktype-contactinfo
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeContactinfo extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.internal/contactinfo');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.internal/contactinfo');
    }

    public static function get_categories() {
        return array('internal');
    }

    public static function render_instance(BlockInstance $instance) {
        require_once(get_config('docroot') . 'lib/artefact.php');
        $smarty = smarty_core();
        $configdata = $instance->get('configdata');

        $data = array();

        // Get data about the profile fields in this blockinstance
        if (!empty($configdata['artefactids'])) {
            $artefactids = implode(', ', array_map('db_quote', $configdata['artefactids']));
            $profiledata = get_records_select_array('artefact',
                'id IN (' . $artefactids . ') AND owner = (SELECT owner FROM {view} WHERE id = ?)',
                array($instance->get('view')),
                '',
                'artefacttype, title'
            );

            foreach ($profiledata as $profilefield) {
                $data[$profilefield->artefacttype] = $profilefield->title;
            }
        }

        $smarty->assign('profileinfo', $data);

        return $smarty->fetch('blocktype:contactinfo:content.tpl');
    }

    public static function get_artefacts(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $return = false;

        if (isset($configdata['artefactids'])) {
            $return = $configdata['artefactids'];
        }

        return $return;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');

        $form = array();

        // Which fields does the user want
        $form[] = self::artefactchooser_element((isset($configdata['artefactids'])) ? $configdata['artefactids'] : null);

        return $form;
    }

    // TODO: make decision on whether this should be abstract or not
    public static function artefactchooser_element($default=null) {
        safe_require('artefact', 'internal');
        return array(
            'name'  => 'artefactids',
            'type'  => 'artefactchooser',
            'title' => get_string('fieldstoshow', 'blocktype.internal/contactinfo'),
            'defaultvalue' => $default,
            'blocktype' => 'contactinfo',
            'limit'     => 655360, // 640K profile fields is enough for anyone!
            'selectone' => false,
            'artefacttypes' => PluginArtefactInternal::get_contactinfo_artefact_types(),
            'template'  => 'artefact:internal:artefactchooser-element.tpl',
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
