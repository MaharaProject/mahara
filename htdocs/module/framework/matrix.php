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
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'framework');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');
require_once(get_config('libroot') . 'institution.php');
safe_require('module', 'framework');
safe_require('artefact', 'comment');

pieform_setup_headdata();

// This page should only be viewable if:
// 1). The collection has_framework() return true
// 2). The institution the collection owner belongs to has 'Smart Evidence' turned on.
// 3). The collection is able to be viewed by the user.

$collectionid = param_integer('id');
$collection = new Collection($collectionid);
if (!$collection->has_framework()) {
    if ($collection->get('framework') > 0) {
        // The collection does have a framework associated but we are not allowed
        // to see the matrix page so show an error page with link to first page of collection.
        $smarty = smarty();
        $smarty->assign('maintitle', $collection->get('name'));
        $smarty->assign('owner', $collection->get('owner'));
        $smarty->assign('PAGEHEADING', null);
        $smarty->assign('name', get_string('frameworkmissing', 'module.framework'));
        $smarty->assign('error', get_string('accessdeniednoframework', 'module.framework'));
        if ($collection->get('navigation')) {
            $views = $collection->get('views');
            $smarty->assign('firstviewlink', get_string('firstviewlink', 'module.framework', $views['views'][0]->fullurl));
        }
        $smarty->display('module:framework:noviewmatrix.tpl');
        exit;
    }
    // No framework involved.
    throw new AccessDeniedException(get_string('accessdeniednoframework', 'module.framework'));
}
$owner = $collection->get('owner');
$views = $collection->get('views');

// Get the first view from the collection
$firstview = $views['views'][0];
$view = new View($firstview->id);

if (!can_view_view($view->get('id'))) {
    $errorstr = get_string('accessdenied', 'error');
    throw new AccessDeniedException($errorstr);
}
$frameworkid = $collection->get('framework');
$framework = new Framework($frameworkid);
$standards = $framework->standards();

define('TITLE', $collection->get('name'));

$javascript = array('js/collection-navigation.js', 'tinymce', 'module/framework/js/matrix.js');

// Set up theme
$viewtheme = $view->get('theme');
if ($viewtheme && $THEME->basename != $viewtheme) {
    $THEME = new Theme($viewtheme);
}

$headers = array();
$headers[] = '<link rel="stylesheet" type="text/css" href="' . append_version_number(get_config('wwwroot') . 'js/jquery/jquery-ui/css/smoothness/jquery-ui.min.css') . '">';

// Set up skin, if the page has one
$viewskin = $view->get('skin');
$issiteview = $view->get('institution') == 'mahara';
if ($viewskin && get_config('skins') && can_use_skins($owner, false, $issiteview) && (!isset($THEME->skins) || $THEME->skins !== false)) {
    $skin = array('skinid' => $viewskin, 'viewid' => $view->get('id'));
}
else {
    $skin = false;
}

$headers[] = '<meta name="robots" content="noindex">';  // Tell search engines not to index this page

$smarty = smarty(
    $javascript,
    $headers,
    array('View' => 'view',
          'Collection' => 'collection'),
    array(
        'sidebars' => false,
        'skin' => $skin
    )
);

// collection top navigation
if ($collection) {
    $shownav = $collection->get('navigation');
    if ($shownav) {
        $viewnav = $views['views'];
        array_unshift($viewnav, $collection->collection_nav_framework_option());
        $smarty->assign('collection', $viewnav);
    }
}

$evidence = $framework->get_evidence($collection->get('id'));
if (!$evidence) {
    $evidence = array();
}
$evidencematrix = $completed = array();
foreach ($evidence as $e) {
    $state = Framework::get_state_array($e->state, true);
    $choices = Framework::get_evidence_statuses($e->framework);
    $state['title'] = $choices[$e->state];
    $evidencematrix[$e->framework][$e->element][$e->view] = $state;
    if (!isset($completed[$e->element])) {
        $completed[$e->element] = 0;
    }
    if ((int) $e->state === Framework::EVIDENCE_COMPLETED) {
        $completed[$e->element] ++;
    }
}

$inlinejs = <<<EOF
    // Variable to adjust for the hiding/showing of columns
    var frameworkid = $frameworkid; // The id of the framework via php

EOF;

$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('maintitle', $collection->get('name'));
$smarty->assign('owner', $owner);
$smarty->assign('PAGEHEADING', null);
$smarty->assign('name', $framework->get('name'));
$smarty->assign('description', $framework->get('description'));
$smarty->assign('standards', $standards['standards']);
$smarty->assign('evidence', $evidencematrix);
$smarty->assign('completed', $completed);
$smarty->assign('canaddannotation', Framework::can_annotate_view($view->get('id')));
$smarty->assign('standardscount', $standards['count']);
$smarty->assign('framework', $collection->get('framework'));
$smarty->assign('views', $views['views']);
$smarty->assign('viewcount', $views['count']);
if ($view->is_anonymous()) {
    $smarty->assign('PAGEAUTHOR', get_string('anonymoususer'));
    $smarty->assign('author', get_string('anonymoususer'));
    if ($view->is_staff_or_admin_for_page()) {
        $smarty->assign('realauthor', $view->display_author());
    }
    $smarty->assign('anonymous', TRUE);
}
else {
    $smarty->assign('PAGEAUTHOR', $view->formatted_owner());
    $smarty->assign('author', $view->display_author());
    $smarty->assign('anonymous', FALSE);
}

$smarty->display('module:framework:matrix.tpl');
