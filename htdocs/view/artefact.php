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
define('SECTION_PAGE', 'artefact');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');

$artefactid = param_integer('artefact');
$viewid     = param_integer('view');
$path       = param_variable('path', null);

$view = new View($viewid);
if (!can_view_view($viewid)) {
    throw new AccessDeniedException();
}

if (!artefact_in_view($artefactid, $viewid)) {
    throw new AccessDeniedException(get_string('artefactnotinview', 'error', $artefactid, $viewid));
}

// Feedback list pagination requires limit/offset params
$limit    = param_integer('limit', 10);
$offset   = param_integer('offset', 0);

require_once(get_config('docroot') . 'artefact/lib.php');
$artefact = artefact_instance_from_id($artefactid);

// Create the "make feedback private form" now if it's been submitted
if (param_variable('make_private_submit', null)) {
    pieform(make_private_form(param_integer('feedback')));
}

if (!$artefact->in_view_list()) {
    throw new AccessDeniedException(get_string('artefactsonlyviewableinview', 'error'));
}

define('TITLE', $artefact->display_title() . ' ' . get_string('in', 'view') . ' ' . $view->get('title'));

// Render the artefact
$options = array('viewid' => $viewid,
                 'path' => $path);
$rendered = $artefact->render_self($options);
$content = '';
if (!empty($rendered['javascript'])) {
    $content = '<script type="text/javascript">' . $rendered['javascript'] . '</script>';
}
$content .= $rendered['html'];

// Build the path to the artefact, through its parents
$artefactpath = array();
$parent = $artefact->get('parent');
while ($parent !== null) {
    // This loop could get expensive when there are a lot of parents. But at least 
    // it works, unlike the old attempt
    $parentobj = artefact_instance_from_id($parent);
    if (artefact_in_view($parent, $viewid)) {
        array_unshift($artefactpath, array(
            'url'   => get_config('wwwroot') . 'view/artefact.php?artefact=' . $parent . '&view=' . $viewid,
            'title' => $parentobj->display_title(),
        ));
    }

    $parent = $parentobj->get('parent');
}

$artefactpath[] = array(
    'url' => '',
    'title' => $artefact->display_title(),
);


// Feedback
$feedback = $artefact->get_feedback($limit, $offset, $viewid);
build_feedback_html($feedback);

$javascript = <<<EOF
var viewid = {$viewid};
addLoadEvent(function () {
    paginator = {$feedback->pagination_js}
});
EOF;

$feedbackform = pieform(add_feedback_form(false));
$objectionform = pieform(objection_form());

$viewbeingwatched = (int)record_exists('usr_watchlist_view', 'usr', $USER->get('id'), 'view', $viewid);

$headers = array('<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/views.css">',);

$hasfeed = false;
$feedlink = '';
// add a link to the ATOM feed in the header if the view is public
if($artefact->get('artefacttype') == 'blog' && $view->is_public()) {
    $hasfeed = true;
    $feedlink = get_config('wwwroot') . 'artefact/blog/atom.php?artefact=' .
        $artefactid . '&view=' . $viewid;
    $headers[] = '<link rel="alternate" type="application/atom+xml" href="' . $feedlink . '" />';
}

$smarty = smarty(
    array('paginator', 'feedbacklist'),
    $headers,
    array(),
    array(
        'stylesheets' => array('style/views.css'),
        'sidebars'    => false,
    )
);

$smarty->assign('artefact', $content);
$smarty->assign('artefactpath', $artefactpath);
$smarty->assign('INLINEJAVASCRIPT', $javascript);

$smarty->assign('viewid', $viewid);
$smarty->assign('viewtitle', $view->get('title'));
$smarty->assign('feedback', $feedback);

$smarty->assign('hasfeed', $hasfeed);
$smarty->assign('feedlink', $feedlink);

$viewowner = $view->get('owner');
if ($viewowner) {
    $smarty->assign('ownerlink', 'user/view.php?id=' . $viewowner);
}
else if ($view->get('group')) {
    $smarty->assign('ownerlink', 'group/view.php?id=' . $view->get('group'));
}

$smarty->assign('ownername', $view->formatted_owner());
$smarty->assign('addfeedbackform', $feedbackform);
$smarty->assign('objectionform', $objectionform);
$smarty->assign('anonfeedback', !$USER->is_logged_in() && $viewid == get_view_from_token(get_cookie('viewaccess:'.$viewid)));
$smarty->assign('viewbeingwatched', $viewbeingwatched);

$smarty->display('view/artefact.tpl');

?>
