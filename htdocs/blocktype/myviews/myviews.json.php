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

safe_require('blocktype', 'myviews');
require_once(get_config('libroot') . 'view.php');

$offset = param_integer('offset', 0);
$viewid = param_integer('view');
$editing = param_boolean('editing', false);
$limit = param_integer('limit', 10);

$dashboard = new View($viewid);

if (!can_view_view($dashboard)) {
    json_reply(true, get_string('accessdenied', 'error'));
}

$views = View::view_search(
        null, // $query
        null, // $ownerquery
        (object) array('owner' => $dashboard->get('owner')), // $ownedby
        null, // $copyableby
        $limit, // $limit
        $offset, // $offset
        true, // $extra
        null, // $sort
        array('portfolio'), // $types
        null, // $collection
        null, // $accesstypes
        null, // $tag
        null, // $viewid
        null, // $excludeowner
        true // $groupbycollection
);
$views = (array)$views;
$baseurl = $dashboard->get_url();
$baseurl .= (strpos($baseurl, '?') === false ? '?' : '&') . 'view=' . $viewid . '&editing=' . $editing;
$pagination = array(
    'baseurl'    => $baseurl,
    'id'         => 'myviews_pagination',
    'datatable'  => 'myviewlist',
    'jsonscript' => 'blocktype/myviews/myviews.json.php',
    'resultcounttextsingular' => get_string('view', 'view'),
    'resultcounttextplural'   => get_string('views', 'view'),
);
PluginBlocktypeMyViews::render_items($views, 'blocktype:myviews:myviewspaginator.tpl', array(), $pagination);

json_reply(false, array('data' => $views));
