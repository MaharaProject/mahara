<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/views');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('view.php');
require_once('activity.php');
$viewid = param_integer('id');
$groupid = param_integer('group');
$returnto = param_variable('returnto', 'view');

$view = get_record('view', 'id', $viewid, 'owner', $USER->get('id'));
$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {grouptype} gt ON gt.name = g.grouptype
       WHERE u.member = ?
       AND g.id = ?
       AND gt.submittableto = 1',
    array($USER->get('id'), $groupid)
);

if (!$view || !$group || $view->submittedgroup || $view->submittedhost) {
    throw new AccessDeniedException(get_string('cantsubmitviewtogroup', 'view'));
}

define('TITLE', get_string('submitviewtogroup', 'view', $view->title, $group->name));

$form = pieform(array(
    'name' => 'submitview',
    'renderer' => 'div',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . returnto(),
        )
    ),
));

$smarty = smarty();
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('message', get_string('submitviewconfirm', 'view', $view->title, $group->name));
$smarty->assign('form', $form);
$smarty->display('view/submit.tpl');

function submitview_submit(Pieform $form, $values) {
    global $SESSION, $USER, $viewid, $groupid, $group;
    db_begin();
    update_record('view', array('submittedgroup' => $groupid, 'submittedtime' => db_format_timestamp(time())), array('id' => $viewid));
    activity_occurred('groupmessage', array(
        'subject'       => get_string('viewsubmitted', 'view'), // will be overwritten
        'message'       => get_string('viewsubmitted', 'view'), // will be overwritten
        'submittedview' => $viewid,
        'viewowner'     => $USER->get('id'),
        'group'         => $groupid,
        'roles'         => get_column('grouptype_roles', 'role', 'grouptype', $group->grouptype, 'see_submitted_views', 1),
    ));
    db_commit();
    $SESSION->add_ok_msg(get_string('viewsubmitted', 'view'));
    redirect('/' . returnto());
}

function returnto() {
    GLOBAL $viewid, $groupid, $returnto;
    // Deteremine the best place to return to
    if ($returnto === 'group') {
        $goto = 'group/view.php?id=' . $groupid;
    }
    else if ($returnto === 'view') {
        $goto = 'view/view.php?id=' . $viewid;
    }
    else {
        $goto = 'view/';
    }
    return $goto;
}
?>
