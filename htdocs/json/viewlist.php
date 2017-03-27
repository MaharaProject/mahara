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
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');

$offset = param_integer('offset', 0);
$limit = param_integer('limit', 12);
$setlimit = param_boolean('setlimit', false);
$groupid = param_integer('group', null);
$institution = param_alpha('institution', null);
$orderby = param_alphanum('orderby', null);

if (!empty($groupid)) {
    define('PUBLIC', 1);
    define('GROUP', param_integer('group'));
    require_once(get_config('docroot') . 'lib/group.php');
    $group = group_current_group();
    if (!is_logged_in() && !$group->public) {
        json_reply('local', get_string('accessdenied', 'error'));
    }

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
                }
            }
        }

        $data->data  = $viewdata;

        // Add a copy view form for all templates in the list
        foreach ($data->data as &$v) {
            if ($v['template']) {
                $v['copyform'] = pieform(create_view_form(null, null, $v['id']));
            }
        }

       $pagination = build_showmore_pagination(array(
            'count' => $data->count,
            'limit' => $limit,
            'offset' => $offset,
            'orderby' => 'atoz',
            'group' => $groupid,
            'databutton' => 'showmorebtn',
            'jsonscript' => 'json/viewlist.php',
        ));
    }
    else {
        list($searchform, $data, $pagination) = View::views_by_owner($group->id);
        $createviewform = pieform(create_view_form($group->id));
    }
}
else if (!empty($institution)) {
    if ($institution == 'mahara') {
        define('ADMIN', 1);
        $templateviews = View::get_site_template_views();
        list($searchform, $data, $pagination) = View::views_by_owner(null, 'mahara');
        if ($data->data && $offset == '0') {
            $data->data = array_merge($templateviews, $data->data);
        }
    }
    else {
        define('INSTITUTIONALADMIN', 1);
        list($searchform, $data, $pagination) = View::views_by_owner(null, $institution);
    }
}
else {
    list($searchform, $data, $pagination) = View::views_by_owner();
}

$smarty = smarty_core();
$smarty->assign('views', $data->data);
$smarty->assign('sitetemplate', View::SITE_TEMPLATE);
$smarty->assign('pagination', $pagination['html']);
if ($groupid && !$can_edit) {
    $smarty->assign('noedit', true);
}
else if ($groupid) {
    $smarty->assign('editlocked', $role == 'admin');
}
$smarty->assign('querystring', get_querystring());
$html = $smarty->fetch('view/indexresults.tpl');

json_reply(false, array(
    'message' => null,
    'data' => array(
        'tablerows' => $html,
        'pagination_js' => $pagination['javascript'],
        'count' => $data->count,
        'results' => $data->count . ' ' . ($data->count == 1 ? get_string('result') : get_string('results')),
        'offset' => $offset,
        'query' => (param_variable('query', null)),
        'setlimit' => $setlimit,
    )
));
