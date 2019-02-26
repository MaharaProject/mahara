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
require_once('collection.php');
require_once('view.php');

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
$message = $messagestatus = null;
if (!empty($direction)) {
    parse_str($direction, $direction_array);
    $viewids = array();
    // get all the id's of the existing views attached to collection - if any
    $firstviewaccess = array();
    if (!empty($views['views'])) {
        foreach ($views['views'] as $v) {
            $viewids[] = $v->view;
        }
        $firstview = new View($viewids[0]);
        $firstviewaccess = $firstview->get_access();
    }
    // now check if there are any new views to add to the collection
    // items dragged from the 'add to collection' list. (currently handles only one at a time)
    $diff = array_diff($direction_array['row'], $viewids);
    if (!empty($diff)) {
        // turn it into an array understood by $collection->add_views()
        $addviews = array();
        $newviewid = false;
        foreach ($diff as $v) {
            $newviewid = $v;
            // We need to check that the id's are allowed to be added to the collection
            // by checking if the user can edit the view.
            $view = new View($v);
            $viewowner = $view->get('owner');
            $viewgroup = $view->get('group');
            $viewaccess = $view->get_access();
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
            // New view permissions
            $collectiondifferent = false;
            $different = false;
            $differentarray = array();
            if (!empty($firstviewaccess) && empty($viewaccess)) {
                // adding the collection access rules to the added pages
                $different = true;
                $differentarray[] = $newviewid;
            }
            else if (!empty($firstviewaccess)) {
                $merged = combine_arrays($firstviewaccess, $viewaccess);
                if ($merged != $firstviewaccess) {
                    // adding the new access rules to both collection and added pages
                    $different = true;
                    $collectiondifferent = true;
                    $differentarray[] = $newviewid;
                }
                else if ($merged != $viewaccess) {
                    // adding the collection access rules to the added pages
                    $different = true;
                    $differentarray[] = $newviewid;
                }
            }
            else if (empty($firstviewaccess) && !empty($viewaccess)) {
                // adding the page's access rules to the collection pages
                $different = true;
                $collectiondifferent = true;
            }
            if ($collectiondifferent) {
                $differentarray = array_merge($differentarray, $viewids);
            }
            // Check if the collection has a secret url token for any of the existing views
            $hassecreturl = false;
            $views_all = array_merge($differentarray, $viewids);
            if (!empty($views_all)) {
                if (record_exists_select("view_access", "view IN (" . join(',', array_merge($differentarray, $viewids)) . ") AND (token IS NOT NULL AND token !='')")) {
                    $hassecreturl = true;
                }
            }

            if ($different && !empty($differentarray)) {
                $alertstr = get_string('viewsaddedaccesschanged', 'collection');
                foreach ($differentarray as $viewid) {
                    $changedview = new View($viewid);
                    $alertstr .= " " . json_encode($changedview->get('title')) . ",";
                }
                $alertstr = substr($alertstr, 0, -1) . '.';
                $alertstr .= ($hassecreturl) ? ' ' . get_string('viewaddedsecreturl', 'collection') : '';
                $message = get_string('viewsaddedtocollection1', 'collection', 1) . ' ' . $alertstr;
                $messagestatus = 'warning';
            }
            else {
                $alertstr = ($hassecreturl) ? get_string('viewaddedsecreturl', 'collection') : '';
                $message = get_string('viewsaddedtocollection1', 'collection', 1) . ' ' . $alertstr;
                $messagestatus = ($hassecreturl) ? 'warning' : 'ok';
            }
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
            'renderer' => 'div',
            'class' => 'form-as-button float-right',
            'name' => 'removeview_' . $v->view,
            'successcallback' => 'removeview_submit',
            'elements' => array(
                'view' => array(
                    'type' => 'hidden',
                    'value' => $v->view,
                ),
                'submit' => array(
                    'type' => 'button',
                    'usebuttontag' => true,
                    'class' => 'btn-link btn-sm',
                    'confirm' => get_string('viewconfirmremove', 'collection'),
                    'value' => '<span class="icon icon-times icon-lg text-danger" role="presentation" aria-hidden="true"><span class="sr-only">' . get_string('remove') . '</span></span>'                ),
            ),
        ));
    }
}


$smarty = smarty_core();
$smarty->assign('views', $views);
$smarty->assign('displayurl', get_config('wwwroot') . 'collection/views.php?id=' . $id);
$html = $smarty->fetch('collection/views.json.tpl');

json_reply(false, array(
    'message' => $message,
    'messagestatus' => $messagestatus,
    'html' => $html,
));
