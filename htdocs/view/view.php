<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'view');

require(dirname(dirname(__FILE__)) . '/init.php');
require(get_config('libroot') . 'view.php');
require('group.php');
safe_require('artefact', 'comment');

// access key for roaming teachers
$mnettoken = $SESSION->get('mnetuser') ? param_alphanum('mt', null) : null;

// access key for logged out users
$usertoken = (is_null($mnettoken) && get_config('allowpublicviews')) ? param_alphanum('t', null) : null;

if ($mnettoken) {
    if (!$viewid = get_view_from_token($mnettoken, false)) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
    if ($mnettoken != get_cookie('mviewaccess:'.$viewid)) {
        set_cookie('mviewaccess:'.$viewid, $mnettoken);
    }
}
else if ($usertoken) {
    if (!$viewid = get_view_from_token($usertoken, true)) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
    if ($usertoken != get_cookie('viewaccess:'.$viewid)) {
        set_cookie('viewaccess:'.$viewid, $usertoken);
    }
}
else {
    $viewid = param_integer('id');
}

$new = param_boolean('new');

if (!can_view_view($viewid, null, $usertoken, $mnettoken)) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

// Feedback list pagination requires limit/offset params
$limit    = param_integer('limit', 10);
$offset   = param_integer('offset', 0);

$view = new View($viewid);

// Create the "make feedback private form" now if it's been submitted
if (param_variable('make_private_submit', null)) {
    pieform(ArtefactTypeComment::make_private_form(param_integer('comment')));
}
else if (param_variable('delete_comment_submit', null)) {
    pieform(ArtefactTypeComment::delete_comment_form(param_integer('comment')));
}

$owner    = $view->get('owner');
$viewtype = $view->get('type');

if ($viewtype == 'profile') {
    define('TITLE', display_name($owner, null, true));
}
else {
    define('TITLE', $view->get('title'));
}
$title = hsc(TITLE);

$submittedgroup = (int)$view->get('submittedgroup');
if ($USER->is_logged_in() && $submittedgroup && group_user_can_assess_submitted_views($submittedgroup, $USER->get('id'))) {
    // The user is a tutor of the group that this view has
    // been submitted to, and is entitled to release the view
    $submittedgroup = get_record('group', 'id', $submittedgroup);
    $releaseform = pieform(array(
        'name'     => 'releaseview',
        'method'   => 'post',
        'plugintype' => 'core',
        'pluginname' => 'view',
        'autofocus' => false,
        'elements' => array(
            'submittedview' => array(
                'type'  => 'html',
                'value' => get_string('viewsubmittedtogroup', 'view', get_config('wwwroot') . 'group/view.php?id=' . $submittedgroup->id, $submittedgroup->name),
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('releaseview', 'group'),
            ),
        ),
    ));
}
else {
    $releaseform = '';
}


function releaseview_submit() {
    global $USER, $SESSION, $view;
    $groupid = $view->get('submittedgroup');
    $view->release($USER);
    $SESSION->add_ok_msg(get_string('viewreleasedsuccess', 'group'));
    if ($groupid) {
        // The tutor might not have access to the view any more; send
        // them back to the group page.
        redirect(get_config('wwwroot') . 'group/view.php?id='.$groupid);
    }
    redirect(get_config('wwwroot') . 'view/view.php?id='.$view->get('id'));
}
  
// If the view has comments turned off, tutors can still leave
// comments if the view is submitted to their group.

// @todo: site setting for public comments
if ($view->get('allowcomments') || !empty($releaseform)) {
    $defaultprivate = !empty($releaseform);
    $addfeedbackform = pieform(ArtefactTypeComment::add_comment_form($defaultprivate));
}
if ($USER->is_logged_in()) {
    $objectionform = pieform(objection_form());
}

$viewbeingwatched = (int)record_exists('usr_watchlist_view', 'usr', $USER->get('id'), 'view', $viewid);

$feedback = ArtefactTypeComment::get_comments($limit, $offset, false, $view);

// Set up theme
$viewtheme = $view->get('theme');
if ($viewtheme && $THEME->basename != $viewtheme) {
    $THEME = new Theme($viewtheme);
}
$stylesheets = array('<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/views.css">');

