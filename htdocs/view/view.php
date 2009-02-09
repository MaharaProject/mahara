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

$viewtoken = get_config('allowpublicviews') ? param_alphanum('t', null) : null;
if ($viewtoken) {
    if (!$viewid = get_view_from_token($viewtoken)) {
        throw new AccessDeniedException();
    }
    if ($viewtoken != get_cookie('viewaccess:'.$viewid)) {
        set_cookie('viewaccess:'.$viewid, $viewtoken);
    }
}
else {
    $viewid = param_integer('id');
}
$new = param_boolean('new');

if (!can_view_view($viewid, null, $viewtoken)) {
    throw new AccessDeniedException();
}
$view = new View($viewid);

$group = $view->get('group');

$title = $view->get('title');
define('TITLE', $title);

$submittedgroup = (int)$view->get('submittedto');
if ($submittedgroup && group_user_can_assess_submitted_views($submittedgroup, $USER->get('id'))) {
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
                'value' => get_string('viewsubmittedtogroup', 'view', get_config('wwwroot'), $submittedgroup->id, $submittedgroup->name),
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
    $view->release($view->get('submittedto'), $USER);
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

$smarty = smarty(
    array('mahara', 'tablerenderer', 'feedbacklist', 'artefact/resume/resumeshowhide.js'),
    array('<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/views.css">'),
    array(),
    array(
        'stylesheets' => array('style/views.css'),
    )
);

$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('new', $new);
$smarty->assign('viewid', $viewid);
$smarty->assign('viewtitle', $view->get('title'));

$owner = $view->get('owner');
if ($owner) {
    $smarty->assign('ownerlink', 'user/view.php?id=' . $owner);
    if ($USER->get('id') == $owner) {
        $smarty->assign('can_edit', !$view->get('submittedto'));
    }
}
else if ($group) {
    $smarty->assign('ownerlink', 'group/view.php?id=' . $group);
}

$smarty->assign('ownername', $view->formatted_owner());
$smarty->assign('streditviewbutton', ($new) ? get_string('backtocreatemyview', 'view') : get_string('editmyview', 'view'));
$smarty->assign('viewdescription', $view->get('description'));
$smarty->assign('viewcontent', $view->build_columns());
$smarty->assign('releaseform', $releaseform);
$smarty->assign('anonfeedback', !$USER->is_logged_in() && ($viewtoken || $viewid == get_view_from_token(get_cookie('viewaccess:'.$viewid))));
$smarty->assign('addfeedbackform', pieform(add_feedback_form($allowattachments)));
$smarty->assign('objectionform', pieform(objection_form()));
$smarty->assign('viewbeingwatched', $viewbeingwatched);

$smarty->display('view/view.tpl');

?>
