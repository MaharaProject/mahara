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
define('TITLE', get_string('createcommunity'));

$prefix = get_config('dbprefix');

$joinoptions = array(
    'invite'     => get_string('membershiptype.invite'),
    'request'    => get_string('membershiptype.request'),
    'open'       => get_string('membershiptype.open'),
);
if ($USER->get('admin') || $USER->get('staff')) {
    $joinoptions['controlled'] = get_string('membershiptype.controlled');
}

$createcommunity = pieform(array(
    'name'     => 'createcommunity',
    'method'   => 'post',
    'plugintype' => 'core',
    'pluginname' => 'communities',
    'elements' => array(
        'name' => array(
            'type'         => 'text',
            'title'        => get_string('communityname'),
            'rules'        => array( 'required' => true, 'maxlength' => 128 ),
        ),
        'description' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('communitydescription'),
            'rows'         => 10,
            'cols'         => 80,
        ),
        'membershiptype' => array(
            'type'         => 'select',
            'title'        => get_string('membershiptype'),
            'options'      => $joinoptions,
            'defaultvalue' => 'open',
            'help'         => true,
        ),
        'submit'   => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('savecommunity'), get_string('cancel')),
        ),
    ),
));

function createcommunity_validate(Pieform $form, $values) {
    global $USER;
    global $SESSION;

    $cid = get_field('community', 'id', 'owner', $USER->get('id'), 'name', $values['name']);

    if ($cid) {
        $form->set_error('name', get_string('communityalreadyexists'));
    }
}

function createcommunity_cancel_submit() {
    redirect('/contacts/communities/owned.php');
}

function createcommunity_submit(Pieform $form, $values) {
    global $USER;
    global $SESSION;

    db_begin();

    $now = db_format_timestamp(time());

    $id = insert_record(
        'community',
        (object) array(
            'name'           => $values['name'],
            'description'    => $values['description'],
            'jointype'       => $values['membershiptype'],
            'owner'          => $USER->get('id'),
            'ctime'          => $now,
            'mtime'          => $now,
        ),
        'id',
        true
    );

    // If the user is a staff member, they should be added as a tutor automatically
    if ($values['membershiptype'] == 'controlled' && $USER->get('staff')) {
        log_debug('Adding staff user to community');
        insert_record(
            'community_member',
            (object) array(
                'community' => $id,
                'member'    => $USER->get('id'),
                'ctime'     => $now,
                'tutor'     => 1
            )
        );
    }

    $SESSION->add_ok_msg(get_string('communitysaved'));

    db_commit();

    redirect('/contacts/communities/owned.php');
}

$smarty = smarty();

$smarty->assign('createcommunity', $createcommunity);

$smarty->display('contacts/communities/create.tpl');

?>
