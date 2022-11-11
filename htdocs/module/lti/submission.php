<?php
/**
 *
 * @package    mahara
 * @subpackage module-lti
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'create/views');
define('SECTION_PLUGINTYPE', 'module');
define('SECTION_PLUGINNAME', 'lti');
define('SECTION_PAGE', 'submission');


require(dirname(dirname(dirname(__FILE__))) . '/init.php');

define('TITLE', get_string('submitportfolio', 'module.lti'));

safe_require('module', 'lti');

$form = '';
if (PluginModuleLti::can_grade()) {
    $form = PluginModuleLti::config_form();
}
else if (PluginModuleLti::can_submit_for_grading()) {
    $form = PluginModuleLti::submit_for_grading_form();
}

$smarty = smarty();

if ($SESSION->get('lti.presentation_target') != 'window') {
    $smarty->assign('SIDEBARS', false);
    $smarty->assign('presentation', 'presentation-iframe');
}

if (PluginModuleLti::can_grade()) {
    $smarty->assign('PAGEHEADING', get_string('gradesubmissions', 'module.lti'));

    if (!$submissions = PluginModuleLti::get_all_submissions()) {
        // Assessment configuration form
        $smarty->assign('PAGEHEADING', get_string('configuration', 'module.lti'));
        $smarty->assign('form', $form);
        $smarty->display('module:lti:config.tpl');
    }
    else {
        // List of submissions
        $smarty->assign('submissions', $submissions);
        $smarty->display('module:lti:submissions.tpl');
    }

}
else if (PluginModuleLti::can_submit_for_grading()) {

    $sub = PluginModuleLti::get_submission();
    $revokeform = PluginModuleLti::revokesubmission_form();

    if ($sub && $sub->is_submitted()) {
        $smarty->assign('PAGEHEADING', get_string('portfoliosubmittedheader', 'module.lti'));
        // Info on submitted collection
        $info = $sub->get_portfolio_info();
        $originalportfolioinfo = $sub->get_original_portfolio_info();
        $grader = $sub->get_grader();

        $smarty->assign('originaltitle', $originalportfolioinfo->title);
        $smarty->assign('link', $info->link);
        $smarty->assign('timesubmitted', $sub->get('timesubmitted'));
        $smarty->assign('grade', $sub->get('grade'));
        $smarty->assign('gradedby', empty($grader) ? '' : display_name($grader));
        $smarty->assign('timegraded', $sub->get('timegraded'));
        $smarty->assign('revokeform', $revokeform);

        $smarty->display('module:lti:submittedforgrading.tpl');
    }
    else if (PluginModuleLti::activity_configured()) {
        $smarty->assign('PAGEHEADING', get_string('submitportfolio', 'module.lti'));
        // Assessment submission form
        $smarty->assign('form', $form);
        $smarty->display('module:lti:submitforgrading.tpl');
    }
    else {
        $smarty->assign('PAGEHEADING', get_string('submitportfolio', 'module.lti'));
        $smarty->assign('error', get_string('notconfigured', 'module.lti'));
        $smarty->display('module:lti:submitforgrading.tpl');
    }
}
else {
    throw new AccessDeniedException();
}