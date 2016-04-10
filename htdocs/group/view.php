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
define('MENUITEM', 'groups/info');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
require_once('searchlib.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once(get_config('libroot') . 'view.php');
safe_require('artefact', 'file');

if ($urlid = param_alphanumext('homepage', null)) {
    define('GROUPURLID', $urlid);
    $group = group_current_group();
}
else {
    define('GROUP', param_integer('id'));
    $group = group_current_group();
}

if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}

define('TITLE', $group->name);
define('SUBSECTIONHEADING', get_string('about'));
$group->role = group_user_access($group->id);

// logged in user can do stuff
if ($USER->is_logged_in()) {
    $afterjoin = param_variable('next', 'view');
    if ($group->role) {
        if ($group->role == 'admin') {
            $group->membershiptype = 'admin';
            $group->requests = count_records('group_member_request', 'group', $group->id);
        }
        else {
            $group->membershiptype = 'member';
        }
        $group->canleave = group_user_can_leave($group->id);
    }
    else if ($invite = get_record('group_member_invite', 'group', $group->id, 'member', $USER->get('id'))) {
        $group->membershiptype = 'invite';
        $group->invite = group_get_accept_form('invite', $group->id, $afterjoin);
    }
    else if ($group->jointype == 'open') {
        $group->groupjoin = group_get_join_form('joingroup', $group->id, $afterjoin);
    }
    else if ($group->request
             and $request = get_record('group_member_request', 'group', $group->id, 'member', $USER->get('id'))) {
        $group->membershiptype = 'request';
    }
}

$editwindow = group_format_editwindow($group);

$view = group_get_homepage_view($group->id);
$viewcontent = $view->build_rows(); // Build content before initialising smarty in case pieform elements define headers.

$headers = array();
if ($group->public) {
    $feedlink = get_config('wwwroot') . 'interaction/forum/atom.php?type=g&id=' . $group->id;
    $headers[] = '<link rel="alternate" type="application/atom+xml" href="' . $feedlink . '">';
}

$javascript = array('paginator');
$blocktype_js = $view->get_all_blocktype_javascript();
$javascript = array_merge($javascript, $blocktype_js['jsfiles']);
$inlinejs = "addLoadEvent( function() {\n" . join("\n", $blocktype_js['initjs']) . "\n});";

$headers = array_merge($headers, $view->get_all_blocktype_css());

// Set up skin, if the page has one
$viewskin = $view->get('skin');
$owner    = $view->get('owner');
$issiteview = $view->get('institution') == 'mahara';
if ($viewskin && get_config('skins') && can_use_skins($owner, false, $issiteview) && (!isset($THEME->skins) || $THEME->skins !== false)) {
    $skin = array('skinid' => $viewskin, 'viewid' => $view->get('id'));
}
else {
    $skin = false;
}

$smarty = smarty(
    $javascript,
    $headers,
    array(),
    array(
        'stylesheets' => array('style/views.css'),
        'skin' => $skin,
    )
);


$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('viewid', $view->get('id'));
$smarty->assign('viewcontent', $viewcontent);
$smarty->assign('group', $group);
$smarty->assign('editwindow', $editwindow);
$smarty->assign('returnto', 'view');
$smarty->assign('SUBPAGETOP', 'group/groupuserstatus.tpl');
$smarty->assign('headingclass', 'page-header');
$smarty->display('group/view.tpl');
