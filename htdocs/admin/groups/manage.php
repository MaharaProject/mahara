<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2010 Catalyst IT Ltd and others; see:
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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2010 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'managegroups/groups');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

require_once('pieforms/pieform.php');

$group = get_record_select('group', 'id = ? AND deleted = 0', array(param_integer('id')));

define('TITLE', get_string('administergroups', 'admin'));

$quotasform = pieform(array(
    'name'       => 'groupquotasform',
    'elements'   => array(
        'groupid' => array(
            'type' => 'hidden',
            'value' => $group->id,
        ),
        'quota'  => array(
            'type' => 'bytes',
            'title' => get_string('filequota1', 'admin'),
            'description' => get_string('groupfilequotadescription', 'admin'),
            'defaultvalue' => $group->quota,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('save'),
        ),
    ),
));

function groupquotasform_submit(Pieform $form, $values) {
    global $SESSION;

    $group = new StdClass;
    $group->id = $values['groupid'];
    $group->quota = $values['quota'];
    update_record('group', $group);

    $SESSION->add_ok_msg(get_string('groupquotaupdated', 'admin'));
    redirect(get_config('wwwroot').'admin/groups/groups.php');
}


$admins = get_column_sql(
    "SELECT gm.member FROM {group_member} gm WHERE gm.role = 'admin' AND gm.group = ?", array($group->id)
);

$groupadminsform = pieform(array(
    'name'       => 'groupadminsform',
    'renderer'   => 'table',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'elements'   => array(
        'admins' => array(
            'type' => 'userlist',
            'title' => get_string('groupadmins', 'group'),
            'defaultvalue' => $admins,
            'filter' => false,
            'lefttitle' => get_string('potentialadmins', 'admin'),
            'righttitle' => get_string('currentadmins', 'admin'),
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('save'),
        ),
    ),
));

function groupadminsform_submit(Pieform $form, $values) {
    global $SESSION, $group, $admins;

    $newadmins = array_diff($values['admins'], $admins);
    $demoted = array_diff($admins, $values['admins']);

    db_begin();
    if ($demoted) {
        $demoted = join(',', array_map('intval', $demoted));
        execute_sql("
            UPDATE {group_member}
            SET role = 'member'
            WHERE role = 'admin' AND \"group\" = ?
                AND member IN ($demoted)",
            array($group->id)
        );
    }
    $dbnow = db_format_timestamp(time());
    foreach ($newadmins as $id) {
        if (group_user_access($group->id, $id)) {
            group_change_role($group->id, $id, 'admin');
        }
        else {
            group_add_user($group->id, $id, 'admin');
        }
    }
    db_commit();

    $SESSION->add_ok_msg(get_string('groupadminsupdated', 'admin'));
    redirect(get_config('wwwroot').'admin/groups/groups.php');
}

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('quotasform', $quotasform);
$smarty->assign('groupname', $group->name);
$smarty->assign('managegroupform', $groupadminsform);
$smarty->display('admin/groups/manage.tpl');
