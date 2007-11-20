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
 * @subpackage admin
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('edituseraccount', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
require_once('pieforms/pieform.php');


$id = param_integer('id');
if (!$user = get_record('usr', 'id', $id)) {
    throw new UserNotFoundException("User not found");
}


if (empty($user->suspendedcusr)) {
    $suspendform = pieform(array(
        'name'       => 'suspend',
        'plugintype' => 'core',
        'pluginname' => 'admin',
        'elements'   => array(
            'id' => array(
                 'type'    => 'hidden',
                 'value'   => $id,
            ),
            'reason' => array(
                'type'        => 'text',
                'title'       => get_string('reason'),
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('suspenduser','admin'),
            ),
        )
    ));
} else {
    $suspendform = pieform(array(
        'name'       => 'unsuspend',
        'plugintype' => 'core',
        'pluginname' => 'admin',
        'elements'   => array(
            'id' => array(
                 'type'    => 'hidden',
                 'value'   => $id,
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('unsuspenduser','admin'),
            ),
        )
    ));
}

function suspend_submit(Pieform $form, $values) {
    global $SESSION;
    suspend_user($values['id'], $values['reason']);
    $SESSION->add_ok_msg(get_string('usersuspended', 'admin'));
    redirect('/admin/users/edit.php?id=' . $values['id']);
}

function unsuspend_submit(Pieform $form, $values) {
    global $SESSION;
    unsuspend_user($values['id']);
    $SESSION->add_ok_msg(get_string('userunsuspended', 'admin'));
    redirect('/admin/users/edit.php?id=' . $values['id']);
}


// Site-wide account settings

$elements['id'] = array(
    'type'    => 'hidden',
    'rules'   => array('integer' => true),
    'value'   => $id,
);
$elements['password'] = array(
    'type'         => 'text',
    'title'        => get_string('resetpassword','admin'),
);
$elements['passwordchange'] = array(
    'type'         => 'checkbox',
    'title'        => get_string('forcepasswordchange','admin'),
    'defaultvalue' => $user->passwordchange,
);
$elements['staff'] = array(
    'type'         => 'checkbox',
    'title'        => get_string('sitestaff','admin'),
    'defaultvalue' => $user->staff,
);
$elements['admin'] = array(
    'type'         => 'checkbox',
    'title'        => get_string('siteadmin','admin'),
    'defaultvalue' => $user->admin,
);
$elements['quota'] = array(
    'type'         => 'text',
    'title'        => get_string('filequota','admin'),
    'defaultvalue' => $user->quota / 1048576,
    'rules'        => array('integer' => true),
);
$elements['submit'] = array(
    'type'  => 'submit',
    'value' => get_string('savechanges','admin'),
);

$mainform = pieform(array(
    'name'       => 'edituser',
    'renderer'   => 'table',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'elements'   => $elements,
));


function edituser_submit(Pieform $form, $values) {
    if (!$user = get_record('usr', 'id', $values['id'])) {
        return false;
    }

    if (isset($values['password']) && $values['password'] !== '') {
        $user->password = $values['password'];
    }
    $user->passwordchange = (int) ($values['passwordchange'] == 'on');
    $user->staff = (int) ($values['staff'] == 'on');
    $user->admin = (int) ($values['admin'] == 'on');
    $user->quota = $values['quota'] * 1048576;

    update_record('usr', $user);

    redirect('/admin/users/edit.php?id='.$user->id);
}




$smarty = smarty();
$smarty->assign('user', $user);
$smarty->assign('suspendform', $suspendform);
$smarty->assign('mainform', $mainform);
$smarty->display('admin/users/edit.tpl');

?>
