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
$plan = new ArtefactTypePlan($id);

if (!$USER->can_edit_artefact($plan)) {
    throw new AccessDeniedException();
}

if ($plan->get('group')) {
    define('GROUP', $plan->get('group'));
    define('MENUITEM_SUBPAGE', 'groupplans');
    $group = group_current_group();
    $menuItem = 'engage/index';
    $title = $group->name . ' - ' . get_string('groupplans', 'artefact.plans');
    $pageheading = $group->name;
    $subsectionheading = hsc(get_string("editingplan", "artefact.plans"));
}
else {
    $group = null;
    $menuItem = 'create/plans';
    $title = get_string('editplan','artefact.plans');
    $pageheading = hsc(get_string("editingplan", "artefact.plans"));
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

$editform = ArtefactTypePlan::get_form($group, $plan);

$smarty = smarty(['artefact/plans/js/planedit.js']);
$smarty->assign('editform', $editform);
$smarty->assign('PAGEHEADING', $pageheading);
$smarty->assign('SUBSECTIONHEADING', $subsectionheading);
$smarty->display('artefact:plans:plan/edit.tpl');
