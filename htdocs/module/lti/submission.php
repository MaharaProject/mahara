<?php
/**
 *
 * @package    mahara
 * @subpackage module-lti
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
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

    $smarty->assign('PAGEHEADING', get_string('submitportfolio', 'module.lti'));

    $sub = PluginModuleLti::get_submission();

    if ($sub && $sub->is_submitted()) {
        // Info on submitted collection
        $info = $sub->get_portfolio_info();
        $grader = $sub->get_grader();

        $smarty->assign('title', $info->title);
        $smarty->assign('link', $info->link);
        $smarty->assign('timesubmitted', $sub->timesubmitted);
        $smarty->assign('grade', $sub->grade);
        $smarty->assign('gradedby', empty($grader) ? '' : display_name($grader));
        $smarty->assign('timegraded', $sub->timegraded);

        $smarty->display('module:lti:submittedforgrading.tpl');
    }
    else if (PluginModuleLTI::activity_configured()) {
        // Assessment submission form
        $smarty->assign('form', $form);
        $smarty->display('module:lti:submitforgrading.tpl');
    }
    else {
        $smarty->assign('error', get_string('notconfigured', 'module.lti'));
        $smarty->display('module:lti:submitforgrading.tpl');
    }
}
else {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}