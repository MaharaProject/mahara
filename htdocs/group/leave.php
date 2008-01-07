<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Clare Lenihan <clare@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require('group.php');
$groupid = param_integer('id');
$returnto = param_alpha('returnto', 'mygroups');

$group = get_record('group', 'id', $groupid);
if (!$group) {
    throw new GroupNotFoundException(get_string('groupnotfound', 'mahara', $groupid));
}

define('TITLE', get_string('leavespecifiedgroup', 'mahara', $group->name));

$membership = user_can_access_group($group);

if (!($membership & GROUP_MEMBERSHIP_MEMBER)) {
    throw new AccessDeniedException(get_string('notamember'));
}

if (!group_user_can_leave($group)) {
    throw new AccessDeniedException(get_string('cantleavegroup'));
}

$views = count_records_sql(
    'SELECT COUNT(v.*)
    FROM {view} v
    INNER JOIN {view_access_group} a
    ON a.group = ?
    AND a.view = v.id
    WHERE v.owner = ?',
    array($groupid, $USER->get('id'))
);

$form = pieform(array(
    'name' => 'leavegroup',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'title' => $views ? get_string('groupconfirmleavehasviews') : get_string('groupconfirmleave'),
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . ($returnto == 'find' ? 'group/find.php' : 'group/mygroups.php')
        ),
        'returnto' => array(
            'type' => 'hidden',
            'value' => $returnto
        )
    ),
));

$smarty = smarty();
$smarty->assign('heading', TITLE);
$smarty->assign('form', $form);
$smarty->display('group/leave.tpl');

function leavegroup_submit(Pieform $form, $values) {
    global $USER, $SESSION, $groupid;
    group_remove_user($groupid, $USER->get('id'));
    $SESSION->add_ok_msg(get_string('leftgroup'));
    redirect($values['returnto'] == 'find' ? '/group/find.php' : '/group/mygroups.php');
}
?>
