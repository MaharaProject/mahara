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

$collectionid = param_integer('id');

$collection = new Collection($collectionid);

$javascript = array('js/collection-navigation.js',
'js/jquery/jquery-mobile/jquery.mobile.custom.min.js',
'tinymce',
'js/jquery/jquery-ui/js/jquery-ui.min.js');

$views = $collection->get('views');

// Get the first view from the collection
$firstview = $views['views'][0];
$view = new View($firstview->id);

if (!can_view_view($view->get('id'))) {
    throw new AccessDeniedException();
}
if (!$collection->has_progresscompletion()) {
    throw new AccessDeniedException();
}

// Set up theme
$viewtheme = $view->get('theme');
if ($viewtheme && $THEME->basename != $viewtheme) {
    $THEME = new Theme($viewtheme);
}
$headers[] = '<meta name="robots" content="noindex">';

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
}

$smarty->assign('views', $views['views']);

$smarty->display('collection/progresscompletion.tpl');
