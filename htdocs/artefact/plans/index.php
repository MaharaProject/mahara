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
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'plans');

if (!PluginArtefactPlans::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('Plans','artefact.plans')));
}

if (param_exists('group')) {
    define('GROUP', param_integer('group'));
    define('MENUITEM_SUBPAGE', 'groupplans');

    $group = group_current_group();
    $urlQuery = '?group=' . $group->id;
    $menuItem = 'engage/index';
    $title = $group->name . ' - ' . get_string('Plans','artefact.plans');
    $pageheading = $group->name;
    $subsectionheading = hsc(get_string('Plans', 'artefact.plans'));
    if (!ArtefactTypePlan::user_can_view_groupplans($group)) {
        throw new AccessDeniedException();
    }
    $canEdit = ArtefactTypePlan::user_can_edit_groupplan($group);
}
else {
    $group = null;
    $urlQuery = '';
    $menuItem = 'create/plans';
    $title = get_string('Plans','artefact.plans');
    $pageheading = hsc(get_string('Plans', 'artefact.plans'));
    $subsectionheading = null;
    $canEdit = true;
}

define('TITLE', $title);
define('MENUITEM', $menuItem);

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

$plans = ArtefactTypePlan::get_plans($offset, $limit, $group);
ArtefactTypePlan::build_plans_list_html($plans, $canEdit);

$js = <<< EOF
jQuery(function () {
    {$plans['pagination_js']}
});
EOF;

$newPlanLink = get_config('wwwroot') . 'artefact/plans/plan/new.php' . $urlQuery;

$smarty = smarty(['paginator']);
setpageicon($smarty, 'icon-clipboard-list');
$smarty->assign('plans', $plans);
$smarty->assign('canedit', $canEdit);
if ($canEdit) {
    $smarty->assign('strnoplans',
                    get_string('noplansaddone', 'artefact.plans',
                               '<a href="' . $newPlanLink.'">', '</a>'));
}
else {
    $smarty->assign('strnoplans', get_string('noplans', 'artefact.plans'));
}
$smarty->assign('PAGEHEADING', $pageheading);
$smarty->assign('SUBSECTIONHEADING', $subsectionheading);
$smarty->assign('newPlanLink', $newPlanLink);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('artefact:plans:index.tpl');
