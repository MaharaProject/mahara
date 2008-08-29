<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
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

$group_data = get_record_sql("SELECT g.name, g.description, g.grouptype, g.jointype
    FROM {group} g
    INNER JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ? AND gm.role = 'admin')
    WHERE g.id = ?
    AND g.deleted = 0", array($USER->get('id'), $id));

if (!$group_data) {
    $SESSION->add_error_msg(get_string('canteditdontown'));
    redirect('/group/mygroups.php');
}

$editgroup = pieform(array(
    'name'     => 'editgroup',
    'method'   => 'post',
    'plugintype' => 'core',
    'pluginname' => 'groups',
    'elements' => array(
        'name' => array(
            'type'         => 'text',
            'title'        => get_string('groupname', 'group'),
            'rules'        => array( 'required' => true, 'maxlength' => 128 ),
            'defaultvalue' => $group_data->name,
        ),
        'description' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('groupdescription', 'group'),
            'rows'         => 10,
            'cols'         => 55,
            'defaultvalue' => $group_data->description,
        ),
        'grouptype' => array(
            'type'         => 'select',
            'title'        => get_string('grouptype', 'group'),
            'options'      => group_get_grouptype_options(),
            'defaultvalue' => $group_data->grouptype . '.' . $group_data->jointype,
            'help'         => true,
        ),
        'id'          => array(
            'type'         => 'hidden',
            'value'        => $id,
        ),
        'submit'   => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('savegroup', 'group'), get_string('cancel')),
        ),
    ),
));

function editgroup_validate(Pieform $form, $values) {
    $cid = get_field('group', 'id', 'name', $values['name']);

    if ($cid && $cid != $values['id']) {
        $form->set_error('name', get_string('groupalreadyexists', 'group'));
    }
}

function editgroup_cancel_submit() {
    redirect('/group/mygroups.php');
}

function editgroup_submit(Pieform $form, $values) {
    global $USER;
    global $SESSION;

    db_begin();

    $now = db_format_timestamp(time());

    list($grouptype, $jointype) = explode('.', $values['grouptype']);

    update_record(
        'group',
        (object) array(
            'id'             => $values['id'],
            'name'           => $values['name'],
            'description'    => $values['description'],
            'grouptype'      => $grouptype,
            'jointype'       => $jointype,
            'mtime'          => $now,
        ),
        'id'
    );

    $SESSION->add_ok_msg(get_string('groupsaved', 'group'));

    db_commit();

    redirect('/group/mygroups.php');
}

$smarty = smarty();

$smarty->assign('editgroup', $editgroup);

$smarty->display('group/edit.tpl');

?>
