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
 * @subpackage blocktype-myviews
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeMyviews extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.myviews');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.myviews');
    }
    
    public static function single_only() {
        return true;
    }

    public static function get_categories() {
        return array('internal');
    }

    public static function get_viewtypes() {
        return array('profile', 'dashboard');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $userid = $instance->get_view()->get('owner');
        if (!$userid) {
            return '';
        }

        $smarty = smarty_core();

        // Get viewable views
        $views = array();
        if ($allviews = get_records_select_array('view', "\"owner\" = ? AND type NOT IN ('profile', 'dashboard')", array($userid))) {
            foreach ($allviews as $view) {
                if (can_view_view($view->id)) {
                    $views[$view->id] = $view;
                    $view->artefacts = array();
                    $view->description = str_shorten_html($view->description, 100, true);
                }
            }
        }

        if ($views) {
            $viewidlist = implode(', ', array_map(create_function('$a', 'return (int)$a->id;'), $views));
            $artefacts = get_records_sql_array('SELECT va.view, va.artefact, a.title, a.artefacttype, t.plugin
                FROM {view_artefact} va
                INNER JOIN {artefact} a ON va.artefact = a.id
                INNER JOIN {artefact_installed_type} t ON a.artefacttype = t.name
                WHERE va.view IN (' . $viewidlist . ')
                GROUP BY 1, 2, 3, 4, 5
                ORDER BY a.title, va.artefact', '');
            if ($artefacts) {
                foreach ($artefacts as $artefactrec) {
                    safe_require('artefact', $artefactrec->plugin);
                    // Perhaps I shouldn't have to construct the entire
                    // artefact object to render the name properly.
                    $classname = generate_artefact_class_name($artefactrec->artefacttype);
                    $artefactobj = new $classname(0, array('title' => $artefactrec->title));
                    $artefactobj->set('dirty', false);
                    if (!$artefactobj->in_view_list()) {
                        continue;
                    }
                    $artname = $artefactobj->display_title(30);
                    if (strlen($artname)) {
                        $views[$artefactrec->view]->artefacts[] = array('id'    => $artefactrec->artefact,
                                                                        'title' => $artname);
                    }
                }
            }
            $tags = get_records_select_array('view_tag', 'view IN (' . $viewidlist . ')');
            if ($tags) {
                foreach ($tags as &$tag) {
                    $views[$tag->view]->tags[] = $tag->tag;
                }
            }
        }
        $smarty->assign('VIEWS',$views);
        return $smarty->fetch('blocktype:myviews:myviews.tpl');
    }

    public static function has_instance_config() {
        return false;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Myviews only makes sense for personal views
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

    public static function override_instance_title(BlockInstance $instance) {
        global $USER;
        $ownerid = $instance->get_view()->get('owner');
        if ($ownerid === null || $ownerid == $USER->get('id')) {
            return get_string('title', 'blocktype.myviews');
        }
        return get_string('otherusertitle', 'blocktype.myviews', display_name($ownerid, null, true));
    }

}

?>
