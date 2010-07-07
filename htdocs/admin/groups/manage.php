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
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'groupsearch');
define('MENUITEM', 'managegroups/groups');

require_once('pieforms/pieform.php');

if (!$USER->get('admin')) {
    //User not an admin, redirect away
    redirect(get_config('wwwroot'));
}

$id = param_integer('id');
$exists = record_exists('group', 'id', $id);

if (!$exists) {
    //Group doesn't exist
    redirect(get_config('wwwroot').'admin/groups/group.php');
}

$group = get_record_sql("SELECT g.name FROM {group} g WHERE g.id = ?", array($id));
define('TITLE', "Manage group '$group->name'");

$admins = get_records_sql_array(
    "SELECT gm.member FROM {group_member} gm WHERE gm.role = 'admin' AND gm.group = ?", array($id)
);
foreach ($admins as $admin) {
    $group_admins[] = $admin->member;
}

$subscribeform = array(
    'name'       => 'groupadminsform',
    'renderer'   => 'table',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'jssuccesscallback' => 'checkReload',
    'elements'   => array(
        'admins' => array(
            'type' => 'userlist',
            'title' => 'Group Admins',
            'description' => 'Manage the admins for this group',
            'defaultvalue' => $group_admins,
            'group' => $id,
            'includeadmins' => true,
            'filter' => false,
            'lefttitle' => 'Members',
            'righttitle' => 'Admins',
        ),
        'group'  => array(
            'type' => 'hidden',
            'value' => $id,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => 'Save',
        ),
    ),
);
$subscribeform = pieform($subscribeform);

function groupadminsform_submit(Pieform $form, $values) {
    global $SESSION;
    $gid = $values['group'];
    $old_admins = array();
    $results = get_records_sql_array(
        "SELECT gm.member FROM {group_member} gm WHERE gm.role = 'admin' AND gm.group = ?", array($gid)
    );
    foreach ($results as $row) {
        $old_admins[] = $row->member;
    }

    db_begin;
    if (!empty($values['admins'])) {
        foreach ($values['admins'] as $uid) {
            if (!in_array($uid, $old_admins)) {
                //An admin has been added
                set_field('group_member', 'role', 'admin', 'group', $gid, 'member', $uid);
            }
        }
    }
    foreach ($old_admins as $uid) {
        if (!in_array($uid, $values['admins'])) {
            //An admin has been removed
            set_field('group_member', 'role', 'member', 'group', $gid, 'member', $uid);
        }
    }
    db_commit();

    $SESSION->add_ok_msg("Group admins have been updated");
    redirect(get_config('wwwroot').'admin/groups/groups.php');
}

$smarty = smarty();
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('managegroupform', $subscribeform);
$smarty->display('admin/groups/manage.tpl');

?>
