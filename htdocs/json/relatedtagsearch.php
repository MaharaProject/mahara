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
define('PUBLIC', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('searchlib.php');
require_once('view.php');
require_once('collection.php');

$tag = param_variable('tag');
$tag = urldecode($tag);
$viewid = param_integer('view');

$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$sort   = param_alpha('sort', 'name');
$type   = param_alpha('type', null);

// Check view id to see if we are allowed access the view and the view is owned by a user
if ($viewid) {
    $view = new View($viewid);
    $owner = $view->get('owner');
    if (!can_view_view($view) || !$owner) {
        $errorstr = get_string('accessdenied', 'error');
        json_reply(true, $errorstr);
    }
}

// Now we have a valid view lets get the user id
$user = new User();
$user->find_by_id($owner);
$displayname = display_name($user);

$userobj  = (object) array('type' => 'user', 'id' => $owner, 'owner' => $owner);

if ($USER->is_logged_in()) {
    // Find all views owned by owner shared to current user
    $rawdata = View::view_search(null, null, $userobj);
}
else {
    $rawdata = new stdClass();
    // when logged out we restrict the tags to the page/collection being viewed
    // Check to see if it is part of a collection
    if ($view->get('collection')) {
        $rawdata->ids = array();
        $viewlist = $view->get('collection')->get('views');
        foreach ($viewlist['views'] as $v) {
            $rawdata->ids[] = $v->view;
        }
        $relatedtitle = $view->get('collection')->get('name');
    }
    else {
        // Otherwise just look at the current view
        $rawdata->ids = array($view->get('id'));
        $relatedtitle = $view->get('title');
    }
}

// Now get the subset where either the view / collection has the tag or the artefact(s) on the view have the tag
$data = get_portfolio_items_by_tag($tag, $userobj, $limit, $offset, $sort, $type, true, $rawdata->ids);
$data->isrelated = true;
$data->viewid = $view->get('id');
build_portfolio_search_html($data);

$data->tagdisplay = hsc(str_shorten_text($tag, 50));
$data->tagurl = urlencode($tag);

json_reply(false, array('data' => $data));
