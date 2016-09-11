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
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'collection');
define('SECTION_PAGE', 'index');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('collection.php');

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 0);
$limit = user_preferred_limit($limit);
$owner = null;
$groupid = param_integer('group', 0);
$institutionname = param_alphanum('institution', false);
$urlparams = array();

$pageIcon = 'icon-folder-open';

if (!empty($groupid)) {
    define('MENUITEM', 'groups/collections');
    define('GROUP', $groupid);
    define('SUBSECTIONHEADING', get_string('Collections', 'collection'));
    require_once('group.php');
    $group = group_current_group();
    // Check if user can edit group collections <-> user can edit group views
    $role = group_user_access($group->id);
    $canedit = $role && group_role_can_edit_views($group, $role);
    if (!$role) {
        throw new GroupAccessDeniedException(get_string('cantlistgroupcollections', 'collection'));
    }

    define('TITLE', $group->name);
    $urlparams['group'] = $groupid;
    $pageIcon = '';
}
else if (!empty($institutionname)) {
    if ($institutionname == 'mahara') {
        define('ADMIN', 1);
        define('MENUITEM', 'configsite/collections');
        define('TITLE', get_string('sitecollections', 'collection'));
        // Check if user is a site admin
        $canedit = $USER->get('admin');
        if (!$canedit) {
            throw new AccessDeniedException(get_string('cantlistinstitutioncollections', 'collection'));
        }
    }
    else {
        define('INSTITUTIONALADMIN', 1);
        define('MENUITEM', 'manageinstitutions/institutioncollections');
        define('TITLE', get_string('institutioncollections', 'collection'));

        $pageIcon = 'icon icon-university';
        // Check if user is a institution admin
        $canedit = $USER->get('admin') || $USER->is_institutional_admin();
        if (!$canedit) {
            throw new AccessDeniedException(get_string('cantlistinstitutioncollections', 'collection'));
        }
        require_once('institution.php');
        // Get list of availlable institutions
        $s = institution_selector_for_page($institutionname, get_config('wwwroot') . 'collection/index.php');
        $institutionname = $s['institution'];
        if ($institutionname === false) {
            $smarty = smarty();
            $smarty->display('admin/users/noinstitutions.tpl');
            exit;
        }
    }
    $urlparams['institution'] = $institutionname;
}
else {
    define('MENUITEM', 'myportfolio/collection');
    $owner = $USER->get('id');
    $canedit = true;
    define('TITLE', get_string('Collections', 'collection'));
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

$pagination = build_pagination(array(
    'id' => 'collectionslist_pagination',
    'class' => 'center',
    'url' => $baseurl,
    'count' => $data->count,
    'limit' => $data->limit,
    'offset' => $data->offset,
    'datatable' => 'mycollections',
    'jsonscript' => 'collection/index.json.php',
    'setlimit' => true,
    'jumplinks' => 6,
    'numbersincludeprevnext' => 2,
    'resultcounttextsingular' => get_string('collection', 'collection'),
    'resultcounttextplural' => get_string('collections', 'collection'),
));

$smarty = smarty(array('paginator'), array(), array(), array(
    'PAGEICON'=>$pageIcon
));
$urlparamsstr = '';
if ($urlparams) {
    $urlparamsstr = '&' . http_build_query($urlparams);
}
if ($canedit) {
    $smarty->assign('addonelink', get_config('wwwroot') . 'collection/edit.php?new=1' . $urlparamsstr);
}

if (!empty($institutionname) && ($institutionname != 'mahara')) {
    $smarty->assign('institution', $institutionname);
    $smarty->assign('institutionselector', $s['institutionselector']);
    $smarty->assign('INLINEJAVASCRIPT', $s['institutionselectorjs']);
}

setpageicon($smarty, $pageIcon);
$smarty->assign('canedit', $canedit);
$smarty->assign('urlparamsstr', $urlparamsstr);
$smarty->assign('collections', $data->data);
$html = $smarty->fetch('collection/collectionresults.tpl');
$smarty->assign('collectionhtml', $html);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('pagination_js', $pagination['javascript']);
$smarty->assign('headingclass', 'page-header');

$smarty->assign('SUBPAGETOP', 'collection/actions.tpl');
$smarty->display('collection/index.tpl');
