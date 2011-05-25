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
define('MENUITEM', 'groups/groupsiown');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('group.php');
define('TITLE', get_string('editgroup', 'group'));

$id = param_integer('id');
define('GROUP', $id);

$group_data = get_record_sql("SELECT g.*
    FROM {group} g
    INNER JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ? AND gm.role = 'admin')
    WHERE g.id = ?
    AND g.deleted = 0", array($USER->get('id'), $id));

if (!$group_data) {
    $SESSION->add_error_msg(get_string('canteditdontown', 'group'));
    redirect('/group/mygroups.php');
}
$elements = array();
$elements['name'] = array(
            'type'         => 'text',
            'title'        => get_string('groupname', 'group'),
            'rules'        => array( 'required' => true, 'maxlength' => 128 ),
            'defaultvalue' => $group_data->name);
$elements['description'] = array(
            'type'         => 'wysiwyg',
            'title'        => get_string('groupdescription', 'group'),
            'rules'        => array('maxlength' => 65536),
            'rows'         => 10,
            'cols'         => 55,
            'defaultvalue' => $group_data->description);

$grouptypeoptions = group_get_grouptype_options($group_data->grouptype);
$currenttype = $group_data->grouptype . '.' . $group_data->jointype;
if (!isset($grouptypeoptions[$currenttype])) {
    // The user can't create groups of this type.  Probably a non-staff user
    // who's been promoted to admin of a controlled group.  Just don't let
    // them change it.
    $grouptypeoptions = array($currenttype => get_string('membershiptype.' . $group_data->jointype, 'group'));
}

$elements['grouptype'] = array(
    'type'         => 'select',
    'title'        => get_string('grouptype', 'group'),
    'options'      => $grouptypeoptions,
    'defaultvalue' => $currenttype,
    'help'         => true
);
if (get_config('allowgroupcategories')
    && $groupcategories = get_records_menu('group_category','','','displayorder', 'id,title')
) {
    $elements['category'] = array(
                'type'         => 'select',
                'title'        => get_string('groupcategory', 'group'),
                'options'      => array('0'=>get_string('nocategoryselected', 'group')) + $groupcategories,
                'defaultvalue' => $group_data->category);
}
$elements['public'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('publiclyviewablegroup', 'group'),
            'description'  => get_string('publiclyviewablegroupdescription', 'group'),
            'defaultvalue' => $group_data->public,
            'help'         => true,
            'ignore'       => !(get_config('createpublicgroups') == 'all' || get_config('createpublicgroups') == 'admins' && $USER->get('admin')));
$elements['usersautoadded'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('usersautoadded', 'group'),
            'description'  => get_string('usersautoaddeddescription', 'group'),
            'defaultvalue' => $group_data->usersautoadded,
            'help'         => true,
            'ignore'       => !$USER->get('admin'));
$elements['viewnotify'] = array(
    'type' => 'checkbox',
    'title' => get_string('viewnotify', 'group'),
    'description' => get_string('viewnotifydescription', 'group'),
    'defaultvalue' => $group_data->viewnotify
);
$elements['id'] = array(
            'type'         => 'hidden',
            'value'        => $id);
$elements['submit'] = array(
            'type'  => 'submitcancel',
            'value' => array(get_string('savegroup', 'group'), get_string('cancel')));

$editgroup = pieform(array(
    'name'     => 'editgroup',
    'method'   => 'post',
    'plugintype' => 'core',
    'pluginname' => 'groups',
    'elements' => $elements));

function editgroup_validate(Pieform $form, $values) {
    global $group_data;
    if ($group_data->name != $values['name']) {
        // This check has not always been case-insensitive; don't use get_record in case we get >1 row back.
        if ($ids = get_records_sql_array('SELECT id FROM {group} WHERE LOWER(TRIM(name)) = ?', array(strtolower(trim($values['name']))))) {
            if (count($ids) > 1 || $ids[0]->id != $group_data->id) {
                $form->set_error('name', get_string('groupalreadyexists', 'group'));
            }
        }
    }
}

function editgroup_cancel_submit() {
    redirect('/group/mygroups.php');
}

function editgroup_submit(Pieform $form, $values) {
    global $USER, $SESSION, $group_data;

    db_begin();

    $now = db_format_timestamp(time());

    list($grouptype, $jointype) = explode('.', $values['grouptype']);
    $values['public'] = (isset($values['public'])) ? $values['public'] : 0;
    $values['usersautoadded'] = (isset($values['usersautoadded'])) ? $values['usersautoadded'] : 0;

    update_record(
        'group',
        (object) array(
            'id'             => $values['id'],
            'name'           => $group_data->name == $values['name'] ? $values['name'] : trim($values['name']),
            'description'    => $values['description'],
            'grouptype'      => $grouptype,
            'category'       => empty($values['category']) ? null : intval($values['category']),
            'jointype'       => $jointype,
            'mtime'          => $now,
            'usersautoadded' => intval($values['usersautoadded']),
            'public'         => intval($values['public']),
            'viewnotify'     => intval($values['viewnotify']),
        ),
        'id'
    );

    // When jointype changes from invite/request to anything else,
    // remove all open invitations/requests, ---
    // Except for when jointype changes from request to open. Then
    // we can just add group membership for everyone with an open
    // request.

    if ($group_data->jointype == 'invite' && $jointype != 'invite') {
        delete_records('group_member_invite', 'group', $group_data->id);
    }
    else if ($group_data->jointype == 'request') {
        if ($jointype == 'open') {
            $userids = get_column_sql('
                SELECT u.id
                FROM {usr} u JOIN {group_member_request} r ON u.id = r.member
                WHERE r.group = ? AND u.deleted = 0',
                array($group_data->id)
            );
            if ($userids) {
                foreach ($userids as $uid) {
                    group_add_user($group_data->id, $uid);
                }
            }
        }
        else if ($jointype != 'request') {
            delete_records('group_member_request', 'group', $group_data->id);
        }
    }
    // When group type changes from course to standard, make sure that tutors
    // are demoted to members.
    if ($group_data->grouptype == 'course' && $grouptype != 'course') {
        set_field('group_member', 'role', 'member', 'group', $values['id'], 'role', 'tutor');
    }

    // When a group changes from public -> private or vice versa, set the
    // appropriate access permissions on the group homepage view.
    if ($group_data->public != $values['public']) {
        $homepageid = get_field('view', 'id', 'type', 'grouphomepage', 'group', $group_data->id);
        if ($group_data->public && !$values['public']) {
            delete_records('view_access', 'view', $homepageid, 'accesstype', 'public');
            insert_record('view_access', (object) array('view' => $homepageid, 'accesstype' => 'loggedin'));
        }
        else if (!$group_data->public && $values['public']) {
            delete_records('view_access', 'view', $homepageid, 'accesstype', 'loggedin');
            insert_record('view_access', (object) array('view' => $homepageid, 'accesstype' => 'public'));
        }
    }

    $SESSION->add_ok_msg(get_string('groupsaved', 'group'));

    db_commit();

    redirect('/group/view.php?id=' . $values['id']);
}

$smarty = smarty();
$smarty->assign('editgroup', $editgroup);
$smarty->display('group/edit.tpl');
