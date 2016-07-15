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
$views = $collection->get('views');
if (empty($views)) {
    $errorstr = get_string('accessdeniednoviews', 'module.framework');
    throw new AccessDeniedException($errorstr);
}

// Get the first view from the collection
$firstview = $views['views'][0];
$view = new View($firstview->id);

if (!$collection->has_framework()) {
    // We can't show the matrix page so redirect them to the first page of the collection instead
    redirect($view->get_url());
}

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

$inlinejs = <<<EOF
jQuery(function($) {
    // Variable to adjust for the hiding/showing of columns
    var minstart = 1; // The index of the last column before first page column, indexes start at zero so 1 = two columns
    var curstart = 2; // The index of first page currently being displayed
    var range = 4; // The number of pages to display
    var curend = curstart + range; // The index of last page currently being displayed
    var maxend = $( "#tablematrix tr th" ).length; // The number of columns in the table

    function carousel_matrix() {
        $('#tablematrix td:not(.special), #tablematrix th').each(function() {
            var index = $(this).index();
            if ((index > minstart && index < curstart) || index > curend) {
                $(this).hide();
            }
            else {
                $(this).show();
            }
        });

        if (curstart <= (minstart + 1)) {
            $('#prev').hide();
        }
        else {
            $('#prev').show();
        }
        if (curend >= (maxend - 1)) {
            $('#next').hide();
        }
        else {
            $('#next').show();
        }
    }

    $('#prev, #next').on('click', function(e) {
        e.preventDefault();
        var action = $(this).attr('id');
        if (action == 'next') {
            curend = Math.min(curend + 1, maxend - 1);
            curstart = curend - range;
            carousel_matrix();
        }
        if (action == 'prev') {
            curstart = Math.max(curstart - 1, minstart + 1);
            curend = curstart + range;
            carousel_matrix();
        }
    });
    // Setup
    carousel_matrix();

    $('.code div').hover(
        function() {
            $(this).find('span').removeClass('hidden');
        },
        function() {
            $(this).find('span').addClass('hidden');
        }
    );
});
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
$smarty->assign('standardscount', $standards['count']);
$smarty->assign('framework', $collection->get('framework'));
$smarty->assign('views', $views['views']);
$smarty->assign('viewcount', $views['count']);
$smarty->assign('totalcompleted', array_sum($completed));

$smarty->display('module:framework:matrix.tpl');
