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
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('view.php');
$viewid = param_integer('id');

$view = new View($viewid, null);

if (!$view || !$USER->can_edit_view($view) || $view->is_submitted()) {
    throw new AccessDeniedException(get_string('cantdeleteview', 'view'));
}
$groupid = $view->get('group');
if ($groupid && !group_within_edit_window($groupid)) {
    throw new AccessDeniedException(get_string('cantdeleteview', 'view'));
}

$collectionnote = '';
$collection = $view->get_collection();
if ($collection) {
    $collectionnote = get_string('deleteviewconfirmnote2', 'view', $collection->get_url(), $collection->get('name'));
}

// Check to see if the view is being used as a landing page url
$landingpagenote = '';
if (get_config('homepageredirect') && !empty(get_config('homepageredirecturl'))) {
    $landing = translate_landingpage_to_tags(array(get_config('homepageredirecturl')));
    foreach ($landing as $land) {
        if ($land->type == 'view' && $land->typeid == $viewid) {
            $landingpagenote = get_string('islandingpage', 'admin');
        }
    }
}

$institution = $view->get('institution');
View::set_nav($groupid, $institution);

//pressing the delete button from inside page view that belongs to a collection
if ($collection) {
    $views = $collection->get_viewids();
    if (($key = array_search($viewid, $views)) !== false) {
        unset($views[$key]);
    }
    if ($id = reset($views)) {
        if ($collection->has_framework()) {
            $goto = 'module/framework/matrix.php?id=' . $collection->get('id');
        }
        else {
            $goto = 'view/view.php?id=' . $id;
        }
    }
    else {
        $goto = 'view/index.php';
        if ($groupid) {
            $goto = 'view/groupviews.php?group=' . $groupid;
        }
        else if ($institution) {
            if ($institution == 'mahara') {
                $goto = 'admin/site/views.php';
            }
            else {
                $goto .= 'view/institutionviews.php?institution=' . $institution;
            }
        }
    }
}
else if ($groupid) {
    $goto = 'view/groupviews.php?group=' . $groupid;
}
else if ($institution) {
    if ($institution == 'mahara') {
        $goto = 'admin/site/views.php';
    }
    else {
        $goto = 'view/institutionviews.php?institution=' . $institution;
    }
}
else {
    $query = get_querystring();
    // remove the id
    $query = preg_replace('/id=([0-9]+)\&/','',$query);
    $goto = 'view/index.php?' . $query;
}

define('TITLE', get_string('deletespecifiedview', 'view', $view->get('title')));

$form = pieform(array(
    'name' => 'deleteview',
    'autofocus' => false,
    'method' => 'post',
    'renderer' => 'div',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'class' => 'btn-secondary',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . $goto,
        )
    ),
));

$smarty = smarty();
$smarty->assign('view', $view);
$smarty->assign('form', $form);
$smarty->assign('collectionnote', $collectionnote);
$smarty->assign('landingpagenote', $landingpagenote);
$smarty->display('view/delete.tpl');

function deleteview_submit(Pieform $form, $values) {
    global $SESSION, $USER, $viewid, $groupid, $institution;
    $submitelement = $form->get_element('submit');
    $view = new View($viewid, null);
    if (View::can_remove_viewtype($view->get('type')) || $USER->get('admin')) {
        $collectionid = $view->collection_id();
        $view->delete();
        if ($collection = new Collection($collectionid)) {
            $collection->update_display_order();
        }
        $SESSION->add_ok_msg(get_string('viewdeleted', 'view'));
    }
    else {
        $SESSION->add_error_msg(get_string('cantdeleteview', 'view'));
    }
    redirect( $submitelement['goto']);
}
