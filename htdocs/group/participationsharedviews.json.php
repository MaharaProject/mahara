<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Son Nguyen <son.nguyen@catalyst.net.nz>, Catalyst IT, NZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');

define('GROUP', param_integer('group'));

$wwwroot = get_config('wwwroot');

$limit = param_integer('limit', 0);
$limit = user_preferred_limit($limit, 'itemsperpage');
$offset = param_integer('offset', 0);
$sort = param_variable('sort', 'title');
$direction = param_variable('direction', 'asc');
$group = group_current_group();
$role = group_user_access($group->id);
if (!group_role_can_access_report($group, $role)) {
    json_reply(true, get_string('accessdenied', 'error'));
}

$limit = ($limit > 0) ? $limit : 10;

$sharedviews = View::get_participation_sharedviews_data($group->id, $sort, $direction, $limit, $offset);

$pagination = array(
    'baseurl'    => $wwwroot . 'group/report.php?group=' . $group->id . '&sort=' . $sort . '&direction=' . $direction,
    'id'         => 'sharedviews_pagination',
    'datatable'  => 'sharedviewsreport',
    'jsonscript' => 'group/participationsharedviews.json.php',
    'setlimit'   => true,
    'resultcounttextsingular' => get_string('view', 'view'),
    'resultcounttextplural'   => get_string('views', 'view'),
);

$sharedviews = View::render_participation_views($sharedviews, 'group/participationsharedviews.tpl', $pagination);

json_reply(false, array('data' => $sharedviews));
