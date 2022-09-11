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

use artefact\plans\tools\PlansTools;

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'plans');
define('SECTION_PAGE', 'groupplans');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
safe_require('artefact', 'plans');

if (!PluginArtefactPlans::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('Plans','artefact.plans')));
}

if (param_exists('group')) {
    define('GROUP', param_integer('group'));
    define('MENUITEM_SUBPAGE', 'groupplans');
    $group = group_current_group();
    $menuItem = 'engage/index';
    $title = $group->name . ' - ' . get_string('groupplans', 'artefact.plans');
    $pageheading = $group->name;
    $subsectionheading = hsc(get_string('newplan', "artefact.plans"));
}
else {
    $group = null;
    $menuItem = 'create/plans';
    $title = get_string('newplan','artefact.plans');
    $pageheading = hsc(get_string('newplan', "artefact.plans"));
    $subsectionheading = null;
}

define('MENUITEM', $menuItem);
define('TITLE', $title);

$userPlanTemplates = null;

if ($group) {
    if (!ArtefactTypePlan::user_can_edit_groupplan($group)) {
        throw new AccessDeniedException();
    }
    require_once(dirname(dirname(__FILE__)) . '/tools/PlansTools.php');
    $userPlanTemplates = PlansTools::getIdTitleArrayOfUserPlanTemplates();
}

$viewid = (isset($_GET['view']) ? $_GET['view'] : null);
if ($viewid) {
    require_once('view.php');
    $view = new View($viewid);
}
else {
    $view = null;
}

$form = ArtefactTypePlan::get_form($group);

$smarty = smarty(['artefact/plans/js/plannew.js']);
if ($userPlanTemplates) {
    $smarty->assign('userplantemplates', $userPlanTemplates);
    $smarty->assign('choosetemplate', get_string('choosetemplate', 'artefact.plans'));
    $smarty->assign('notemplate', get_string('notemplate', 'artefact.plans'));
    $smarty->assign('close', get_string('close', 'artefact.plans'));
    $smarty->assign('fromtemplate', get_string('fromtemplate', 'artefact.plans'));
    $smarty->assign('templatedialogdescription', get_string('templatedialogdescription', 'artefact.plans'));
}
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', $pageheading);
$smarty->assign('SUBSECTIONHEADING', $subsectionheading);

$smarty->display('artefact:plans:plan/new.tpl');
