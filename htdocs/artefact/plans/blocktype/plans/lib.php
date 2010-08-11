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
            safe_require('artefact','plans');
            $plan = new ArtefactTypePlan($configdata['artefactid']);
            $title = $plan->get('title');
            return $title;
        }
        return '';
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        safe_require('artefact','plans');
        $configdata = $instance->get('configdata');

        $smarty = smarty_core();
        if (isset($configdata['artefactid'])) {
            $tasks = ArtefactTypeTask::get_tasks($configdata['artefactid']);
            self::build_plans_html($tasks, $editing, $instance);
            $smarty->assign('tasks',$tasks);
        }
        else {
            $smarty->assign('noplans','blocktype.plans/plans');
        }
        $smarty->assign('blockid', $instance->get('id'));
        return $smarty->fetch('blocktype:plans:content.tpl');
    }

    public static function build_plans_html(&$tasks, $editing=false, BlockInstance $instance) {
        $smarty = smarty_core();
        $smarty->assign_by_ref('tasks', $tasks);
        $tasks['tablerows'] = $smarty->fetch('blocktype:plans:taskrows.tpl');
        if ($editing) {
            return;
        }
        $blockid = $instance->get('id');
        $baseurl = $instance->get_view()->get_url() . '&block=' . $blockid;
        $pagination = build_pagination(array(
            'id' => 'block' . $blockid . '_pagination',
            'class' => 'center nojs-hidden-block',
            'datatable' => 'tasktable_' . $blockid,
            'url' => $baseurl,
            'jsonscript' => 'artefact/plans/blocktype/plans/tasks.json.php',
            'count' => $tasks['count'],
            'limit' => $tasks['limit'],
            'offset' => $tasks['offset'],
            'numbersincludefirstlast' => false,
            'resultcounttextsingular' => get_string('task', 'artefact.plans'),
            'resultcounttextplural' => get_string('tasks', 'artefact.plans'),
        ));
        $tasks['pagination'] = $pagination['html'];
        $tasks['pagination_js'] = 'var paginator' . $blockid . ' = ' . $pagination['javascript'];
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
}

?>
