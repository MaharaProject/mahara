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
define('TITLE', get_string('suspenduser', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
require_once('pieforms/pieform.php');


$id = param_integer('id');
if (!$user = get_record('usr', 'id', $id)) {
    throw new UserNotFoundException("User not found");
}

$f = pieform(array(
    'name'                => 'suspend',
    'elements'            => array(
        'id' => array(
            'type'    => 'hidden',
            'value'   => $id,
        ),
        'reason' => array(
            'type'        => 'text',
            'title'       => get_string('reason'),
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('suspenduser','admin'), get_string('cancel')),
        ),
    )
));

function suspend_submit(Pieform $form, $values) {
    global $SESSION;
    suspend_user($values['id'], $values['reason']);
    $SESSION->add_ok_msg(get_string('usersuspended', 'admin'));
    redirect('/user/view.php?id=' . $values['id']);
}

function suspend_cancel_submit() {
    redirect('/admin/users/search.php');
}

$smarty = smarty();
$smarty->assign('user', $user);
$smarty->assign('form', $f);
$smarty->display('admin/users/suspend.tpl');

?>
