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

define('PUBLIC', 1);
define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('collection.php');

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 0);
$limit = user_preferred_limit($limit);
$setlimit = param_boolean('setlimit', false);
$owner = null;
$groupid = param_integer('group', 0);
$institutionname = param_alphanum('institution', false);
$urlparams = array();

if (!empty($groupid)) {
    require_once('group.php');
    $group = group_current_group();
    // Check if user can edit group collections <-> user can edit group views
    $role = group_user_access($group->id);
    $canedit = $role && group_role_can_edit_views($group, $role);
    if (!$role) {
        json_reply('local', get_string('accessdenied', 'error'));
    }
    $urlparams['group'] = $groupid;
}
else if (!empty($institutionname)) {
    if ($institutionname == 'mahara') {
        // Check if user is a site admin
        $canedit = $USER->get('admin');
        if (!$canedit) {
            json_reply('local', get_string('accessdenied', 'error'));
        }
    }
    else {
        // Check if user is a institution admin
        $canedit = $USER->get('admin') || $USER->is_institutional_admin();
        if (!$canedit) {
            json_reply('local', get_string('accessdenied', 'error'));
        }
    }
    $urlparams['institution'] = $institutionname;
}
else {
    $owner = $USER->get('id');
    $canedit = true;
}
$baseurl = get_config('wwwroot') . 'collection/index.php';
if ($urlparams) {
    $baseurl .= '?' . http_build_query($urlparams);
}

$data = Collection::get_mycollections_data($offset, $limit, $owner, $groupid, $institutionname);
foreach ($data->data as $value) {
    $collection = new Collection($value->id);
    $views = $collection->get('views');
    if (!empty($views)) {
        $value->views = $views['views'];
    }
    if (is_plugin_active('framework', 'module') && $collection->has_framework()) {
        $framework = new Framework($collection->get('framework'));
        $value->frameworkname = $framework->get('name');
    }
}
$smarty = smarty_core();
$smarty->assign('canedit', $canedit);
$smarty->assign('collections', $data->data);
$html = $smarty->fetch('collection/collectionresults.tpl');

$pagination = build_pagination(array(
    'id' => 'collectionslist_pagination',
    'class' => 'center',
    'url' => $baseurl,
    'count' => $data->count,
    'limit' => $data->limit,
    'offset' => $data->offset,
    'datatable' => 'mycollections',
    'jsonscript' => 'collection/index.json.php',
    'setlimit' => $setlimit,
    'jumplinks' => 6,
    'numbersincludeprevnext' => 2,
    'resultcounttextsingular' => get_string('collection', 'collection'),
    'resultcounttextplural' => get_string('collections', 'collection'),
));

json_reply(false, array(
    'message' => null,
    'data' => array(
        'tablerows' => $html,
        'pagination' => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
        'count' => $data->count,
        'results' => $data->count . ' ' . ($data->count == 1 ? get_string('result') : get_string('results')),
        'offset' => $data->offset,
        'setlimit' => $setlimit,
        'institution' => $institutionname,
        'group' => $groupid,
    )
));
