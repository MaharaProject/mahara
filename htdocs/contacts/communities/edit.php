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
define('SUBMENUITEM', 'myownedcommunities');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('editcommunity'));

$id = param_integer('id');
$prefix = get_config('dbprefix');

$community_data = get_record('community', 'id', $id, 'owner', $USER->get('id'));

if (!$community_data) {
    $SESSION->add_error_msg(get_string('canteditdontown'));
    redirect('/contacts/communities/owned.php');
}

$joinoptions = array(
    'invite'     => get_string('membershiptype.invite'),
    'request'    => get_string('membershiptype.request'),
    'open'       => get_string('membershiptype.open'),
);
if ($USER->get('admin') || $USER->get('staff')) {
    $joinoptions['controlled'] = get_string('membershiptype.controlled');
}

$editcommunity = pieform(array(
    'name'     => 'editcommunity',
    'method'   => 'post',
    'plugintype' => 'core',
    'pluginname' => 'communities',
    'elements' => array(
        'name' => array(
            'type'         => 'text',
            'title'        => get_string('communityname'),
            'rules'        => array( 'required' => true, 'maxlength' => 128 ),
            'defaultvalue' => $community_data->name,
        ),
        'description' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('communitydescription'),
            'rows'         => 10,
            'cols'         => 70,
            'defaultvalue' => $community_data->description,
        ),
        'membershiptype' => array(
            'type'         => 'select',
            'title'        => get_string('membershiptype'),
            'options'      => $joinoptions,
            'defaultvalue' => $community_data->jointype,
            'help'         => true,
        ),
        'id'          => array(
            'type'         => 'hidden',
            'value'        => $id,
        ),
        'submit'   => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('savecommunity'), get_string('cancel')),
        ),
    ),
));

function editcommunity_validate(Pieform $form, $values) {
    global $USER;
    global $SESSION;

    $cid = get_field('community', 'id', 'owner', $USER->get('id'), 'name', $values['name']);

    if ($cid && $cid != $values['id']) {
        $form->set_error('name', get_string('communityalreadyexists'));
    }
}

function editcommunity_cancel_submit() {
    redirect('/contacts/communities/owned.php');
}

function editcommunity_submit(Pieform $form, $values) {
    global $USER;
    global $SESSION;

    db_begin();

    $now = db_format_timestamp(time());

    update_record(
        'community',
        (object) array(
            'id'             => $values['id'],
            'name'           => $values['name'],
            'description'    => $values['description'],
            'jointype'       => $values['membershiptype'],
            'mtime'          => $now,
        ),
        'id'
    );

    $SESSION->add_ok_msg(get_string('communitysaved'));

    db_commit();

    redirect('/contacts/communities/owned.php');
}

$smarty = smarty();

$smarty->assign('editcommunity', $editcommunity);

$smarty->display('contacts/communities/edit.tpl');

?>
