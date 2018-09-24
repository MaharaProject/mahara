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

        if (!empty($configdata['artefactids']) && is_array($configdata['artefactids'])) {
            if (count($configdata['artefactids']) > 1) {
                return get_string('title', 'blocktype.plans/plans');
            }
            else if (count($configdata['artefactids']) == 1) {
                return $bi->get_artefact_instance($configdata['artefactids'][0])->get('title');
            }
        }
        return '';
    }

    public static function get_instance_javascript(BlockInstance $bi) {
        $blockid = $bi->get('id');
        return array(
            array(
                'file'   => 'js/plansblock.js',
            )
        );
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        global $exporter;

        require_once(get_config('docroot') . 'artefact/lib.php');
        safe_require('artefact','plans');

        $configdata = $instance->get('configdata');
        $limit = (!empty($configdata['count'])) ? $configdata['count'] : 10;

        $smarty = smarty_core();
        if (isset($configdata['artefactids']) && is_array($configdata['artefactids']) && count($configdata['artefactids']) > 0) {
            $plans = array();
            $alltasks = array();
            foreach ($configdata['artefactids'] as $planid) {
                $plan = artefact_instance_from_id($planid);
                $tasks = ArtefactTypeTask::get_tasks($planid, 0, $limit);
                $template = 'artefact:plans:taskrows.tpl';
                $blockid = $instance->get('id');
                if ($exporter) {
                    $pagination = false;
                }
                else {
                    $baseurl = $instance->get_view()->get_url();
                    $baseurl .= ((false === strpos($baseurl, '?')) ? '?' : '&') . 'block=' . $blockid . '&planid=' . $planid . '&editing=' . $editing;
                    $pagination = array(
                        'baseurl'   => $baseurl,
                        'id'        => 'block' . $blockid . '_plan' . $planid . '_pagination',
                        'datatable' => 'tasklist_' . $blockid . '_plan' . $planid,
                        'jsonscript' => 'artefact/plans/viewtasks.json.php',
                    );
                }
                $configdata['view'] = $instance->get('view');
                $configdata['block'] = $blockid;
                $configdata['versioning'] = $versioning;
                ArtefactTypeTask::render_tasks($tasks, $template, $configdata, $pagination, $editing);

                if ($exporter && $tasks['count'] > $tasks['limit']) {
                    $artefacturl = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $planid
                        . '&view=' . $instance->get('view');
                    $tasks['pagination'] = '<a href="' . $artefacturl . '">' . get_string('alltasks', 'artefact.plans') . '</a>';
                }
                $plans[$planid]['id'] = $planid;
                $plans[$planid]['title'] = $plan->get('title');
                $plans[$planid]['description'] = $plan->get('description');
                $plans[$planid]['owner'] = $plan->get('owner');
                $plans[$planid]['tags'] = $plan->get('tags');
                $plans[$planid]['view'] = $instance->get('view');
                $plans[$planid]['details'] = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $plan->get('id') . '&view=' .
                        $instance->get_view()->get('id') . '&block=' . $blockid;

                $plans[$planid]['numtasks'] = $tasks['count'];

                $tasks['planid'] = $planid;
                array_push($alltasks, $tasks);
            }
            $smarty->assign('editing', $editing);
            $smarty->assign('plans', $plans);
            $smarty->assign('alltasks', $alltasks);
        }
        else {
            $smarty->assign('editing', $editing);
            $smarty->assign('noplans', get_string('noplansselectone', 'blocktype.plans/plans'));
        }
        $smarty->assign('blockid', $instance->get('id'));
        $smarty->assign('versioning', $versioning);
        return $smarty->fetch('blocktype:plans:content.tpl');
    }

    // My Plans blocktype only has 'title' option so next two functions return as normal
    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $instance->set('artefactplugin', 'plans');
        $configdata = $instance->get('configdata');
        $owner = $instance->get_view()->get('owner');
        $form = array();
        if ($owner) {
            // Which resume field does the user want
            $form[] = self::artefactchooser_element((isset($configdata['artefactids'])) ? $configdata['artefactids'] : null);
            $form['count'] = array(
                'type' => 'text',
                'title' => get_string('taskstodisplay', 'blocktype.plans/plans'),
                'defaultvalue' => isset($configdata['count']) ? $configdata['count'] : 10,
                'size' => 3,
            );
        }
        else {
            $form['blocktemplatehtml'] = array(
                'type' => 'html',
                'value' => get_string('blockinstanceconfigownerchange', 'mahara'),
            );
            $form['blocktemplate'] = array(
                'type'    => 'hidden',
                'value'   => 1,
            );
        }

        return $form;
    }

    public static function artefactchooser_element($default=null) {
        safe_require('artefact', 'plans');
        return array(
            'name'  => 'artefactids',
            'type'  => 'artefactchooser',
            'title' => get_string('planstoshow', 'blocktype.plans/plans'),
            'defaultvalue' => $default,
            'blocktype' => 'plans',
            'selectone' => false,
            'search'    => false,
            'artefacttypes' => array('plan'),
            'template'  => 'artefact:plans:artefactchooser-element.tpl',
        );
    }

    public static function allowed_in_view(View $view) {
        return true;
    }

    public static function rewrite_blockinstance_config(View $view, $configdata) {
        safe_require('artefact', 'plans');
        if ($view->get('owner') !== null && !empty($configdata['blocktemplate'])) {
            if ($artefactids = get_column_sql('
                SELECT a.id FROM {artefact} a
                WHERE a.owner = ? AND a.artefacttype = ?', array($view->get('owner'), 'plan'))) {
                $configdata['artefactids'] = $artefactids;
            }
            else {
                $configdata['artefactids'] = array();
            }
            unset($configdata['blocktemplatehtml']);
            unset($configdata['blocktemplate']);
        }
        return $configdata;
    }
}
