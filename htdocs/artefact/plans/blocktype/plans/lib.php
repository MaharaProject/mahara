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
 * @subpackage blocktype-plans
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypePlans extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.plans/plans');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.plans/plans');
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

        if (!empty($configdata['artefactid'])) {
            return $bi->get_artefact_instance($configdata['artefactid'])->get('title');
        }
        return '';
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $exporter;

        require_once(get_config('docroot') . 'artefact/lib.php');
        safe_require('artefact','plans');

        $configdata = $instance->get('configdata');

        $smarty = smarty_core();
        if (isset($configdata['artefactid'])) {
            $tasks = ArtefactTypeTask::get_tasks($configdata['artefactid']);
            $template = 'artefact:plans:taskrows.tpl';
            $blockid = $instance->get('id');
            if ($exporter) {
                $pagination = false;
            }
            else {
                $pagination = array(
                    'baseurl'   => $instance->get_view()->get_url() . '&block=' . $blockid,
                    'id'        => 'block' . $blockid . '_pagination',
                    'datatable' => 'tasktable_' . $blockid,
                    'jsonscript' => 'artefact/plans/viewtasks.json.php',
                );
            }
            ArtefactTypeTask::render_tasks($tasks, $template, $configdata, $pagination);

            if ($exporter && $tasks['count'] > $tasks['limit']) {
                $artefacturl = get_config('wwwroot') . 'view/artefact.php?artefact=' . $configdata['artefactid']
                    . '&amp;view=' . $instance->get('view');
                $tasks['pagination'] = '<a href="' . $artefacturl . '">' . get_string('alltasks', 'artefact.plans') . '</a>';
            }
            $smarty->assign('tasks',$tasks);
        }
        else {
            $smarty->assign('noplans','blocktype.plans/plans');
        }
        $smarty->assign('blockid', $instance->get('id'));
        return $smarty->fetch('blocktype:plans:content.tpl');
    }

    // My Plans blocktype only has 'title' option so next two functions return as normal
    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');

        $form = array();

        // Which resume field does the user want
        $form[] = self::artefactchooser_element((isset($configdata['artefactid'])) ? $configdata['artefactid'] : null);

        return $form;
    }

    public static function artefactchooser_element($default=null) {
        safe_require('artefact', 'plans');
        return array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('planstoshow', 'blocktype.plans/plans'),
            'defaultvalue' => $default,
            'blocktype' => 'plans',
            'selectone' => true,
            'search'    => false,
            'artefacttypes' => array('plan'),
            'template'  => 'artefact:plans:artefactchooser-element.tpl',
        );
    }

    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }
}
