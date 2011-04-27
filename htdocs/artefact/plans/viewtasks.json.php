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
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'plans');
require_once(get_config('docroot') . 'blocktype/lib.php');
require_once(get_config('docroot') . 'artefact/plans/blocktype/plans/lib.php');

$offset = param_integer('offset', 0);
$limit = param_integer('limit', 10);

if ($blockid = param_integer('block', null)) {
    $bi = new BlockInstance($blockid);
    if (!can_view_view($bi->get('view'))) {
        json_reply(true, get_string('accessdenied', 'error'));
    }
    $options = $configdata = $bi->get('configdata');

    $tasks = ArtefactTypeTask::get_tasks($configdata['artefactid'], $offset, $limit);

    $template = 'artefact:plans:taskrows.tpl';
    $pagination = array(
        'baseurl'   => $bi->get_view()->get_url() . '&block=' . $blockid,
        'id'        => 'block' . $blockid . '_pagination',
        'datatable' => 'tasktable_' . $blockid,
        'jsonscript' => 'artefact/plans/viewtasks.json.php',
    );
}
else {
    $planid = param_integer('artefact');
    $viewid = param_integer('view');
    if (!can_view_view($viewid)) {
        json_reply(true, get_string('accessdenied', 'error'));
    }
    $options = array('viewid' => $viewid);
    $tasks = ArtefactTypeTask::get_tasks($planid, $offset, $limit);

    $template = 'artefact:plans:taskrows.tpl';
    $baseurl = get_config('wwwroot') . 'view/artefact.php?artefact=' . $planid . '&view=' . $options['viewid'];
    $pagination = array(
        'baseurl' => $baseurl,
        'id' => 'task_pagination',
        'datatable' => 'tasklist',
        'jsonscript' => 'artefact/plans/viewtasks.json.php',
    );

}
ArtefactTypeTask::render_tasks($tasks, $template, $options, $pagination);

json_reply(false, (object) array('message' => false, 'data' => $tasks));
