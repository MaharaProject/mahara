<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
require(dirname(dirname(dirname(__FILE__))). '/init.php');

$taskid = param_integer('taskid');

$task = get_record_sql('SELECT *, CASE WHEN completiondate < NOW() THEN -1 ELSE 1 END AS state
                        FROM {artefact_plans_task}
                        WHERE artefact = ?', array($taskid));
$artefacttask = get_record('artefact', 'id', $task->artefact);
// Check if the user checking the task is the owner of the plan.
if ($artefacttask->owner == $USER->get('id')) {
    if ($task->completed) {
        $task->completed = 0;
        $task->state = ($task->state == 1) ? 0 : -1; // Set state to not done (to do in future) vs not done (in past)
        update_record('artefact_plans_task', $task, 'artefact');
    }
    else {
        $task->completed = 1;
        $task->state = 1;
        update_record('artefact_plans_task', $task, 'artefact');
    }
    json_reply(false, (object) array('message' => false, 'data' => $task));
}
else {
    json_reply(true, get_string('accessdenied', 'error'));
}
