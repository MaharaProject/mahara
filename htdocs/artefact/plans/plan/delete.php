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

if (!PluginArtefactPlans::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('Plans','artefact.plans')));
}

$id = param_integer('id');
$todelete = new ArtefactTypePlan($id);

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
    $subsectionheading = $todelete->get('title');
    $returnurl = get_config('wwwroot') . 'artefact/plans/index.php?group=' . $group->id;
    $redirect = '/artefact/plans/index.php?group=' . $group->id;
}
else {
    $menuItem = 'create/plans';
    $title = get_string('deleteplan','artefact.plans');
    $pageheading = $todelete->get('title');
    $subsectionheading = null;
    $returnurl = get_config('wwwroot') . 'artefact/plans/index.php';
    $redirect = '/artefact/plans/index.php';
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
    'name' => 'deleteplanform',
    'class' => 'form-delete',
    'plugintype' => 'artefact',
    'pluginname' => 'plan',
    'renderer' => 'div',
    'elements' => [
        'submit' => [
            'type' => 'submitcancel',
            'subclass' => array('btn-secondary'),
            'value' => [get_string('deleteplan','artefact.plans'), get_string('cancel')],
            'goto' => $returnurl,
        ],
    ]
];
$form = pieform($deleteform);

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', $pageheading);
$smarty->assign('SUBSECTIONHEADING', $subsectionheading);
$smarty->assign('subheading', get_string('deletethisplan','artefact.plans',$todelete->get('title')));
$smarty->assign('message', get_string('deleteplanconfirm','artefact.plans'));
$smarty->display('artefact:plans:plan/delete.tpl');

// calls this function first so that we can get the artefact and call delete on it
function deleteplanform_submit(Pieform $form, $values) {
    global $SESSION, $todelete, $redirect;

    $todelete->delete();
    $SESSION->add_ok_msg(get_string('plandeletedsuccessfully', 'artefact.plans'));

    redirect($redirect);
}
