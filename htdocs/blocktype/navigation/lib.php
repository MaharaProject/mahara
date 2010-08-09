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
            require_once('collection.php');
            $data = get_record_select('collection', 'id = ?', array($configdata['collection']));
            $collection = new Collection($configdata['collection'], (array)$data);
            $title = $collection->get('name');
            return $title;
        }
        return '';
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        $smarty = smarty_core();

        if (!empty($configdata['collection'])) {
            $sql = "SELECT cv.*, v.title
                    FROM {collection_view} cv
                        LEFT JOIN {collection} c ON cv.collection = c.id
                        LEFT JOIN {view} v ON cv.view = v.id
                    WHERE c.id = ?
                    ORDER BY cv.displayorder, v.title, v.ctime ASC";

            if ($views = get_records_sql_array($sql, array($configdata['collection']))) {
                $smarty->assign('views',$views);
            }
        }
        $smarty->assign('currentview',$instance->get('view'));
        return $smarty->fetch('blocktype:navigation:navigation.tpl');
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

?>
