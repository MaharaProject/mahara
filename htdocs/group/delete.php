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

$group = get_record('group', 'id', $groupid, 'owner', $USER->get('id'));

if (!$group) {
    throw new AccessDeniedException(get_string('cantdeletegroup', 'group'));
}

define('TITLE', get_string('deletespecifiedgroup', 'group', $group->name));

$views = count_records_sql(
    'SELECT COUNT(a.*)
    FROM {view_access_group} a
    WHERE a.group = ?',
    array($groupid)
);

$form = pieform(array(
    'name' => 'deletegroup',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'title' => $views ? get_string('groupconfirmdeletehasviews', 'group') : get_string('groupconfirmdelete', 'group'),
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'group/view.php?id=' . $groupid
        )
    ),
));

$smarty = smarty();
$smarty->assign('heading', TITLE);
$smarty->assign('form', $form);
$smarty->display('group/delete.tpl');

function deletegroup_submit(Pieform $form, $values) {
    global $SESSION, $USER, $groupid;
    db_begin();
    delete_records('view_access_group', 'group', $groupid);
    delete_records('group_member_invite', 'group', $groupid);
    delete_records('group_member_request', 'group', $groupid);
    delete_records('group_member', 'group', $groupid);
    delete_records('group', 'id', $groupid);
    db_commit();
    $SESSION->add_ok_msg(get_string('deletegroup', 'group'));
    redirect('/group/mygroups.php');
}
?>
