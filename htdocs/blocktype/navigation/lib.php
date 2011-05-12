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

class PluginBlocktypeNavigation extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.navigation');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.navigation');
    }

    public static function get_categories() {
        return array('general');
    }

     /**
     * Optional method. If exists, allows this class to decide the title for
     * all blockinstances of this type
     */
    public static function get_instance_title(BlockInstance $bi) {
        $configdata = $bi->get('configdata');

        if (!empty($configdata['collection'])) {
            return $bi->get_data('collection', $configdata['collection'])->get('name');
        }
        return '';
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        $smarty = smarty_core();

        if (!empty($configdata['collection'])) {
            $views = $instance->get_data('collection', $configdata['collection'])->views();
            if (!empty($views)) {
                $smarty->assign('views', $views['views']);
            }
        }
        $smarty->assign('currentview',$instance->get('view'));
        return $smarty->fetch('blocktype:navigation:navigation.tpl');
    }

    // Called by $instance->get_data('collection', ...).
    public static function get_instance_collection($id) {
        require_once('collection.php');
        return new Collection($id);
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');

        $userid = $instance->get_view()->get('owner');
        ($collections = get_records_sql_array("
            SELECT c.id, c.name
                FROM {collection} c
            WHERE c.owner = ?
            ORDER BY c.name, c.ctime ASC", array($userid)))
            || ($collections = array());

        $default = false;
        $options = array();
        if (!empty($collections)) {
            foreach ($collections as $collection) {
                if (!$default) { // need to have an initially selected item
                    $default = $collection->id;
                }
                $options[$collection->id] = $collection->name;
            }

            return array(
                'collection' => array(
                    'type' => 'select',
                    'title' => get_string('collection','blocktype.navigation'),
                    'rules' => array('required' => true),
                    'options' => $options,
                    'defaultvalue' => !empty($configdata['collection']) ? $configdata['collection'] : $default,
                ),
            );
        }
        else {
            return array(
                'nocollections' => array(
                    'type'  => 'html',
                    'title' => get_string('collection', 'blocktype.navigation'),
                    'description' => get_string('nocollections', 'blocktype.navigation', get_config('wwwroot')),
                    'value' => '',
                ),
            );
        }

    }

    public static function default_copy_type() {
        return 'full';
    }

    /**
     * Navigation only makes sense for personal views
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

}
