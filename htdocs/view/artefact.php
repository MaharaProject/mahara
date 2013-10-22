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
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'artefact');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
safe_require('artefact', 'comment');

$artefactid = param_integer('artefact');
$viewid     = param_integer('view');
$blockid    = param_integer('block',null);
$path       = param_variable('path', null);

$view = new View($viewid);
if (!can_view_view($view)) {
    throw new AccessDeniedException();
}

if (!artefact_in_view($artefactid, $viewid)) {
    throw new AccessDeniedException(get_string('artefactnotinview', 'error', $artefactid, $viewid));
}

// Feedback list pagination requires limit/offset params
$limit       = param_integer('limit', 10);
$offset      = param_integer('offset', 0);
$showcomment = param_integer('showcomment', null);

require_once(get_config('docroot') . 'artefact/lib.php');
$artefact = artefact_instance_from_id($artefactid);
if ($artefactid && $viewid && $blockid) {
    // use the block instance title rather than the artefact title if it exists
    $title = artefact_title_for_view_and_block($artefactid, $viewid, $blockid);
}
else {
    $title = $artefact->display_title();
}
if (!$artefact->in_view_list()) {
    throw new AccessDeniedException(get_string('artefactsonlyviewableinview', 'error'));
}

// Create the "make feedback private form" now if it's been submitted
if (param_variable('make_public_submit', null)) {
    pieform(ArtefactTypeComment::make_public_form(param_integer('comment')));
}
else if (param_variable('delete_comment_submit_x', null)) {
    pieform(ArtefactTypeComment::delete_comment_form(param_integer('comment')));
}

define('TITLE', $title . ' ' . get_string('in', 'view') . ' ' . $view->get('title'));

// Render the artefact
$options = array(
    'viewid' => $viewid,
    'path' => $path,
    'details' => true,
);
if (param_integer('details', 0)) {
    $options['metadata'] = 1;
}
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
    'title' => $title,
);


// Feedback
$feedback = ArtefactTypeComment::get_comments($limit, $offset, $showcomment, $view, $artefact);

$inlinejavascript = <<<EOF
var viewid = {$viewid};
addLoadEvent(function () {
    paginator = {$feedback->pagination_js}
});
EOF;

$javascript = array('paginator', 'viewmenu');
$extrastylesheets = array('style/views.css');

if ($artefact->get('allowcomments')) {
    $anonfeedback = !$USER->is_logged_in() && view_has_token($viewid, get_cookie('viewaccess:'.$viewid));
    $addfeedbackform = pieform(ArtefactTypeComment::add_comment_form(false, $artefact->get('approvecomments')));
    $extrastylesheets[] = 'style/jquery.rating.css';
    $javascript[] = 'jquery.rating';
}
$objectionform = pieform(objection_form());
if ($notrudeform = $view->notrude_form()) {
    $notrudeform = pieform($notrudeform);
}

$viewbeingwatched = (int)record_exists('usr_watchlist_view', 'usr', $USER->get('id'), 'view', $viewid);

$headers = array('<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/views.css?v=' . get_config('release'). '">',);

$hasfeed = false;
$feedlink = '';
// add a link to the ATOM feed in the header if the view is public
if($artefact->get('artefacttype') == 'blog' && $view->is_public()) {
    $hasfeed = true;
    $feedlink = get_config('wwwroot') . 'artefact/blog/atom.php?artefact=' .
        $artefactid . '&view=' . $viewid;
    $headers[] = '<link rel="alternate" type="application/atom+xml" href="' . $feedlink . '">';
}

$smarty = smarty(
    $javascript,
    $headers,
    array(),
    array(
        'stylesheets' => $extrastylesheets,
        'sidebars'    => false,
    )
);

$smarty->assign('artefact', $content);
$smarty->assign('artefactpath', $artefactpath);
$smarty->assign('INLINEJAVASCRIPT', $inlinejavascript);

if (get_config('viewmicroheaders')) {
    $smarty->assign('maharalogofilename', 'images/site-logo-small.png');
    $smarty->assign('microheaders', true);
    $smarty->assign('microheadertitle', $view->display_title(true, false));
}

$smarty->assign('view', $view);
$smarty->assign('viewid', $viewid);
$smarty->assign('feedback', $feedback);

$smarty->assign('hasfeed', $hasfeed);
$smarty->assign('feedlink', $feedlink);

if (isset($addfeedbackform)) {
    $smarty->assign('enablecomments', 1);
    $smarty->assign('anonfeedback', $anonfeedback);
    $smarty->assign('addfeedbackform', $addfeedbackform);
}
$smarty->assign('objectionform', $objectionform);
$smarty->assign('notrudeform', $notrudeform);
$smarty->assign('viewbeingwatched', $viewbeingwatched);

$smarty->display('view/artefact.tpl');
