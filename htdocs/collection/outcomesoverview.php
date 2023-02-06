<?php
/**
 * Provides support for Outcomes progress of Collections.
 *
 * Provides a summary page of Pages in the portfolio and their sign-off status
 * if the Page has a "Sign off" block on it.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('PUBLIC_ACCESS', 1);
define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'collection');
define('SECTION_PAGE', 'outcomeoverview');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('collection.php');
require_once(dirname(dirname(__FILE__)). '/group/outcomes.php');

$collectionid = param_integer('id');

$collection = new Collection($collectionid);

$headers[] = '<meta name="robots" content="noindex">';
$javascript = array(
    'js/collection-navigation.js',
    'js/jquery/jquery-mobile/jquery.mobile.custom.min.js');

if (!$collection || !$collection->get('outcomeportfolio') || !$collection->get('group')) {
  throw new AccessDeniedException();
}

// Get the first view from the collection
$firstview = $collection->first_view();
if (isset($firstview) && !can_view_view($firstview->get('id'))) {
    throw new AccessDeniedException();
}

$grouprole = group_user_access($collection->get('group'));
$outcomes = get_outcomes($collectionid);
if ($outcomes) {
  $outcometypes = get_outcome_types($collection);
  // create support forms
  $supportform  = [];
  foreach($outcomes as $outcome) {
    $supportform[$outcome->id] = pieform(
      array(
        'name' => 'support_' . $outcome->id,
        'class' => 'supportform',
        'checkdirtychange' => false,
        'elements' => array(
          'id' => array(
            'type' => 'hidden',
            'value'=> $outcome->id,
          ),
          'support' => array(
            'type' => 'switchbox',
            'title' => get_string('supporttitle', 'collection'),
            'value' => $outcome->support,
            'disabled' => $grouprole === 'member' || $outcome->complete,
          )
        )
      )
    );
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
$smarty->assign('PAGETITLE', get_string('outcomes', 'collection'));
$smarty->assign('maintitle', $collection->get('name'));
$smarty->assign('name', get_string('outcomes', 'collection'));

$views = $collection->get('views');
if ($views) {
    // Get the first view from the collection.
    $firstview = $views['views'][0];
    $view = new View($firstview->id);

    // if the view theme is set in view table as is usable
    if ($view->is_themeable() && $view->get('theme') && $THEME->basename != $view->get('theme')) {
        $THEME = new Theme($view);
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
    // Collection top navigation.
    if ($collection->get('navigation')) {
        $viewnav = $views['views'];
        if ($collection->get('framework')) {
            array_unshift($viewnav, $collection->collection_nav_framework_option());
        }
        if ($collection->has_progresscompletion()) {
            array_unshift($viewnav, $collection->collection_nav_progresscompletion_option());
        }
        if ($collection->has_outcomes()) {
            array_unshift($viewnav, $collection->collection_nav_outcomes_option());
        }
        $smarty->assign('collectionnav', $viewnav);
    }

    $submittedgroup = (int)$view->get('submittedgroup');
    $can_edit = $USER->can_edit_view($view) && !$submittedgroup && !$view->is_submitted() && $USER->can_edit_collection($view->get_collection());
    $smarty->assign('usercaneditview', $can_edit);
    $smarty->assign('userisowner', false);
    $smarty->assign('accessurl', get_config('wwwroot') . 'view/accessurl.php?id=' . $view->get('id') . ($collection ? '&collection=' . $collection->get('id') : '' ));
    $smarty->assign('views', $views['views']);
    $smarty->assign('viewlocked', $view->get('locked'));
    $smarty->assign('collectiontitle', $collection->get('name'));
    $smarty->assign('collectionid', $collection->get('id'));
    $smarty->assign('outcomeoverview', true);
}

if ($outcomes) {
    $smarty->assign('actionsallowed', ($grouprole === 'admin' || $grouprole === 'tutor') ? 1 : 0);
    $smarty->assign('showmanagebutton', $grouprole === 'admin');
    $smarty->assign('managaoutcomesurl', get_config('wwwroot') . 'collection/manageoutcomes.php?id=' . $collectionid);
    // Progress bar.
    $smarty->assign('quotamessage', get_string('outcomesoverallcompletion', 'collection'));
    list($completedactionspercentage, $totalactions) = $collection->get_outcomes_complete_percentage();
    $smarty->assign('completedactionspercentage', $completedactionspercentage);
    $smarty->assign('totalactions', $totalactions);

    $smarty->assign('group', $collection->get('group'));
    $smarty->assign('collection', $collectionid);
    $smarty->assign('outcomes', $outcomes);
    $smarty->assign('outcometypes', $outcometypes);

    $smarty->assign('supportform', $supportform);
    // Because the 'id' in the query is for collection we want to remove that and set id directly in template
    // to be either view or collection 'id' where needed
    $querystring = get_querystring();
    $querystring = preg_replace('/\bid=\d+\b/', '', $querystring);
    $smarty->assign('querystring', $querystring);

    $activities = get_outcome_activity_views($collection->get('id'));
    $smarty->assign('activities', $activities);
    $can_edit_layout = View::check_can_edit_activity_page_info($collection->get('group'), true);
    $smarty->assign('usercaneditview', $can_edit_layout);
}

$smarty->assign('url', get_config('wwwroot') . 'view/groupviews.php?group=' . $collection->get('group'));
$smarty->assign('linktext', get_string('returntogroupportfolios1', 'group'));

$smarty->display('collection/outcomesoverview.tpl');
