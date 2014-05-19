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
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('collection.php');

$id = param_integer('id');
$direction = param_variable('direction','');

$collection = new Collection($id);

if (!$USER->can_edit_collection($collection)) {
    json_reply('local', get_string('accessdenied', 'error'));
}

$owner = $collection->get('owner');
$groupid = $collection->get('group');
$institutionname = $collection->get('institution');
$views = $collection->views();
if (!empty($direction)) {
    parse_str($direction, $direction_array);
    $viewids = array();
    // get all the id's of the existing views attached to collection - if any
    if (!empty($views['views'])) {
        foreach ($views['views'] as $v) {
            $viewids[] = $v->view;
        }
    }
    // now check if there are any new views to add to the collection
    // items dragged from the 'add to collection' list. (currently handles only one at a time)
    $diff = array_diff($direction_array['row'], $viewids);
    if (!empty($diff)) {
        // turn it into an array understood by $collection->add_views()
        $addviews = array();
        foreach ($diff as $v) {
            // We need to check that the id's are allowed to be added to the collection
            // by checking if the user can edit the view.
            require_once('view.php');
            $view = new View($v);
            $viewowner = $view->get('owner');
            $viewgroup = $view->get('group');
            $viewinstitution = $view->get('institution');
            if ((!$USER->can_edit_view($view)) ||
                (!empty($viewowner) && $viewowner != $collection->get('owner')) ||
                (!empty($viewgroup) && $viewgroup != $collection->get('group')) ||
                (!empty($viewinstitution) && $viewinstitution != $collection->get('institution'))
               ) {
                continue;
            }
            $addviews['view_' . $v] = true;
        }
        if (!empty($addviews)) {
            $collection->add_views($addviews);
        }
    }
    $collection->set_viewdisplayorder(null, $direction_array['row']);
}

// We need to call the collection again to get the updated view list
$collection = new Collection($id);
$views = $collection->get('views');

if ($views) {
    foreach ($views['views'] as &$v) {
        $v->remove = pieform(array(
            'name' => 'removeview_' . $v->view,
            'successcallback' => 'removeview_submit',
            'elements' => array(
                'view' => array(
                    'type' => 'hidden',
                    'value' => $v->view,
                ),
                'submit' => array(
                    'type' => 'submit',
                    'confirm' => get_string('viewconfirmremove', 'collection'),
                    'value' => get_string('remove'),
                ),
            ),
        ));
    }
}


$smarty = smarty_core();
$smarty->assign_by_ref('views', $views);
$smarty->assign('displayurl', get_config('wwwroot') . 'collection/views.php?id=' . $id);
$html = $smarty->fetch('collection/views.json.tpl');

json_reply(false, array(
    'message' => null,
    'html' => $html,
));
