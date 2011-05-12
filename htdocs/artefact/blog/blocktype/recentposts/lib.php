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
 * @subpackage blocktype-recentposts
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeRecentposts extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.blog/recentposts');
    }


    public static function get_description() {
        return get_string('description', 'blocktype.blog/recentposts');
    }

    public static function get_categories() {
        return array('blog');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');

        $result = '';
        $limit = isset($configdata['count']) ? (int) $configdata['count'] : 10;

        if (!empty($configdata['artefactids'])) {
            $before = 'TRUE';
            if ($instance->get_view()->is_submitted()) {
                if ($submittedtime = $instance->get_view()->get('submittedtime')) {
                    // Don't display posts added after the submitted date.
                    $before = "a.ctime < '$submittedtime'";
                }
            }
            $artefactids = implode(', ', array_map('db_quote', $configdata['artefactids']));
            if (!$mostrecent = get_records_sql_array(
            'SELECT a.title, ' . db_format_tsfield('a.ctime', 'ctime') . ', p.title AS parenttitle, a.id, a.parent
                FROM {artefact} a
                JOIN {artefact} p ON a.parent = p.id
                JOIN {artefact_blog_blogpost} ab ON (ab.blogpost = a.id AND ab.published = 1)
                WHERE a.artefacttype = \'blogpost\'
                AND a.parent IN ( ' . $artefactids . ' )
                AND a.owner = (SELECT "owner" from {view} WHERE id = ?)
                AND ' . $before . '
                ORDER BY a.ctime DESC
                LIMIT ' . $limit, array($instance->get('view')))) {
                $mostrecent = array();
            }
            // format the dates
            foreach ($mostrecent as &$data) {
                $data->displaydate = format_date($data->ctime);
            }
            $smarty = smarty_core();
            $smarty->assign('mostrecent', $mostrecent);
            $smarty->assign('view', $instance->get('view'));
            $result = $smarty->fetch('blocktype:recentposts:recentposts.tpl');
        }

        return $result;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        safe_require('artefact', 'blog');
        $configdata = $instance->get('configdata');
        $elements = array(self::artefactchooser_element((isset($configdata['artefactids'])) ? $configdata['artefactids'] : null),
            'count' => array(
                'type' => 'text',
                'title' => get_string('itemstoshow', 'blocktype.blog/recentposts'),
                'defaultvalue' => isset($configdata['count']) ? $configdata['count'] : 10,
                'size' => 3,
            ),
        );
	
        return $elements;
    }

    public static function artefactchooser_element($default=null) {
        return array(
            'name'  => 'artefactids',
            'type'  => 'artefactchooser',
            'title' => get_string('blogs', 'artefact.blog'),
            'defaultvalue' => $default,
            'blocktype' => 'recentposts',
            'limit'     => 10,
            'selectone' => false,
            'artefacttypes' => array('blog'),
            'template'  => 'artefact:blog:artefactchooser-element.tpl',
        );
    }

    public static function default_copy_type() {
        return 'nocopy';
    }

    /**
     * Recentposts blocktype is only allowed in personal views, because 
     * currently there's no such thing as group/site blogs
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

}
