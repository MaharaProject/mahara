<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */


define('INTERNAL', 1);
define('MENUITEM', 'create/plans');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'plans');
define('SECTION_PAGE', 'plans');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'plans');
if (!PluginArtefactPlans::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('Plans','artefact.plans')));
}

define('TITLE', get_string('Tasks','artefact.plans'));

$id = param_integer('id');

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

$plan = new ArtefactTypePlan($id);
if (!$USER->can_edit_artefact($plan)) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}


$tasks = ArtefactTypeTask::get_tasks($plan->get('id'), $offset, $limit);
ArtefactTypeTask::build_tasks_list_html($tasks);

$js = <<< EOF
jQuery(function () {
    {$tasks['pagination_js']}
});
EOF;

$smarty = smarty(array('paginator'));
$smarty->assign('tasks', $tasks);
$smarty->assign('plan', $id);
$smarty->assign('tags', $plan->get('tags'));
$smarty->assign('owner', $plan->get('owner'));
$smarty->assign('strnotasksaddone',
    get_string('notasksaddone', 'artefact.plans',
    '<a class="addtask" href="' . get_config('wwwroot') . 'artefact/plans/new.php?id='.$plan->get('id').'">', '</a>'));
$smarty->assign('planstasksdescription', get_string('planstasksdescription', 'artefact.plans', get_string('newtask', 'artefact.plans')));
$smarty->assign('PAGEHEADING', get_string("planstasks", "artefact.plans",$plan->get('title')));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('artefact:plans:plan.tpl');
