<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Alexander Del Ponte
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

use artefact\plans\tools\PlansTools;

define('INTERNAL', 1);
define('JSON', 1);

try {
    require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
    require(dirname(dirname(__FILE__)) . '/tools/PlansTools.php');
    safe_require('artefact', 'plans');

    if (!PluginArtefactPlans::is_active()) {
        throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('Plans', 'artefact.plans')));
    }

    $rootGroupTaskId = param_integer('taskid');
    $chosen = param_boolean('chosen');

    $rootGroupTask = new ArtefactTypeTask($rootGroupTaskId);
    $rootGroupPlan = new ArtefactTypePlan((int)$rootGroupTask->get('parent'));

    if (!$USER->can_view_artefact($rootGroupPlan)) {
        throw new AccessDeniedException();
    }

    if ($USER->can_edit_artefact($rootGroupPlan)) {
        throw new Exception(get_string('wrongfunctionrole', 'artefact.plans'));
    }

    if (!$rootGroupPlan->is_selection_plan()) {
        throw new MaharaException(get_string('noselectiontask','artefact.plans'));
    }

    $userTask = PlansTools::findCorrespondingUserTaskByRootGroupTaskId($rootGroupTaskId);
    // $chosen represents the selection state of the frontend. If it is not sync to the backend, send info.
    if (empty($userTask) === $chosen) {
        global $SESSION;
        $SESSION->add_error_msg(get_string('selectionstatechangedpagereload','artefact.plans'));
        json_reply(true, null, 1);
        die;
    }

    $userPlan = PlansTools::findCorrespondingUserPlanByRootGroupPlanId($rootGroupPlan->get('id'));
    if (!$userPlan) {
        $userPlan = PlansTools::createUserPlanFromGroupPlan($rootGroupPlan);
    }
    $taskview = $rootGroupTask->get('taskview');
    if ($userTask) {
        $portfolioElement = PlansTools::getPortfolioElementByTypeAndId($rootGroupTask->get('outcometype'), $rootGroupTask->get('outcome'));
        $outcomeurl = false;
        if ($portfolioElement) {
            $outcomeurl = PlansTools::createOutcomeUrlForPortfolioElement($portfolioElement);
        }
        $buttons = '';
        if ($taskview) {
            $buttons .= '<a href="' . get_config('wwwroot') . 'view/view.php?id=' . $taskview . '" class="btn btn-secondary btn-sm btn-view" title="' . get_string('showassignedview', 'artefact.plans') . '">
                             <span class="icon icon-info" role="presentation" aria-hidden="true"></span>
                         </a>';
        }
        if ($outcomeurl) {
            $buttons .= '<a href="' . $outcomeurl . '" class="btn btn-secondary btn-sm btn-outcome" title="' . get_string('editassignedoutcome', 'artefact.plans') . '">
                             <span class="icon icon-file" role="presentation" aria-hidden="true"></span>
                         </a>';
        }
        $userTask->delete();
        if ($userPlan->count_children() == 0) {
            $userPlan->delete();
        }
        $outcome['buttons'] = $buttons;
        $outcome['status'] = 0;
    }
    else {
        $newUserTask = PlansTools::createUserTaskFromGroupTask($rootGroupTask, $userPlan);
        $outcomeId = $newUserTask->get('outcome');
        if ($outcomeId) {
            $portfolioElement = PlansTools::getPortfolioElementByTypeAndId($newUserTask->get('outcometype'), $outcomeId);
            $outcomeurl = PlansTools::createOutcomeUrlForPortfolioElement($portfolioElement);
            $buttons = '';
            if ($taskview) {
                $buttons .= '<a href="' . get_config('wwwroot') . 'view/view.php?id=' . $taskview . '" class="btn btn-secondary btn-sm btn-view" title="' . get_string('showassignedview', 'artefact.plans') . '">
                                 <span class="icon icon-info" role="presentation" aria-hidden="true"></span>
                             </a>';
            }
            if ($outcomeurl) {
                $buttons .= '<a href="' . $outcomeurl . '" class="btn btn-secondary btn-sm btn-outcome" title="' . get_string('editassignedoutcome', 'artefact.plans') . '">
                                 <span class="icon icon-file" role="presentation" aria-hidden="true"></span>
                             </a>';
            }
            $outcomesubmissionurl = PlansTools::createSubmissionUrlForCompletedTaskWithOutcome($newUserTask, $rootGroupPlan->get('id'));
            if ($outcomesubmissionurl) {
                $buttons .= '<a href="' . $outcomesubmissionurl . '" title="' . get_string('submitassignedoutcome', 'artefact.plans') . '" class="btn btn-secondary btn-sm">
                                 <span class="icon icon-file-upload" role="presentation" aria-hidden="true"></span>
                                 <span class="sr-only">' . get_string('submitassignedoutcome', 'artefact.plans') . '</span>
                             </a>';
            }
            $outcome['buttons'] = $buttons;
            $outcome['status'] = 1;
        }
        else {
            $outcome['status'] = 1;
        }
    }
}
catch (Exception $e) {
    json_reply(true, $e->getMessage());
    die;
}
json_reply(false, (isset($outcome) ? $outcome : []));
