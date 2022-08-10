<?php
/**
 * Groups page.
 *
 * Allows creation of groups, search and shows list of my groups.
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
require_once('group.php');
require_once('searchlib.php');

$filter = param_alpha('filter', 'canjoin');
$offset = param_integer('offset', 0);
$groupsperpage = param_integer('limit', 10);
$groupcategory = param_signed_integer('groupcategory', 0);
$setlimit = param_boolean('setlimit', false);
$query = param_variable('query', '');

/* $searchmode will switch between 2 search funcs (with different queries)
*  $searchmode = 'find' uses search_group
*  $searchmode = 'mygroups' uses group_get_associated_groups
*/
$searchmode = 'find';

// check that the filter is valid, if not default to 'all'
if (in_array($filter, array('allmy', 'member', 'admin', 'invite', 'notmember', 'canjoin'))) {
    if ($filter == 'allmy' || $filter == 'admin' || $filter == 'member' || $filter == 'invite') {
        $searchmode = 'mygroups';
    }
    $type = $filter;
}
else { // all or some other text
    $filter = 'all';
    $type = 'all';
}

$groups = array();
if ($searchmode == 'mygroups') {
    $results = group_get_associated_groups($USER->get('id'), $type, $groupsperpage, $offset, $groupcategory, $query);
    $groups['data'] = isset($results['groups']) ? $results['groups'] : array();
    $groups['count'] = isset($results['count']) ? $results['count'] : 0;
}
else {
    if (is_isolated()) {
        if ($USER->get('admin') || $USER->get('staff')) {
             // show all groups because admin or staff (isolation overwrite)
            $groups = search_group($query, $groupsperpage, $offset, $type, $groupcategory);
        }
        else {
            // isolated and NOT ADMIN or Staff
            $groups = search_group($query, $groupsperpage, $offset, $type, $groupcategory, $USER->get('institutions'));
        }
    }
    else {
        // show all groups because of no isolation
        $groups = search_group($query, $groupsperpage, $offset, $type, $groupcategory);
    }
}

// gets more data about the groups found by search_group
// including type if the user is associated with the group in some way
if ($searchmode == 'find') {
    if ($groups['data']) {
        $groups['data'] = group_get_extended_data($groups['data']);
    }
}

group_prepare_usergroups_for_display($groups['data']);

$params = array();
$params['filter'] = $filter;
if ($groupcategory != 0) {
    $params['groupcategory'] = $groupcategory;
}
if ($query) {
    $params['query'] = $query;
}
$paramsurl = get_config('wwwroot') . 'group/index.php' . ($params ? ('?' . http_build_query($params)) : '');
$pagination = build_pagination(array(
    'url' => $paramsurl,
    'count' => $groups['count'],
    'limit' => $groupsperpage,
    'offset' => $offset,
    'datatable' => 'findgroups',
    'jsonscript' => 'group/index.json.php',
    'setlimit' => true,
    'jumplinks' => 6,
    'numbersincludeprevnext' => 2,
    'resultcounttext' => get_string('ngroups', 'group', $groups['count']),
));

$smarty = smarty_core();
$smarty->assign('groups', $groups['data']);
$smarty->assign('paramsurl', $paramsurl);
$html = $smarty->fetch('group/mygroupresults.tpl');

json_reply(false, array(
    'message' => null,
    'data' => array(
        'tablerows' => $html,
        'pagination' => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
        'count' => $groups['count'],
        'results' => $groups['count'] . ' ' . ($groups['count'] == 1 ? get_string('result') : get_string('results')),
        'offset' => $offset,
        'setlimit' => $setlimit,
    )
));
