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
 * returns shared views in a given group id
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
if (!isset($configdata['showsharedviews'])) {
    $configdata['showsharedviews'] = 1;
}
$limit = isset($configdata['count']) ? intval($configdata['count']) : 5;
$limit = ($limit > 0) ? $limit : 5;
// Find out what order to sort them by (default is titles)
if (!isset($configdata['sortsharedviewsby']) || $configdata['sortsharedviewsby'] == PluginBlocktypeGroupViews::SORTBY_TITLE) {
    $sortsharedviewsby = 'v.title';
}
else {
    $sortsharedviewsby = 'v.mtime DESC';
}

// For group members, display a list of views that others have
// shared to the group
$hidesubmitted = group_user_can_assess_submitted_views($groupid, $USER->get('id'));
if (empty($configdata['showsharedviews'])) {
   $sharedviews = array(
        'data'   => array(),
        'count'  => 0,
        'limit'  => $limit,
        'offset' => 0
    );
}
else {
    $sharedviews = (array) View::get_sharedviews_data(
            $limit,
            $offset,
            $groupid,
            ($configdata['showsharedviews'] == 2 ? false : true),
            $sortsharedviewsby,
            $hidesubmitted
    );
    foreach ($sharedviews['data'] as &$view) {
        if (!$editing && isset($view['template']) && $view['template']) {
            $view['form'] = pieform(create_view_form($group, null, $view->id));
        }
    }
}

if (!empty($configdata['showsharedviews'])) {
    $baseurl = $group_homepage_view->get_url();
    $baseurl .= (strpos($baseurl, '?') === false ? '?' : '&') . 'group=' . $groupid . '&editing=' . $editing;
    $pagination = array(
        'baseurl'    => $baseurl,
        'id'         => 'sharedviews_pagination',
        'datatable'  => 'sharedviewlist',
        'jsonscript' => 'blocktype/groupviews/sharedviews.json.php',
        'resultcounttextsingular' => get_string('view', 'view'),
        'resultcounttextplural'   => get_string('views', 'view'),
    );
    PluginBlocktypeGroupViews::render_items($sharedviews, 'blocktype:groupviews:sharedviews.tpl', $configdata, $pagination);
}

json_reply(false, array('data' => $sharedviews));
