<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype/groupviews
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * returns group views in a given group id
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

safe_require('blocktype', 'groupviews');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');

$offset = param_integer('offset', 0);
$groupid = param_integer('group');
$editing = param_boolean('editing', false);

$group_homepage_view = group_get_homepage_view($groupid);
$bi = group_get_homepage_view_groupview_block($groupid);

if (!can_view_view($group_homepage_view)) {
    json_reply(true, get_string('accessdenied', 'error'));
}

$configdata = $bi->get('configdata');
if (!isset($configdata['showgroupviews'])) {
    $configdata['showgroupviews'] = 1;
}
$limit = isset($configdata['count']) ? intval($configdata['count']) : 5;
$limit = ($limit > 0) ? $limit : 5;

// Sortorder: Group homepage should be first, then sort by sortorder
$sort = array(
        array(
                'column' => "type='grouphomepage'",
                'desc' => true
        )
);
// Find out what order to sort them by (default is titles)
if (!isset($configdata['sortgroupviewsby']) || $configdata['sortgroupviewsby'] == PluginBlocktypeGroupViews::SORTBY_TITLE) {
    $sort[] = array('column' => 'title');
}
else {
    $sort[] = array('column' => 'mtime', 'desc' => true);
}
$groupviews = (array)View::view_search(null, null, (object) array('group' => $groupid),
                                      null, $limit, $offset, true, $sort, null,
                                      false, null, null, null, null, true);
foreach ($groupviews['data'] as &$view) {
    if (empty($view['displaytitle'])) {
        $view['displaytitle'] = $view['title']; // add for collections
    }
    if (isset($view['template']) && $view['template']) {
        $collid = !empty($view['collid']) ? $view['collid'] : null;
        $view['form'] = pieform(create_view_form(null, null, $view['id'], $collid, $collid));
    }
}
if (!empty($configdata['showgroupviews']) && isset($groupviews)) {
    $baseurl = $group_homepage_view->get_url();
    $baseurl .= (strpos($baseurl, '?') === false ? '?' : '&') . 'group=' . $groupid;
    $pagination = array(
        'baseurl'    => $baseurl,
        'id'         => 'groupviews_pagination',
        'datatable'  => 'groupviewlist',
        'jsonscript' => 'blocktype/groupviews/groupviews.json.php',
        'resultcounttextsingular' => get_string('view', 'view'),
        'resultcounttextplural'   => get_string('views', 'view'),
    );
    PluginBlocktypeGroupViews::render_items($groupviews, 'blocktype:groupviews:groupviewssection.tpl', $configdata, $pagination);
}

json_reply(false, array('data' => $groupviews));
