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

define('INTERNAL', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('docroot') . 'artefact/lib.php');
safe_require('artefact','plans');

if (!PluginArtefactPlans::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('Plans','artefact.plans')));
}

$id = param_integer('id');
$task = new ArtefactTypeTask($id);

if (!$USER->can_edit_artefact($task)) {
    throw new AccessDeniedException();
}
// If accessing a personal task check that the task owner matches the current user
if ($task->get('owner') && $task->get('owner') != $USER->get('id')) {
    throw new AccessDeniedException();
}

if ($task->get('group')) {
    define('GROUP', $task->get('group'));
    define('MENUITEM_SUBPAGE', 'groupplans');
    $group = group_current_group();
    $menuItem = 'engage/index';
    $title = $group->name . ' - ' . get_string('groupplans', 'artefact.plans');
    $pageheading = $group->name;
    $subsectionheading = hsc(get_string("editingtask", "artefact.plans"));
}
else {
    $group = null;
    $menuItem = 'create/plans';
    $title = get_string('edittask','artefact.plans');
    $pageheading = hsc(get_string("editingtask", "artefact.plans"));
    $subsectionheading = null;
}

$viewid = (isset($_GET['view']) ? $_GET['view'] : null);
if ($viewid) {
    require_once('view.php');
    $view = new View($viewid);
}
else {
    $view = null;
}

define('MENUITEM', $menuItem);
define('TITLE', $title);

$form = ArtefactTypeTask::get_form($task->get('parent'), $group, $task);

$smarty = smarty(['paginator', 'js/preview.js', 'artefact/plans/js/taskedit.js',
                  'js/gridstack/gridstack_modules/gridstack-h5.js', 'js/gridlayout.js']);
$smarty->assign('editform', $form);
$smarty->assign('PAGEHEADING', $pageheading);
$smarty->assign('SUBSECTIONHEADING', $subsectionheading);
$smarty->assign('showassignedview', get_string('showassignedview', 'artefact.plans'));
$smarty->assign('showassignedoutcome', get_string('showassignedoutcome', 'artefact.plans'));
$smarty->display('artefact:plans:task/edit.tpl');
