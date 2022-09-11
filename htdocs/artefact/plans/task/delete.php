<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
safe_require('artefact','plans');

$id = param_integer('id');
$todelete = new ArtefactTypeTask($id);

if (!$USER->can_edit_artefact($todelete)) {
    throw new AccessDeniedException();
}

if (param_exists('group')) {
    define('GROUP', param_integer('group'));
    define('MENUITEM_SUBPAGE', 'groupplans');
    $group = group_current_group();
    $menuItem = 'engage/index';
    $title = $group->name . ' - ' . get_string('groupplans', 'artefact.plans');
    $pageheading = $group->name;
    $returnurl = get_config('wwwroot') . 'artefact/plans/plan/view.php?group=' . $group->id . '&id='.$todelete->get('parent');
    $redirect = '/artefact/plans/plan/view.php?group=' . $group->id . '&id='.$todelete->get('parent');
}
else {
    $menuItem = 'create/plans';
    $title = get_string('deletetask','artefact.plans');
    $pageheading = $todelete->get('title');
    $returnurl = get_config('wwwroot') . 'artefact/plans/plan/view.php?id='.$todelete->get('parent');
    $redirect = '/artefact/plans/plan/view.php?id='.$todelete->get('parent');
}

define('MENUITEM', $menuItem);
define('TITLE', $title);

$viewid = (isset($_GET['view']) ? $_GET['view'] : null);
if ($viewid) {
    require_once('view.php');
    $view = new View($viewid);
}
else {
    $view = null;
}

if ($view && $USER->can_edit_view($view)) {
    $returnurl = get_config('wwwroot') . 'view/blocks.php?id=' . $view->get('id');
    $redirect = get_config('wwwroot') . 'view/blocks.php?id=' . $view->get('id');
}

$deleteform = [
    'name' => 'deletetaskform',
    'class' => 'form-delete',
    'plugintype' => 'artefact',
    'pluginname' => 'plans',
    'renderer' => 'div',
    'elements' => [
        'submit' => [
            'type' => 'submitcancel',
            'subclass' => array('btn-secondary'),
            'value' => [get_string('deletetask','artefact.plans'), get_string('cancel')],
            'goto' => $returnurl,
        ],
    ]
];
$form = pieform($deleteform);

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', $pageheading);
$smarty->assign('subheading', get_string('deletethistask','artefact.plans',$todelete->get('title')));
$smarty->assign('message', get_string('deletetaskconfirm','artefact.plans'));
$smarty->display('artefact:plans:task/delete.tpl');

// calls this function first so that we can get the artefact and call delete on it
function deletetaskform_submit(Pieform $form, $values) {
    global $SESSION, $todelete, $redirect;

    $todelete->delete();
    $SESSION->add_ok_msg(get_string('taskdeletedsuccessfully', 'artefact.plans'));

    redirect($redirect);
}
