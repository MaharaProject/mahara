<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
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
    if ($usertoken != get_cookie('mviewaccess:'.$viewid)) {
        set_cookie('mviewaccess:'.$viewid, $usertoken);
    }
}
else {
    $viewid = param_integer('id');
}
$new = param_boolean('new');

if (!can_view_view($viewid, null, $usertoken, $mnettoken)) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}
$view = new View($viewid);

$group = $view->get('group');

$title = $view->get('title');
define('TITLE', $title);

$submittedgroup = (int)$view->get('submittedgroup');
if ($USER->is_logged_in() && $submittedgroup && group_user_can_assess_submitted_views($submittedgroup, $USER->get('id'))) {
    // The user is a tutor of the group that this view has
    // been submitted to, and is entitled to release the view, and to
    // upload an additional file when submitting feedback.
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
    $allowattachments = true;
}
else {
    $releaseform = '';
    $allowattachments = false;
}


function releaseview_submit() {
    global $USER, $SESSION, $view;
    $view->release($USER);
    $SESSION->add_ok_msg(get_string('viewreleasedsuccess', 'group'));
    redirect(get_config('wwwroot') . 'view/view.php?id='.$view->get('id'));
}
  
$viewbeingwatched = (int)record_exists('usr_watchlist_view', 'usr', $USER->get('id'), 'view', $viewid);

// Feedback 
$javascript = <<<EOF
feedbacklist.view = {$viewid};
feedbacklist.statevars.push('view');
feedbacklist.updateOnLoad();
EOF;


$anonfeedback = !$USER->is_logged_in() && ($usertoken || $viewid == get_view_from_token(get_cookie('viewaccess:'.$viewid)));
if ($USER->is_logged_in() || $anonfeedback) {
    $addfeedbackform = pieform(add_feedback_form($allowattachments));
}
if ($USER->is_logged_in()) {
    $objectionform = pieform(objection_form());
}

// Set up theme
list($basetheme, $viewtheme) = $view->get_theme();
if ($THEME->basename != $basetheme) {
    $THEME = new Theme($basetheme);
}
$stylesheets = array(
    // Basic structure CSS
    '<link rel="stylesheet" type="text/css" href="'
        . get_config('wwwroot') . 'theme/views.css">',
    // Extra CSS for the view theme
    '<link rel="stylesheet" type="text/css" href="'
        . get_config('wwwroot') . 'theme/' . $basetheme . '/viewthemes/' . $viewtheme . '/views.css">',
);

$smarty = smarty(
    array('mahara', 'tablerenderer', 'feedbacklist', 'artefact/resume/resumeshowhide.js'),
    $stylesheets,
    array(),
    array('sidebars' => false)
);

$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('new', $new);
$smarty->assign('viewid', $viewid);
$smarty->assign('viewtitle', $view->get('title'));

$owner = $view->get('owner');
if ($owner) {
    $smarty->assign('ownerlink', 'user/view.php?id=' . $owner);
    if ($USER->get('id') == $owner) {
        $smarty->assign('can_edit', !$submittedgroup && !$view->is_submitted());
    }
}
else if ($group) {
    $smarty->assign('ownerlink', 'group/view.php?id=' . $group);
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

$smarty->assign('ownername', $view->formatted_owner());
$smarty->assign('viewdescription', $view->get('description'));
$smarty->assign('viewcontent', $view->build_columns());
$smarty->assign('releaseform', $releaseform);
$smarty->assign('anonfeedback', $anonfeedback);
if (isset($addfeedbackform)) {
    $smarty->assign('addfeedbackform', $addfeedbackform);
}
if (isset($objectionform)) {
    $smarty->assign('objectionform', $objectionform);
}
$smarty->assign('viewbeingwatched', $viewbeingwatched);

$smarty->display('view/view.tpl');

?>
