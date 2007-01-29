<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage core
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'mycontacts');
define('SUBMENUITEM', 'mygroups');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('creategroup'));

$creategroup = pieform(array(
    'name'     => 'creategroup',
    'method'   => 'post',
    'elements' => array(
        'name' => array(
            'type'  => 'text',
            'title' => get_string('groupname'),
            'rules' => array( 'required' => true ),
        ),
        'description' => array(
            'type'  => 'wysiwyg',
            'title' => get_string('groupdescription'),
            'rows'  => 10,
            'cols'  => 70,
        ),
        'members'     => array(
            'type'   => 'userlist',
            'title'  => get_string('groupmembers'),
            'rules'  => array( 'required' => true ),
            'filter' => false,
        ),
        'submit'   => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('creategroup'), get_string('cancel')),
        ),
    ),
));

function creategroup_validate(Pieform $form, $values) {
    global $USER;

    $gid = get_field('usr_group', 'id', 'owner', $USER->get('id'), 'name', $values['name']);

    if($gid) {
        $form->set_error('name', get_string('groupalreadyexists'));
    }
}

function creategroup_cancel_submit() {
    redirect('/contacts/groups/');
}

function creategroup_submit(Pieform $form, $values) {
    global $USER;
    global $SESSION;

    db_begin();

    $now = db_format_timestamp(time());

    $gid = insert_record(
        'usr_group',
        (object) array(
            'name'        => $values['name'],
            'owner'       => $USER->get('id'),
            'description' => $values['description'],
            'ctime' => $now,
            'mtime' => $now,
        ),
        'id',
        true
    );

    foreach ($values['members'] as $member) {
        insert_record(
            'usr_group_member',
            (object) array(
                'grp'   => $gid,
                'member'=> $member,
                'ctime' => $now,
            )
        );
    }

    $SESSION->add_ok_msg(get_string('groupcreated'));

    db_commit();

    redirect('/contacts/groups/');
}

$smarty = smarty();

$smarty->assign('creategroup', $creategroup);

$smarty->display('contacts/groups/create.tpl');

?>
