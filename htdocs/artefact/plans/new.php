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
define('MENUITEM', 'content/plans');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'plans');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'plans');
if (!PluginArtefactPlans::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('plans','artefact.plans')));
}

$id = param_integer('id',0);
if ($id) {
    $plan = new ArtefactTypePlan($id);
    if (!$USER->can_edit_artefact($plan)) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
    define('TITLE', get_string('newtask','artefact.plans'));
    $form = ArtefactTypeTask::get_form($id);
}
else {
    define('TITLE', get_string('newplan','artefact.plans'));
    $form = ArtefactTypePlan::get_form();
}

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->display('artefact:plans:new.tpl');
