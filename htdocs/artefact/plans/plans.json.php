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

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'plans');

if (!PluginArtefactPlans::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('Plans','artefact.plans')));
}

$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);

if (param_exists('group')) {
    define('GROUP', param_integer('group'));
    $group = group_current_group();
    if (!ArtefactTypePlan::user_can_view_groupplans($group)) {
        json_reply(true, get_string('accessdenied', 'error'));
    }
    $canedit = ArtefactTypePlan::user_can_edit_groupplan($group);
}
else {
    $group = null;
    $canedit = true;    // User always can edit his private plans
}

$plans = ArtefactTypePlan::get_plans($offset, $limit, $group);
ArtefactTypePlan::build_plans_list_html($plans, $canedit);

json_reply(false, (object) ['message' => false, 'data' => $plans]);
