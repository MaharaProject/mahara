<?php
/**
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
// 2). The institution the collection owner belongs to has 'SmartEvidence' turned on.
// 3). The collection is able to be viewed by the user.

$collectionid = param_integer('id');
$collection = new Collection($collectionid);
if (!$collection->has_framework()) {
    if ($collection->get('framework') > 0) {
        // The collection does have a framework associated but we are not allowed
        // to see the matrix page so show an error page with link to first page of collection.
        $smarty = smarty();
        $smarty->assign('owner', $collection->get('owner'));
        $smarty->assign('PAGEHEADING', null);
        $smarty->assign('name', get_string('frameworkmissing', 'module.framework'));
        $smarty->assign('error', get_string('accessdeniednoframework', 'module.framework'));
        if ($collection->get('navigation')) {
            $views = $collection->get('views');
            if ($views) {
                $smarty->assign('firstviewlink', get_string('firstviewlink', 'module.framework', $views['views'][0]->fullurl));
            }
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
$view = $collection->first_view();

if (!can_view_view($view->get('id'))) {
    throw new AccessDeniedException();
}
$frameworkid = $collection->get('framework');
$framework = new Framework($frameworkid);
$standards = $framework->standards();
// Set the sections being open/closed based on session info
$settings = !empty($SESSION->get('matrixsettings')) ? $SESSION->get('matrixsettings') : array();
foreach ($standards['standards'] as $standard) {
    $settingstate = isset($settings[$collectionid][$standard->id]) ? $settings[$collectionid][$standard->id] : 'open';
    $standard->settingstate = $settingstate;
}

define('TITLE', $collection->get('name'));

$javascript = array('js/collection-navigation.js', 'js/jquery/jquery-mobile/jquery.mobile.custom.min.js', 'tinymce', 'module/framework/js/matrix.js', 'js/jquery/jquery-ui/js/jquery-ui.min.js');

// Get the first non-special page
$firstview = $views['views'][0];
$firstview = new View($firstview->id);
// if the view theme is set in view table as is usable
if ($firstview->is_themeable() && $firstview->get('theme') && $THEME->basename != $firstview->get('theme')) {
    $THEME = new Theme($firstview);
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
        'Collection' => 'collection'
    ),
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
        if ($collection->has_framework()) {
            array_unshift($viewnav, $collection->collection_nav_framework_option());
        }
        if ($collection->has_progresscompletion()) {
            array_unshift($viewnav, $collection->collection_nav_progresscompletion_option());
        }
        $smarty->assign('collection', $viewnav);
    }
    $smarty->assign('collectiontitle', $collection->get('name'));
}

$evidence = $framework->get_evidence($collection->get('id'));
if (!$evidence) {
    $evidence = array();
}

$evidencematrix = array();
$statuscounts = new stdClass();
$statustotals = array(
    'completed' => 0,
    'readyforassessment' => 0,
    'partiallycomplete' => 0,
    'dontmatch' => 0,
);
$enabled = new stdClass();

//completed should be always enabled
$statuscounts->completed = array();
$enabled->completed = true;

if ($framework->get_config('readyforassesment_field_enabled')) {
    $statuscounts->readyforassessment = array();
    $enabled->readyforassessment = true;
}
if ($framework->get_config('partiallycomplete_field_enabled')) {
    $statuscounts->partiallycomplete = array();
    $enabled->partiallycomplete = true;
}
if ($framework->get_config('dontmatch_field_enabled')) {
    $statuscounts->dontmatch = array();
    $enabled->dontmatch = true;
}

$statusestodisplay = get_statuses_to_display($frameworkid);

foreach ($evidence as $e) {
    $state = Framework::get_state_array($e->state, true);
    $choices = Framework::get_evidence_statuses($e->framework);
    $state['title'] = $choices[$e->state];
    $evidencematrix[$e->framework][$e->element][$e->view] = $state;

    switch ($e->state) {
        case Framework::EVIDENCE_COMPLETED:
            if (isset($statuscounts->completed)) {
                if (!isset($statuscounts->completed[$e->element])) {
                    $statuscounts->completed[$e->element] = 0;
                }
                $statuscounts->completed[$e->element] ++;
                $statustotals['completed']++;
            }
        break;
        case Framework::EVIDENCE_BEGUN:
            if (isset($statuscounts->readyforassessment)) {
                if (!isset($statuscounts->readyforassessment[$e->element])) {
                    $statuscounts->readyforassessment[$e->element] = 0;
                }
                $statuscounts->readyforassessment[$e->element] ++;
                $statustotals['readyforassessment']++;
            }
        break;
        case Framework::EVIDENCE_PARTIALCOMPLETE:
            if (isset($statuscounts->partiallycomplete)) {
                if (!isset($statuscounts->partiallycomplete[$e->element])) {
                    $statuscounts->partiallycomplete[$e->element] = 0;
                }
                $statuscounts->partiallycomplete[$e->element] ++;
                $statustotals['partiallycomplete']++;
            }
        break;
        case Framework::EVIDENCE_INCOMPLETE:
            if (isset($statuscounts->dontmatch)) {
                if (!isset($statuscounts->dontmatch[$e->element])) {
                    $statuscounts->dontmatch[$e->element] = 0;
                }
                $statuscounts->dontmatch[$e->element] ++;
                $statustotals['dontmatch']++;
            }
        break;
    }
}

$inlinejs = <<<EOF
    // Variable to adjust for the hiding/showing of columns
    var frameworkid = $frameworkid; // The id of the framework via php

EOF;

$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('maintitle', hsc($collection->get('name')));
$smarty->assign('collectionid', $collection->get('id'));
$smarty->assign('owner', $owner);
$smarty->assign('userisowner', ($owner && $owner == $USER->get('id')));
$smarty->assign('PAGEHEADING', null);
$smarty->assign('name', $framework->get('name'));
$smarty->assign('description', $framework->get('description'));
$smarty->assign('standards', $standards['standards']);
$smarty->assign('evidence', $evidencematrix);
$smarty->assign('statuscounts', $statuscounts);
$smarty->assign('statustotals', $statustotals);
$smarty->assign('statusestodisplay', $statusestodisplay);
$smarty->assign('enabled', $enabled);
$smarty->assign('colspan', count((array)$enabled) * 2);
$smarty->assign('canaddannotation', Framework::can_annotate_view($view->get('id')));
$smarty->assign('standardscount', $standards['count']);
$smarty->assign('framework', $collection->get('framework'));
$smarty->assign('views', $views['views']);
$smarty->assign('viewlocked', $view->get('locked'));
$smarty->assign('viewcount', $views['count']);
$smarty->assign('accessurl', get_config('wwwroot') . 'view/accessurl.php?id=' . $view->get('id') . (!$collection ? '&collection=' . $collection->get('id') : '' ));

$view = $firstview;
$submittedgroup = (int)$view->get('submittedgroup');
$can_edit = $USER->can_edit_view($view) && !$submittedgroup && !$view->is_submitted();
$urls = $view->get_special_views_copy_urls();
if (array_key_exists('copyurl', $urls)) {
    $smarty->assign('copyurl', $urls['copyurl'] );
}
if (array_key_exists('downloadurl', $urls)) {
    $smarty->assign('downloadurl', $urls['downloadurl']);
}
$smarty->assign('usercaneditview', $can_edit);
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
$returnto = $view->get_return_to_url_and_title();
$smarty->assign('url', $returnto['url']);
$smarty->assign('linktext', $returnto['title']);
$smarty->display('module:framework:matrix.tpl');

function get_statuses_to_display($frameworkid) {
    $statusestodisplay = new stdClass();
    $allstatuses = array(
        Framework::EVIDENCE_COMPLETED,
        Framework::EVIDENCE_BEGUN,
        Framework::EVIDENCE_PARTIALCOMPLETE,
        Framework::EVIDENCE_INCOMPLETE
    );
    $choices = Framework::get_evidence_statuses($frameworkid);
    foreach ($allstatuses as $item) {
        $state = Framework::get_state_array($item, true);
        switch ($item) {
            case Framework::EVIDENCE_COMPLETED:
            $statusestodisplay->completed = array(
                'classes' => $state['classes'],
                'title' => $choices[$item],
            );
            break;
            case Framework::EVIDENCE_BEGUN:
            $statusestodisplay->readyforassessment = array(
                'classes' => $state['classes'],
                'title' => $choices[$item],
            );
            break;
            case Framework::EVIDENCE_PARTIALCOMPLETE:
            $statusestodisplay->partiallycomplete = array(
                'classes' => $state['classes'],
                'title' => $choices[$item],
            );
            break;
            case Framework::EVIDENCE_INCOMPLETE:
            $statusestodisplay->dontmatch = array (
                'classes' => $state['classes'],
                'title' => $choices[$item],
            );
            break;
        }
    }
    return $statusestodisplay;
}
