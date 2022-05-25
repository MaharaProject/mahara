<?php
/**
 * The group 'About' page.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('MENUITEM', 'engage/index');
define('MENUITEM_SUBPAGE', 'info');
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

if ($usetemplate = param_integer('usetemplate', null)) {
    // If a form has been submitted, build it now and pieforms will
    // call the submit function straight away
    pieform(create_view_form(null, null, $usetemplate, param_integer('copycollection', null)));
}

define('TITLE', $group->name);
define('SUBSECTIONHEADING', get_string('about'));
$group->role = group_user_access($group->id);

// logged in user can do stuff
if ($USER->is_logged_in()) {
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
        $group->invite = group_get_accept_form('invite', $group->id);
    }
    // When 'isolatedinstitutions' is set, people cannot join public groups by themselves
    else if ($group->jointype == 'open' && !is_isolated()) {
        $group->groupjoin = group_get_join_form('joingroup', $group->id);
    }
    else if ($group->request
             and $request = get_record('group_member_request', 'group', $group->id, 'member', $USER->get('id'))) {
        $group->membershiptype = 'request';
    }
}

// Check to see if we can invite anyone
if ($group->invitefriends) {
    $results = get_group_user_search_results($group->id, '', 0, 1, 'notinvited', null, $USER->get('id'), 'adminfirst',
                                             (((int) $group->hidemembers === GROUP_HIDE_TUTORS || (int) $group->hidemembersfrommembers === GROUP_HIDE_TUTORS) ? true : false)
    );
    if (empty($results['count'])) {
        $group->invitefriends = 0;
    }
}

$editwindow = group_format_editwindow($group);

$view = group_get_homepage_view($group->id);
$viewcontent = '';
if ($newlayout = $view->uses_new_layout()) {
    $layoutjs = array('js/gridstack/gridstack_modules/gridstack-h5.js', 'js/gridlayout.js');
    $blocks = $view->get_blocks();
    $blocks = json_encode($blocks);
    $blocksjs =   <<<EOF
    $(function () {
        var options = {
            margin: 1,
            cellHeight: 10,
            disableDrag : true,
            disableResize: true,
        };
        var grid = GridStack.init(options);
        // should add the blocks one by one
        var blocks = {$blocks};
        loadGrid(grid, blocks);
    });
EOF;
}
else {
    $viewcontent = $view->build_rows(); // Build content before initialising smarty in case pieform elements define headers.
    $layoutjs= array();
    $blocksjs = "$(function () {jQuery(document).trigger('blocksloaded');});";
}

$headers = array();
if ($group->public) {
    $feedlink = get_config('wwwroot') . 'interaction/forum/atom.php?type=g&id=' . $group->id;
    $headers[] = '<link rel="alternate" type="application/atom+xml" href="' . $feedlink . '">';
}

$javascript = array('paginator');
$javascript = array_merge($javascript, $layoutjs);
$blocktype_js = $view->get_all_blocktype_javascript();
$javascript = array_merge($javascript, $blocktype_js['jsfiles']);
$inlinejs = <<<JS
jQuery(function($) {
JS;
$inlinejs .= join("\n", $blocktype_js['initjs']) . "\n";
$inlinejs .= <<<JS
    // Disable the modal_links for images etc... when page loads
    $('a[class*=modal_link], a[class*=inner-link]').addClass('no-modal');
    $('a[class*=modal_link], a[class*=inner-link]').css('cursor', 'default');
});
JS;

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

$smarty->assign('INLINEJAVASCRIPT', $blocksjs . $inlinejs);
$smarty->assign('viewid', $view->get('id'));
$smarty->assign('newlayout', $newlayout);
if (!$newlayout) {
    $smarty->assign('viewcontent', $viewcontent);
}
$smarty->assign('group', $group);
$smarty->assign('editwindow', $editwindow);
$smarty->assign('cancopy', group_can_create_groups());
$smarty->assign('SUBPAGETOP', 'group/groupuseractions.tpl');
$smarty->assign('headingclass', 'page-header');
$smarty->assign('lastupdatedstr', $view->lastchanged_message());
$smarty->assign('visitstring', $view->visit_message());
$smarty->display('group/view.tpl');
