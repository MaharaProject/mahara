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
define('PUBLIC', 1);
define('JSON', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
safe_require('artefact', 'plans');
require_once(get_config('docroot') . 'blocktype/lib.php');
require_once(get_config('docroot') . 'artefact/plans/blocktype/plans/lib.php');

$offset = param_integer('offset', 0);
$limit = param_integer('limit', 10);
$editing = param_variable('editing', false);
$artefactid = param_integer('artefact', null);
$blockid = param_integer('block', null);

$options = array();
$pagination = array();
$template = '';
if ($blockid && !$artefactid) {
    $bi = new BlockInstance($blockid);
    if (!can_view_view($bi->get('view'))) {
        json_reply(true, get_string('accessdenied', 'error'));
    }
    $options = $configdata = $bi->get('configdata');
    $options['view'] = $bi->get('view');

    // If block sets limit use that instead
    $limit = !empty($configdata['count']) ? $configdata['count'] : $limit;
    $planId = param_integer('planid');
    $plan = new ArtefactTypePlan($planId);
    $tasks = ArtefactTypeTask::get_tasks($plan, $offset, $limit);

    $template = 'artefact:plans:view/plantasks.tpl';
    $baseurl = $bi->get_view()->get_url();
    $baseurl .= ((false === strpos($baseurl, '?')) ? '?' : '&') . 'block=' . $blockid . '&planid=' . $planId . '&editing=' . $editing;
    $pagination = [
        'baseurl'   => $baseurl,
        'id'        => 'block' . $blockid . '_plan' . $planId . '_pagination',
        'datatable' => 'tasklist_' . $blockid . '_plan' . $planId,
        'jsonscript' => 'artefact/plans/block/tasks.json.php',
    ];
}

ArtefactTypeTask::render_tasks($tasks, $template, $options, $pagination, $editing);

json_reply(false, (object) ['message' => false, 'data' => $tasks]);
