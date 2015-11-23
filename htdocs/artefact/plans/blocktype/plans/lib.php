<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-plans
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypePlans extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.plans/plans');
    }

    public static function get_description() {
        return get_string('description1', 'blocktype.plans/plans');
    }

    public static function get_categories() {
        return array('general' => 22000);
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

    public static function get_instance_javascript(BlockInstance $bi) {
        $blockid = $bi->get('id');
        return array(
            array(
                'file'   => 'js/plansblock.js',
                'initjs' => "initNewPlansBlock($blockid);",
            )
        );
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $exporter;

        require_once(get_config('docroot') . 'artefact/lib.php');
        safe_require('artefact','plans');

        $configdata = $instance->get('configdata');
        $limit = (!empty($configdata['count'])) ? $configdata['count'] : 10;

        $smarty = smarty_core();
        if (isset($configdata['artefactid'])) {
            $plan = artefact_instance_from_id($configdata['artefactid']);
            $tasks = ArtefactTypeTask::get_tasks($configdata['artefactid'], 0, $limit);
            $template = 'artefact:plans:taskrows.tpl';
            $blockid = $instance->get('id');
            if ($exporter) {
                $pagination = false;
            }
            else {
                $baseurl = $instance->get_view()->get_url();
                $baseurl .= ((false === strpos($baseurl, '?')) ? '?' : '&') . 'block=' . $blockid;
                $pagination = array(
                    'baseurl'   => $baseurl,
                    'id'        => 'block' . $blockid . '_pagination',
                    'datatable' => 'tasklist_' . $blockid,
                    'jsonscript' => 'artefact/plans/viewtasks.json.php',
                );
            }
            ArtefactTypeTask::render_tasks($tasks, $template, $configdata, $pagination);

            if ($exporter && $tasks['count'] > $tasks['limit']) {
                $artefacturl = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $configdata['artefactid']
                    . '&view=' . $instance->get('view');
                $tasks['pagination'] = '<a href="' . $artefacturl . '">' . get_string('alltasks', 'artefact.plans') . '</a>';
            }
            $smarty->assign('description', $plan->get('description'));
            $smarty->assign('owner', $plan->get('owner'));
            $smarty->assign('tags', $plan->get('tags'));
            $smarty->assign('tasks', $tasks);
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

    public static function instance_config_form(BlockInstance $instance) {
        $instance->set('artefactplugin', 'plans');
        $configdata = $instance->get('configdata');

        $form = array();

        // Which resume field does the user want
        $form[] = self::artefactchooser_element((isset($configdata['artefactid'])) ? $configdata['artefactid'] : null);
        $form['count'] = array(
            'type' => 'text',
            'title' => get_string('taskstodisplay', 'blocktype.plans/plans'),
            'defaultvalue' => isset($configdata['count']) ? $configdata['count'] : 10,
            'size' => 3,
        );

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
