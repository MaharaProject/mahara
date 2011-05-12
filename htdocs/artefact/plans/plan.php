<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */


define('INTERNAL', 1);
define('MENUITEM', 'content/plans');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'plans');
define('SECTION_PAGE', 'plans');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'plans');

define('TITLE', get_string('tasks','artefact.plans'));

$id = param_integer('id');

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

$plan = new ArtefactTypePlan($id);
if (!$USER->can_edit_artefact($plan)) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}


$tasks = ArtefactTypeTask::get_tasks($plan->get('id'), $offset, $limit);
ArtefactTypeTask::build_tasks_list_html($tasks);

$js = <<< EOF
addLoadEvent(function () {
    {$tasks['pagination_js']}
});
EOF;

$smarty = smarty(array('paginator'));
$smarty->assign_by_ref('tasks', $tasks);
$smarty->assign_by_ref('plan', $id);
$smarty->assign('strnotasksaddone',
    get_string('notasksaddone', 'artefact.plans',
    '<a href="' . get_config('wwwroot') . 'artefact/plans/new.php?id='.$plan->get('id').'">', '</a>'));
$smarty->assign('PAGEHEADING', get_string("planstasks", "artefact.plans",$plan->get('title')));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('artefact:plans:plan.tpl');
