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
define('SECTION_PAGE', 'groupplans');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
safe_require('artefact', 'plans');

if (!PluginArtefactPlans::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('Plans', 'artefact.plans')));
}

$planId = param_integer('id');
$artefactType = 'task';

if (param_exists('group')) {
    define('GROUP', param_integer('group'));
    define('MENUITEM_SUBPAGE', 'groupplans');
    $group = group_current_group();
    $menuItem = 'engage/index';
    $title = $group->name . ' - ' . get_string('groupplans', 'artefact.plans');
    $pageheading = $group->name;
    $subsectionheading = hsc(get_string("new" . $artefactType, "artefact.plans"));
}
else {
    $group = null;
    $menuItem = 'create/plans';
    $title = get_string('new' . $artefactType, 'artefact.plans');
    $pageheading = hsc(get_string("new" . $artefactType, "artefact.plans"));
    $subsectionheading = null;
}

define('MENUITEM', $menuItem);
define('TITLE', $title);

$plan = new ArtefactTypePlan($planId);
if (($group && !$USER->can_view_artefact($plan)) || (!$group && !$USER->can_edit_artefact($plan))) {
    throw new AccessDeniedException();
}

$viewid = (isset($_GET['view']) ? $_GET['view'] : null);
if ($viewid) {
    require_once('view.php');
    $view = new View($viewid);
}
else {
    $view = null;
}

$form = ArtefactTypeTask::get_form($planId, $group);

$smarty = smarty(['paginator', 'js/preview.js', 'artefact/plans/js/taskedit.js',
                  'js/gridstack/gridstack_modules/gridstack-h5.js', 'js/gridlayout.js']);

$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', $pageheading);
$smarty->assign('SUBSECTIONHEADING', $subsectionheading);
$smarty->assign('showassignedview', get_string('showassignedview', 'artefact.plans'));
$smarty->assign('showassignedoutcome', get_string('showassignedoutcome', 'artefact.plans'));

$smarty->display('artefact:plans:task/new.tpl');
