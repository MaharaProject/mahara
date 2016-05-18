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

$offset = param_integer('offset', 0);

define('GROUP', param_integer('group'));
define('SUBSECTIONHEADING', get_string('Views', 'view'));
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

    $setlimit = true;
    $limit = param_integer('limit', 0);
    $limit = user_preferred_limit($limit);
    $offset = param_integer('offset', 0);

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
        'setlimit' => $setlimit,
        'datatable' => 'myviews',
        'jsonscript' => 'json/viewlist.php',
        'jumplinks' => 6,
        'numbersincludeprevnext' => 2,
    ));
}
else {
    list($searchform, $data, $pagination) = View::views_by_owner($group->id);
    $createviewform = pieform(create_view_form($group->id));
}
$js = <<< EOF
addLoadEvent(function () {
    p = {$pagination['javascript']}
EOF;
if ($offset > 0) {
    $js .= <<< EOF
    if ($('groupviews')) {
        getFirstElementByTagAndClassName('a', null, 'groupviews').focus();
    }
    if ($('myviews')) {
        getFirstElementByTagAndClassName('a', null, 'myviews').focus();
    }
EOF;
}
else {
    $js .= <<< EOF
    if ($('searchresultsheading')) {
        addElementClass('searchresultsheading', 'hidefocus');
        setNodeAttribute('searchresultsheading', 'tabIndex', -1);
        $('searchresultsheading').focus();
    }
EOF;
}
$js .= '});';

$smarty = smarty(array('paginator'));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('views', $data->data);
$smarty->assign('headingclass', 'page-header');
$smarty->assign('pagination', $pagination['html']);

if (!$can_edit) {
    $html = $smarty->fetch('view/indexgroupresults.tpl');
    $smarty->assign('viewresults', $html);
    $smarty->display('view/groupviews.tpl');
}
else {
    $smarty->assign('query', param_variable('query', null));
    $smarty->assign('querystring', get_querystring());
    $smarty->assign('editlocked', $role == 'admin');
    $html = $smarty->fetch('view/indexresults.tpl');
    $smarty->assign('viewresults', $html);
    $smarty->assign('searchform', $searchform);
    $smarty->assign('createviewform', $createviewform);
    $smarty->display('view/index.tpl');
}
