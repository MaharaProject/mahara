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

// This page should only be viewable if:
// 1). The collection has_framework() return true
// 2). The institution the collection owner belongs to has 'Smart Evidence' turned on.
// 3). The collection is able to be viewed by the user.

$collectionid = param_integer('id');
$collection = new Collection($collectionid);
$owner = $collection->get('owner');
$institution = $collection->get('institution');
if (!$collection->has_framework()) {
    $errorstr = get_string('accessdenied', 'error');
    throw new AccessDeniedException($errorstr);
}

if ($owner) {
    // Find what institution they belong to
    // Use first one if they belong to multiple
    $user = new User();
    $user->find_by_id($owner);
    $institutions = array_keys($user->get('institutions'));
    $institution = (!empty($institutions)) ? $institutions[0] : 'mahara';
}
$institution = new Institution($institution);
// Check that smart evidence is enabled for the institution
if (!$institution->allowinstitutionsmartevidence) {
    $errorstr = get_string('accessdeniedsmartevidencenotallowed', 'module.framework', $institution->displayname);
    throw new AccessDeniedException($errorstr);
}

$views = $collection->get('views');
// Get the first view from the collection
$firstview = $views['views'][0];
$view = new View($firstview->id);
if (!can_view_view($view->get('id'))) {
    $errorstr = get_string('accessdenied', 'error');
    throw new AccessDeniedException($errorstr);
}

$framework = new Framework($collection->get('framework'));
$standards = $framework->standards();

define('TITLE', $collection->get('name'));

$javascript = array('js/collection-navigation.js');

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

$evidence = $framework->get_evidence();
if (!$evidence) {
    $evidence = array();
}
$evidencematrix = $completed = array();
foreach ($evidence as $e) {
    $evidencematrix[$e->framework][$e->element][$e->view] = Framework::get_state_array($e->state);
    if (!isset($completed[$e->element])) {
        $completed[$e->element] = 0;
    }
    if ((int) $e->state === Framework::EVIDENCE_COMPLETED) {
        $completed[$e->element] ++;
    }
}

// $smarty->assign('INLINEJAVASCRIPT', $javascript . $inlinejs);
$smarty->assign('maintitle', $collection->get('name'));
$smarty->assign('owner', $owner);
$smarty->assign('PAGEHEADING', null);
$smarty->assign('name', $framework->get('name'));
$smarty->assign('description', $framework->get('description'));
$smarty->assign('standards', $standards['standards']);
$smarty->assign('evidence', $evidencematrix);
$smarty->assign('completed', $completed);
$smarty->assign('standardscount', $standards['count']);
$smarty->assign('framework', $collection->get('framework'));
$smarty->assign('views', $views['views']);
$smarty->assign('viewcount', $views['count']);
$smarty->assign('totalcompleted', array_sum($completed));

$smarty->display('module:framework:matrix.tpl');
