<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'plans');
define('SECTION_PAGE', 'plans');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
safe_require('artefact', 'plans');
if (!PluginArtefactPlans::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('Plans','artefact.plans')));
}

$id = param_integer('id');

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

$plan = new ArtefactTypePlan($id);

$headingTemplateTextExtension = '';
if ($plan->get('template')) {
    $headingTemplateTextExtension = get_string('templateplan', 'artefact.plans');
}

if ($plan->get('group')) {
    define('GROUP', $plan->get('group'));
    define('MENUITEM_SUBPAGE', 'groupplans');
    $group = group_current_group();
    $menuItem = 'engage/index';
    $title = $group->name . ' - ' . get_string('Tasks', 'artefact.plans');
    $pageheading = $group->name;
    $subsectionheading = get_string('planstasks1', 'artefact.plans', $headingTemplateTextExtension, $plan->get('title'));
    if (!$USER->can_view_artefact($plan)) {
        throw new AccessDeniedException();
    }
}
else {
    $group = null;
    $menuItem = 'create/plans';
    $title = get_string('Tasks','artefact.plans');
    $pageheading = get_string('planstasks1', 'artefact.plans', $headingTemplateTextExtension, $plan->get('title'));
    $subsectionheading = null;
    if (!$USER->can_edit_artefact($plan)) { // A private plan is editable only by the owner
        throw new AccessDeniedException();
    }
}

define('TITLE', $title);
define('MENUITEM', $menuItem);

$canEdit = $USER->can_edit_artefact($plan);

$tasks = ArtefactTypeTask::get_tasks($plan, $offset, $limit);
ArtefactTypeTask::build_tasks_list_html($tasks, $canEdit);

$js = <<< EOF
jQuery(function () {
    {$tasks['pagination_js']}
});
EOF;

$urlQuery = ['id' => $plan->get('id')];
if ($group) {
    $urlQuery['group'] = $group->id;
}
$newTaskLink = get_config('wwwroot') . 'artefact/plans/task/new.php?' . http_build_query($urlQuery);
$pagestrings = [
    'artefact.plans' => [
        'grouptaskselected',
        'grouptaskunselected',
        'unselecttaskconfirm'
    ],
    'collection' => [
        'emptycollection'
    ]
];
$smarty = smarty(['paginator', 'js/preview.js', 'artefact/plans/js/planview.js',
                  'js/gridstack/gridstack_modules/gridstack-h5.js',
                  'js/gridlayout.js'], null, $pagestrings);

$smarty->assign('tasks', $tasks);
//$smarty->assign('plan', $id);
$smarty->assign('tags', $plan->get('tags'));
$smarty->assign('owner', $plan->get('owner'));
$smarty->assign('canedit', $canEdit);
$smarty->assign('newtasklink', $newTaskLink);
if ($canEdit) {
    $smarty->assign('strnotasks',
                    get_string('notasksaddone', 'artefact.plans',
                               '<a class="addtask" href="' . $newTaskLink .'">', '</a>'));
}
else {
    $smarty->assign('strnotasks', get_string('notasks', 'artefact.plans'));
}
$smarty->assign('planstasksdescription', get_string('planstasksdescription', 'artefact.plans', get_string('newtask', 'artefact.plans')));
$smarty->assign('PAGEHEADING', $pageheading);
$smarty->assign('SUBSECTIONHEADING', $subsectionheading);
$smarty->assign('INLINEJAVASCRIPT', $js);

$smarty->display('artefact:plans:plan/view.tpl');
