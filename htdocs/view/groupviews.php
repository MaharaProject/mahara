<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC_ACCESS', 1);
define('MENUITEM', 'engage/index');
define('MENUITEM_SUBPAGE', 'views');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'groupviews');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');

$offset = param_integer('offset', 0);
$urlparams = array();

define('GROUP', param_integer('group'));
define('SUBSECTIONHEADING', get_string('Viewscollections1', 'view'));
$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}

define('TITLE', $group->name . ' - ' . get_string('groupviews1', 'view'));

$role = group_user_access($group->id);
$can_edit = $role && group_role_can_edit_views($group, $role);

// If the user can edit group views, show a page similar to the my views
// page, otherwise just show a list of the views owned by this group that
// are visible to the user.
if (!$can_edit) {

    $setlimit = true;
    $limit = param_integer('limit', 0);
    $limit = user_preferred_limit($limit);
    $searchform = false;
    $data = View::view_search(null, null, (object) array('group' => $group->id), null, $limit, $offset,
                                             true, null, null, false, null, null,
                                             null, null, true);
    $viewdata = $data->data;
    View::get_extra_view_info($viewdata, false);
    View::get_extra_collection_info($viewdata, false, 'collid');
    require_once('collection.php');
    if ($viewdata) {
        foreach ($viewdata as $id => &$item) {
            $item['uniqueid'] = 'u' . $item['id'] . '_' . $item['collid'];
            $item['vctime'] = $item['ctime'];
            $item['vmtime'] = $item['mtime'];
            if (!empty($item['collid'])) {
                $collobj = new Collection($item['collid']);
                $item['displaytitle'] = $collobj->get('name');
                $item['collviews'] = $collobj->views();
                $item['numviews'] = $item['numpages'];
                if ($collobj->has_framework()) {
                    $item['framework'] = $collobj->collection_nav_framework_option();
                }
                if ($collobj->has_progresscompletion()) {
                    $item['progresscompletion'] = $collobj->collection_nav_progresscompletion_option();
                }
                if ($collobj->has_outcomes()) {
                    $item['outcomes'] = $collobj->collection_nav_outcomes_option();
                }
            }
        }
    }

    $data->data  = $viewdata;
    // Add a copy view form for all templates in the list
    foreach ($data->data as &$v) {
        if ($v['template']) {
            $v['copyform'] = true;
        }
    }

    $pagination = build_showmore_pagination(array(
        'count' => $data->count,
        'limit' => $limit,
        'offset' => $offset,
        'extra' => array('group' => $group->id),
        'databutton' => 'showmorebtn',
        'jsonscript' => 'json/viewlist.php',
        'orderby' => 'atoz',
    ));
}
else {
    list($searchform, $data, $pagination) = View::views_by_owner($group->id);
}

$js = <<< EOF
jQuery(function ($) {
    {$pagination['javascript']}
    showmatchall();
    if ($('#searchresultsheading').length) {
        $('#searchresultsheading').addClass('hidefocus')
            .prop('tabIndex', -1)
            .trigger("focus");
    }
});
EOF;

$urlparamsstr = '';
$outcomesgroup = false;
if (!empty($group->id)) {
    $urlparams['group'] = $group->id;
    $urlparamsstr = '&' . http_build_query($urlparams);
    $outcomesgroup = is_outcomes_group($group->id);
}

$data->data = $data->data ?: new StdClass();
foreach ($data->data as $portfolioindex => $portfolio) {
    // Make sure empty collection has collection object associated with it
    if (empty($portfolio['collection']) && !empty($portfolio['collid'])) {
        $data->data[$portfolioindex]['collection'] = new Collection($portfolio['collid']);
    }
}

$smarty = smarty(array('paginator'));
setpageicon($smarty, 'icon-layer-group');
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('views', $data->data);
$smarty->assign('headingclass', 'page-header');
$smarty->assign('urlparamsstr', $urlparamsstr);
$smarty->assign('pagination', $pagination['html']);

if (!$can_edit) {
    $smarty->assign('noedit', true);
}
$smarty->assign('group', $group->id);
$smarty->assign('query', param_variable('query', null));
$smarty->assign('querystring', get_querystring());
$smarty->assign('sitetemplate', View::SITE_TEMPLATE);
$smarty->assign('editlocked', $role == 'admin');
$smarty->assign('role', $role);
$smarty->assign('outcomesgroup', $outcomesgroup);
$html = $smarty->fetch('view/indexresults.tpl');
$smarty->assign('viewresults', $html);
$smarty->assign('searchform', $searchform);
$smarty->display('view/index.tpl');
