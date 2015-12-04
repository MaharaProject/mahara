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
 * returns all submissions to a given group id
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

if (!can_view_view($group_homepage_view)
    || !group_user_can_assess_submitted_views($groupid, $USER->get('id'))) {
    json_reply(true, get_string('accessdenied', 'error'));
}

$configdata = $bi->get('configdata');
if (!isset($configdata['showsubmitted'])) {
    $configdata['showsubmitted'] = 1;
}

if (empty($configdata['showsubmitted'])) {
    $allsubmitted = array(
        'data'   => array(),
        'count'  => 0,
        'limit'  => $limit,
        'offset' => 0
    );
}
else {
    $limit = isset($configdata['count']) ? intval($configdata['count']) : 5;
    $limit = ($limit > 0) ? $limit : 5;
    if (!isset($configdata['sortsubmittedby']) || $configdata['sortsubmittedby'] == PluginBlocktypeGroupViews::SORTBY_TITLE) {
        $sortsubmittedby = 'c.name, v.title';
    }
    else {
        $sortsubmittedby = 'c.submittedtime DESC, v.submittedtime DESC';
    }

    list($collections, $views) = View::get_views_and_collections(null, null, null, null, false, $groupid, $sortsubmittedby);
    $allsubmitted = array_merge(array_values($collections), array_values($views));
    $allsubmitted = array(
        'data'   => array_slice($allsubmitted, $offset, $limit),
        'count'  => count($allsubmitted),
        'limit'  => $limit,
        'offset' => $offset,
    );

    $baseurl = $group_homepage_view->get_url();
    $baseurl .= (strpos($baseurl, '?') === false ? '?' : '&') . 'group=' . $groupid . '&editing=' . $editing;
    $pagination = array(
        'baseurl'    => $baseurl,
        'id'         => 'allsubmitted_pagination',
        'datatable'  => 'allsubmissionlist',
        'jsonscript' => 'blocktype/groupviews/allsubmissions.json.php',
        'resultcounttextsingular' => get_string('vieworcollection', 'view'),
        'resultcounttextplural'   => get_string('viewsandcollections', 'view'),
    );
    PluginBlocktypeGroupViews::render_items($allsubmitted, 'blocktype:groupviews:allsubmissions.tpl', $configdata, $pagination);
}

json_reply(false, array('data' => $allsubmitted));
