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
define('PUBLIC', 1);
define('MENUITEM', 'groups/views');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'groupviews');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');
require_once('pieforms/pieform.php');

define('GROUP', param_integer('group'));
$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}

define('TITLE', $group->name . ' - ' . get_string('groupviews', 'view'));

$role = group_user_access($group->id);
$can_edit = $role && group_role_can_edit_views($group, $role);

// If the user can edit group views, show a page similar to the my views
// page, otherwise just show a list of the views owned by this group that
// are visible to the user.

if (!$can_edit) {

    $limit   = param_integer('limit', 5);
    $offset  = param_integer('offset', 0);

    $data = View::view_search(null, null, (object) array('group' => $group->id), null, $limit, $offset);
    // Add a copy view form for all templates in the list
    foreach ($data->data as &$v) {
        if ($v['template']) {
            $v['copyform'] = pieform(create_view_form(null, null, $v['id']));
        }
    }

    $pagination = build_pagination(array(
        'url' => get_config('wwwroot') . 'view/groupviews.php?group='.$group->id,
        'count' => $data->count,
        'limit' => $limit,
        'offset' => $offset,
    ));
    $js = <<< EOF
addLoadEvent(function () {
    p = {$pagination['javascript']}
});
EOF;

    $smarty = smarty(array('paginator'));
    $smarty->assign('INLINEJAVASCRIPT', $js);
    $smarty->assign('views', $data->data);
    $smarty->assign('pagination', $pagination['html']);
    $smarty->display('view/groupviews.tpl');
    exit;

}

list($searchform, $data, $pagination) = View::views_by_owner($group->id);
$js = <<< EOF
addLoadEvent(function () {
    p = {$pagination['javascript']}
});
EOF;

$createviewform = pieform(create_view_form($group->id));

$smarty = smarty(array('paginator'));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('editlocked', $role == 'admin');
$smarty->assign('views', $data->data);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('searchform', $searchform);
$smarty->assign('createviewform', $createviewform);
$smarty->display('view/index.tpl');
