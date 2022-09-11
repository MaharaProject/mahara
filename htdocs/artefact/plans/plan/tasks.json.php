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
define('JSON', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
safe_require('artefact', 'plans');

if (!PluginArtefactPlans::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('Plans','artefact.plans')));
}

$planId = param_integer('id');
$plan = new ArtefactTypePlan($planId);
$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);

if (param_exists('group')) {
    define('GROUP', param_integer('group'));
    $group = group_current_group();
    if (!$USER->can_view_artefact($plan)) {
        json_reply(true, get_string('accessdenied', 'error'));
    }
}
else {
    $group = null;
    if (!$USER->can_edit_artefact($plan)) {
        json_reply(true, get_string('accessdenied', 'error'));
    }
}

$canEdit = $USER->can_edit_artefact($plan);
$tasks = ArtefactTypeTask::get_tasks($plan, $offset, $limit);
ArtefactTypeTask::build_tasks_list_html($tasks, $canEdit);

json_reply(false, (object) ['message' => false, 'data' => $tasks]);