$can_edit = $USER->can_edit_view($view) && !$submittedgroup && !$view->is_submitted();

$smarty = smarty(
    array('paginator', 'viewmenu', 'artefact/resume/resumeshowhide.js'),
    $stylesheets,
    array(),
    array(
        'stylesheets' => array('style/views.css'),
        'sidebars' => false,
    )
);

$javascript = <<<EOF
var viewid = {$viewid};
addLoadEvent(function () {
    paginator = {$feedback->pagination_js}
});
EOF;

$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('new', $new);
$smarty->assign('viewid', $viewid);
$smarty->assign('viewtype', $viewtype);
$smarty->assign('feedback', $feedback);
$smarty->assign('owner', $owner);
$smarty->assign('tags', $view->get('tags'));

if (get_config('viewmicroheaders')) {
    $smarty->assign('microheaders', true);
    $smarty->assign('microheadertitle', $view->display_title(true, false));

    if ($can_edit) {
        if ($viewtype == 'profile') {
            $microheaderlinks = array(
                array(
                    'name' => get_string('editmyprofilepage'),
                    'url' => get_config('wwwroot') . 'view/blocks.php?profile=1',
                    'type' => 'edit',
                ),
                array(
                    'name' => get_string('editmyprofile', 'artefact.internal'),
                    'url' => get_config('wwwroot') . 'artefact/internal/index.php',
                    'type' => 'edit',
                ),
            );
        }
        else if ($new) {
            $microheaderlinks = array(
                array(
                    'name' => get_string('back'),
                    'url' => get_config('wwwroot') . 'view/blocks.php?id=' . $viewid . '&amp;new=1',
                    'type' => 'reply',
                ),
            );
        }
        else {
            $microheaderlinks = array(
                array(
                    'name' => get_string('edittitle', 'view'),
                    'url' => get_config('wwwroot') . 'view/edit.php?id=' . $viewid . '&amp;new=' . $new,
                    'type' => 'edit',
                ),
                array(
                    'name' => get_string('editcontent', 'view'),
                    'url' => get_config('wwwroot') . 'view/blocks.php?id=' . $viewid . '&amp;new=' . $new,
                    'type' => 'edit',
                ),
                array(
                    'name' => get_string('editaccess', 'view'),
                    'url' => get_config('wwwroot') . 'view/access.php?id=' . $viewid . '&amp;new=' . $new,
                    'type' => 'edit',
                ),
            );
        }
        $smarty->assign('microheaderlinks', $microheaderlinks);
    }

    if ($USER->is_logged_in()) {
        $smarty->assign('userdisplayname', display_name($USER, null, true));
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $page = get_config('wwwroot') . 'view/view.php?id=' . $viewid . ($new ? '&new=1' : '');
            if ($_SERVER['HTTP_REFERER'] != $page) {
                $smarty->assign('backurl', $_SERVER['HTTP_REFERER']);
            }
        }
    }
}

if ($viewtype != 'profile' && !get_config('viewmicroheaders')) {
    $title = $view->display_title();
}

if ($viewtype != 'profile' || !get_config('viewmicroheaders')) {
    $smarty->assign('maintitle', $title);
}

// Provide a link for roaming teachers to return
if ($mnetviewlist = $SESSION->get('mnetviewaccess')) {
    if (isset($mnetviewlist[$view->get('id')])) {
        $returnurl = $SESSION->get('mnetuserfrom');
        require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
        if ($peer = get_peer_from_instanceid($SESSION->get('authinstance'))) {
            $smarty->assign('mnethost', array(
                'name'      => $peer->name,
                'url'       => $returnurl ? $returnurl : $peer->wwwroot,
            ));
        }
    }
}

$smarty->assign('viewdescription', $view->get('description'));
$smarty->assign('viewcontent', $view->build_columns());
$smarty->assign('releaseform', $releaseform);
if (isset($addfeedbackform)) {
    $smarty->assign('enablecomments', 1);
    $smarty->assign('addfeedbackform', $addfeedbackform);
}
if (isset($objectionform)) {
    $smarty->assign('objectionform', $objectionform);
}
$smarty->assign('viewbeingwatched', $viewbeingwatched);

$smarty->display('view/view.tpl');

?>
