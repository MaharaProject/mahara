<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

use artefact\plans\tools\PlansTools;

define('INTERNAL', 1);
define('JSON', 1);

try {
    require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
    safe_require('artefact', 'plans');

    if (!PluginArtefactPlans::is_active()) {
        throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('Plans', 'artefact.plans')));
    }

    $taskId = param_integer('taskid');
    $task = new ArtefactTypeTask($taskId);
    $completed = param_boolean('completed');

    if (!$USER->can_edit_artefact($task)) {
        throw new AccessDeniedException();
    }

    if ($completed != $task->get('completed')) {
        global $SESSION;
        $SESSION->add_error_msg(get_string('completedstatechangedpagereload','artefact.plans'));
        json_reply(true, null, 1);
        die;
    }

    $outcome = array();
    $outcome['submissionUrl'] = null;
    if ($task->get('outcome') && $task->get('completed') == 0) {
        require_once(dirname(dirname(__FILE__)) . '/tools/PlansTools.php');
        $outcome['submissionurl'] = PlansTools::createSubmissionUrlForCompletedTaskWithOutcome($task);
    }
    $task->set('completed', (int) !$task->get('completed'));
    $task->commit();
    $outcome['status'] = $task->get('completed');
    $outcome['classes'] = $task->get('completed') ? 'icon-check-square text-success' : 'icon-square';
}
catch (\Exception $e) {
    json_reply(true, $e->getMessage());
    die;
}
json_reply(false, $outcome);
