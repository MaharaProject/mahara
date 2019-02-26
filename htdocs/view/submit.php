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
define('MENUITEM', 'create/views');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('view.php');
require_once('collection.php');
require_once('activity.php');
require_once(get_config('docroot') . 'artefact/lib.php');
$groupid = param_integer('group');
$returnto = param_variable('returnto', 'view');

$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype, g.urlid
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       WHERE u.member = ?
       AND g.id = ?
       AND g.submittableto = 1',
    array($USER->get('id'), $groupid)
);

if (!$group || !group_within_edit_window($groupid)) {
    throw new AccessDeniedException(get_string('cantsubmittogroup', 'view'));
}

if ($collectionid = param_integer('collection', null)) {
    $collection = new Collection($collectionid);
    if (!$collection || $collection->is_submitted() || ($collection->get('owner') !== $USER->get('id'))) {
        throw new AccessDeniedException(get_string('cantsubmitcollectiontogroup', 'view'));
    }
    $submissionname = $collection->get('name');
}
else {
    $view = new View(param_integer('id'));
    if (!$view || $view->is_submitted() || ($view->get('owner') !== $USER->get('id'))) {
        throw new AccessDeniedException(get_string('cantsubmitviewtogroup', 'view'));
    }
    $submissionname = $view->get('title');
}

define('TITLE', get_string('submitviewtogroup', 'view', $submissionname, $group->name));

$form = pieform(array(
    'name' => 'submitview',
    'renderer' => 'div',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'class' => 'btn-secondary',
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . returnto(),
        )
    ),
));

$smarty = smarty();
$smarty->assign('message', get_string('submitconfirm', 'view', $submissionname, $group->name));
$smarty->assign('form', $form);
$smarty->display('view/submit.tpl');

function submitview_submit(Pieform $form, $values) {
    global $SESSION, $USER, $view, $collection, $group;

    if (!empty($collection)) {
        $collection->submit($group, $USER);
        $SESSION->add_ok_msg(get_string('collectionsubmitted', 'view'));
    }
    else if (!empty($view)) {
        $view->submit($group, $USER);
        $SESSION->add_ok_msg(get_string('viewsubmitted', 'view'));
    }

    redirect('/' . returnto());
}

function returnto() {
    global $view, $collection, $group, $returnto;
    // Deteremine the best place to return to
    if ($returnto === 'group') {
        $goto = group_homepage_url($group, false);
    }
    else if ($returnto === 'view') {
        if (!empty($collection)) {
            $goto = $collection->get_url(false);
        }
        else {
            $goto = $view->get_url(false);
        }
    }
    else {
        $goto = 'view/index.php';
    }
    return $goto;
}
