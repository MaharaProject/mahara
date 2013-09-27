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
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'plans');

$plan = param_integer('id');
$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);

if (!$USER->can_edit_artefact(new ArtefactTypePlan($plan))) {
    json_reply(true, get_string('accessdenied', 'error'));
}

$tasks = ArtefactTypeTask::get_tasks($plan, $offset, $limit);
ArtefactTypeTask::build_tasks_list_html($tasks);

json_reply(false, (object) array('message' => false, 'data' => $tasks));
