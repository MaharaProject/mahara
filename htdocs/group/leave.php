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
define('MENUITEM', 'groups');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('group.php');
$groupid = param_integer('id');
$returnto = param_alpha('returnto', 'mygroups');

define('GROUP', $groupid);
$group = group_current_group();

define('TITLE', $group->name);

if (!group_user_access($group->id)) {
    throw new AccessDeniedException(get_string('notamember', 'group'));
}

if (!group_user_can_leave($group)) {
    throw new AccessDeniedException(get_string('cantleavegroup', 'group'));
}

$goto = get_config('wwwroot') . 'group/' . $returnto . '.php' . ($returnto == 'view' ? ('?id=' . $groupid) : '');

$form = pieform(array(
    'name' => 'leavegroup',
    'renderer' => 'div',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => $goto
        ),
        'returnto' => array(
            'type' => 'hidden',
            'value' => $returnto
        )
    ),
));

$smarty = smarty();
$smarty->assign('subheading', get_string('leavespecifiedgroup', 'group', $group->name));
$smarty->assign('form', $form);
$smarty->assign('message', get_string('groupconfirmleave', 'group'));
$smarty->assign('group', $group);
$smarty->display('group/leave.tpl');

function leavegroup_submit(Pieform $form, $values) {
    global $USER, $SESSION, $groupid, $goto;
    group_remove_user($groupid, $USER->get('id'));
    $SESSION->add_ok_msg(get_string('leftgroup', 'group'));
    redirect($goto);
}
