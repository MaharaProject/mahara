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
 * @subpackage blocktype-newviews
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2010 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeNewViews extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.newviews');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.newviews');
    }

    public static function get_categories() {
        return array('general');
    }

    public static function get_viewtypes() {
        return array('dashboard');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER;
        require_once('view.php');
        $configdata = $instance->get('configdata');
        $nviews = isset($configdata['limit']) ? intval($configdata['limit']) : 5;

        $sort = array(array('column' => 'mtime', 'desc' => true));
        $views = View::view_search(null, null, null, null, $nviews, 0, true, $sort, array('portfolio'));
        $smarty = smarty_core();
        $smarty->assign('loggedin', $USER->is_logged_in());
        $smarty->assign('views', $views->data);
        return $smarty->fetch('blocktype:newviews:newviews.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        return array('limit' => array(
            'type' => 'text',
            'title' => get_string('viewstoshow', 'blocktype.newviews'),
            'description' => get_string('viewstoshowdescription', 'blocktype.newviews'),
            'defaultvalue' => (isset($configdata['limit'])) ? intval($configdata['limit']) : 5,
            'size' => 3,
            'minvalue' => 1,
            'maxvalue' => 100,
        ));
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    public static function get_instance_title(BlockInstance $instance) {
        return get_string('title', 'blocktype.newviews');
    }
}
