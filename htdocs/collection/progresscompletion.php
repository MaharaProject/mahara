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

define('PUBLIC', 1);
define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'collection');
define('SECTION_PAGE', 'progress');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('collection.php');
require_once(get_config('libroot') . 'objectionable.php');
require_once(get_config('libroot'). 'revokemyaccess.php');

$collectionid = param_integer('id');

$collection = new Collection($collectionid);

$javascript = array(
    'js/collection-navigation.js',
    'js/jquery/jquery-mobile/jquery.mobile.custom.min.js',
    'tinymce',
    'viewmenu',
    'js/jquery/jquery-ui/js/jquery-ui.min.js',
    'js/lodash/lodash.js',
    'js/gridstack/gridstack.js',
    'js/gridlayout.js');

$views = $collection->get('views');

// Get the first view from the collection
$firstview = $views['views'][0];
$view = new View($firstview->id);

if (!can_view_view($view->get('id'))) {
    throw new AccessDeniedException();
}
if (!$pid = $collection->has_progresscompletion()) {
    throw new AccessDeniedException();
}
else {
    $pview = new View($pid);
    $blocks = $pview->get_blocks();
    $blocks = json_encode($blocks);
    $blocksjs = <<<EOF
$(function () {
    var options = {
        verticalMargin: 5,
        cellHeight: 10,
        disableDrag : true,
        disableResize: true,
    };
    var grid = $('.grid-stack');
    grid.gridstack(options);
    grid = $('.grid-stack').data('gridstack');
    // should add the blocks one by one
    var blocks = {$blocks};
    loadGrid(grid, blocks);
});
EOF;
}

// Set up theme
$viewtheme = $view->get('theme');
if ($viewtheme && $THEME->basename != $viewtheme) {
    $THEME = new Theme($viewtheme);
}
$headers[] = '<meta name="robots" content="noindex">';

$objectionform = false;
$revokeaccessform = false;
if ($USER->is_logged_in()) {
    if (record_exists('view_access', 'view', $pview->get('id'), 'usr', $USER->get('id'))) {
        $revokeaccessform = pieform(revokemyaccess_form($pview->get('id')));
    }
    $objectionform = pieform(objection_form());
    $reviewform = pieform(review_form($pview->get('id')));
    if ($notrudeform = notrude_form()) {
        $notrudeform = pieform($notrudeform);
    }
    // For admin to review objection claim, add comment
    // about objectionable content and possibly remove access
    if ($stillrudeform = stillrude_form()) {
        $stillrudeform = pieform($stillrudeform);
    }
}

$smarty = smarty(
    $javascript,
    $headers,
    array('View' => 'view',
        'Collection' => 'collection'
    ),
    array(
        'sidebars' => false,
        'pagehelp' => true,
    )
);

$smarty->assign('PAGETITLE', get_string('portfoliocompletion', 'collection'));
$smarty->assign('maintitle', $collection->get('name'));
$smarty->assign('name', get_string('portfoliocompletion', 'collection'));
$smarty->assign('INLINEJAVASCRIPT', $blocksjs);
if (isset($objectionform)) {
    $smarty->assign('objectionform', $objectionform);
    if ($USER->is_logged_in()) {
        $smarty->assign('notrudeform', $notrudeform);
        $smarty->assign('stillrudeform', $stillrudeform);
    }
    $smarty->assign('objectedpage', $pview->is_objectionable());
    $smarty->assign('objector', $pview->is_objectionable($USER->get('id')));
    $smarty->assign('objectionreplied', $pview->is_objectionable(null, true));
}
if (isset($revokeaccessform)) {
    $smarty->assign('revokeaccessform', $revokeaccessform);
}
if (isset($reviewform)) {
    $smarty->assign('reviewform', $reviewform);
}
if ($view->is_anonymous()) {
    $smarty->assign('author', get_string('anonymoususer'));
    if ($view->is_staff_or_admin_for_page()) {
        $smarty->assign('realauthor', $view->display_author());
    }
}
else {
    $smarty->assign('author', $view->display_author());
}

// collection top navigation
if ($collection) {
    $shownav = $collection->get('navigation');
    if ($shownav) {
        $viewnav = $views['views'];
        if ($collection->get('framework')) {
            array_unshift($viewnav, $collection->collection_nav_framework_option());
        }
        array_unshift($viewnav, $collection->collection_nav_progresscompletion_option());
        $smarty->assign('collection', $viewnav);
    }
    $smarty->assign('collectiontitle', $collection->get('name'));
}

$smarty->assign('progresscompletion', true);

// progress bar
$smarty->assign('quotamessage', get_string('overallcompletion', 'collection'));
list($completedactionspercentage, $totalactions) = $collection->get_signed_off_and_verified_percentage();
$smarty->assign('completedactionspercentage', $completedactionspercentage);
$smarty->assign('totalactions', $totalactions);


// table
$showVerification = false;
foreach ($views['views'] as &$view) {
    $viewobj = new View($view->id);
    $owneraction = $viewobj->get_progress_action('owner');
    $manageraction = $viewobj->get_progress_action('manager');

    $view->ownericonclass = $owneraction->get_icon();
    $view->owneraction = $owneraction->get_action();
    $view->ownertitle = $owneraction->get_title();
    $view->signedoff = ArtefactTypePeerassessment::is_signed_off($viewobj);

    $view->managericonclass = $manageraction->get_icon();
    $view->manageraction = $manageraction->get_action();
    $view->managertitle = $manageraction->get_title();
    $view->verified = ArtefactTypePeerassessment::is_verified($viewobj);
    if (ArtefactTypePeerassessment::is_verify_enabled($viewobj)) {
        $showVerification = true;
    }
    $view->description = $viewobj->get('description');
}

// TODO: Later on we will change which $view object will be set instead of taking the first view
$viewobj = new View($firstview->id); // Need to call this as $viewobj to avoid clash with $view in foreach loop above
$submittedgroup = (int)$viewobj->get('submittedgroup');
$can_edit = $USER->can_edit_view($viewobj) && !$submittedgroup && !$viewobj->is_submitted();
$urls = $viewobj->get_special_views_copy_urls();
if (array_key_exists('copyurl', $urls)) {
    $smarty->assign('copyurl', $urls['copyurl'] );
}
if (array_key_exists('downloadurl', $urls)) {
    $smarty->assign('downloadurl', $urls['downloadurl']);
}
$owner = $collection->get('owner');
$smarty->assign('usercaneditview', $can_edit);
$smarty->assign('userisowner', ($owner && $owner == $USER->get('id')));
$smarty->assign('accessurl', get_config('wwwroot') . 'view/accessurl.php?id=' . $viewobj->get('id') . (!empty($collection) ? '&collection=' . $collection->get('id') : '' ));
$smarty->assign('showVerification', $showVerification);
$smarty->assign('views', $views['views']);
$smarty->assign('viewlocked', $viewobj->get('locked'));
// Is progres page editable?
$pageistemplate = $pview->get_original_template();
if ($can_edit && !$collection->get('lock')) {
    if (($pview->get('owner') && !$pageistemplate) || !$pview->get('owner')) {
        $smarty->assign('editurl', get_config('wwwroot') . 'view/blocks.php?id=' . $collection->has_progresscompletion());
    }
}
$smarty->display('collection/progresscompletion.tpl');
