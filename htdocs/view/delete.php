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

$institution = $view->get('institution');
View::set_nav($groupid, $institution);

if ($groupid) {
    $goto = 'groupviews.php?group=' . $groupid;
}
else if ($institution) {
    $goto = 'institutionviews.php?institution=' . $institution;
}
else {
    $query = get_querystring();
    // remove the id
    $query = preg_replace('/id=([0-9]+)\&/','',$query);
    $goto = 'index.php?' . $query;
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
            'class' => 'btn-default',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'view/' . $goto,
        )
    ),
));

$smarty = smarty();
$smarty->assign('view', $view);
$smarty->assign('form', $form);
$smarty->assign('collectionnote', $collectionnote);
$smarty->display('view/delete.tpl');

function deleteview_submit(Pieform $form, $values) {
    global $SESSION, $USER, $viewid, $groupid, $institution, $goto;
    $view = new View($viewid, null);
    if (View::can_remove_viewtype($view->get('type')) || $USER->get('admin')) {
        $view->delete();
        $SESSION->add_ok_msg(get_string('viewdeleted', 'view'));
    }
    else {
        $SESSION->add_error_msg(get_string('cantdeleteview', 'view'));
    }
    if ($groupid) {
        redirect('/view/groupviews.php?group='.$groupid);
    }
    if ($institution) {
        redirect('/view/institutionviews.php?institution='.$institution);
    }
    redirect('/view/' . $goto);
}
