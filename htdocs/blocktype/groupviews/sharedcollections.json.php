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
 * returns shared collections in a given group id
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

safe_require('blocktype', 'groupviews');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');

$offset = param_integer('offset', 0);
$groupid = param_integer('group');

$group_homepage_view = group_get_homepage_view($groupid);
$bi = group_get_homepage_view_groupview_block($groupid);

if (!can_view_view($group_homepage_view)) {
    json_reply(true, get_string('accessdenied', 'error'));
}

$configdata = $bi->get('configdata');
if (!isset($configdata['showsharedcollections'])) {
    $configdata['showsharedcollections'] = 1;
}

$limit = isset($configdata['count']) ? intval($configdata['count']) : 5;
$limit = ($limit > 0) ? $limit : 5;

$sharedcollections = (array)View::get_sharedcollections_data($limit, $offset, $groupid);
if (!empty($configdata['showsharedcollections']) && isset($sharedcollections)) {
    $baseurl = $group_homepage_view->get_url();
    $baseurl .= (strpos($baseurl, '?') === false ? '?' : '&') . 'group=' . $groupid;
    $pagination = array(
        'baseurl'    => $baseurl,
        'id'         => 'sharedcollections_pagination',
        'datatable'  => 'sharedcollectionlist',
        'jsonscript' => 'blocktype/groupviews/sharedcollections.json.php',
        'resultcounttextsingular' => get_string('collection', 'collection'),
        'resultcounttextplural'   => get_string('collections', 'collection'),
    );
    PluginBlocktypeGroupViews::render_items($sharedcollections, 'blocktype:groupviews:sharedcollections.tpl', $configdata, $pagination);
}

json_reply(false, array('data' => $sharedcollections));
