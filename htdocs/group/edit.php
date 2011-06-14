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

    list($grouptype, $jointype) = explode('.', $values['grouptype']);
    $values['public'] = (isset($values['public'])) ? $values['public'] : 0;
    $values['usersautoadded'] = (isset($values['usersautoadded'])) ? $values['usersautoadded'] : 0;

    $newvalues = (object) array(
        'id'             => $group_data->id,
        'name'           => $group_data->name == $values['name'] ? $values['name'] : trim($values['name']),
        'description'    => $values['description'],
        'grouptype'      => $grouptype,
        'category'       => empty($values['category']) ? null : intval($values['category']),
        'jointype'       => $jointype,
        'usersautoadded' => intval($values['usersautoadded']),
        'public'         => intval($values['public']),
        'viewnotify'     => intval($values['viewnotify']),
    );

    group_update($newvalues);

    $SESSION->add_ok_msg(get_string('groupsaved', 'group'));

    db_commit();

    redirect('/group/view.php?id=' . $values['id']);
}

$smarty = smarty();
$smarty->assign('editgroup', $editgroup);
$smarty->display('group/edit.tpl');
