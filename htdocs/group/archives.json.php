<?php
/**
 * Group archive.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('view.php');
require_once('group.php');
require_once('searchlib.php');

define('GROUP', param_integer('group'));

$wwwroot = get_config('wwwroot');
$limit = param_integer('limit', 0);
$limit = user_preferred_limit($limit, 'itemsperpage');
$offset = param_integer('offset', 0);
$search = (object) array(
    'query' => trim(param_variable('query', '')),
    'sortby' => param_alpha('sortby', 'firstname'),
    'sortdir' => param_alpha('sortdir', 'asc'),
    'group' => param_integer('group'),
);

$group = group_current_group();
$role = group_user_access($group->id);
if (!group_role_can_access_archives($group, $role)) {
    throw new AccessDeniedException();
}

$offset = param_integer('offset', 0);
$limit = param_integer('limit', 10);

list($html, $columns, $pagination, $search) = build_group_archived_submissions_results($search, $offset, $limit);

json_reply(false, array(
    'message' => null,
    'data' => array(
        'tablerows' => $html,
        'pagination' => $pagination['html'],
        'pagination_js' => $pagination['javascript']
    )
));
