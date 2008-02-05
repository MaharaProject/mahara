<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Clare Lenihan <clare@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups/findfriends');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require('searchlib.php');
safe_require('search', 'internal');

$id = param_integer('id');

if (!can_send_message($USER, $id)) {
	throw new AccessDeniedException(get_string('cantmessageuser', 'group'));
}

define('TITLE', get_string('sendmessageto', 'group', display_name($id)));

$form = pieform(array(
    'name' => 'sendmessage',
    'autofocus' => false,
    'elements' => array(
        'message' => array(
            'type'  => 'textarea',
            'title' => get_string('message'),
            'cols'  => 50,
            'rows'  => 4,       
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('sendmessage', 'group'), get_string('cancel')),
            'goto' => get_config('wwwroot') . 'user/view.php?id=' . $id,
        )
    )
));

$smarty = smarty();
$smarty->assign('heading', TITLE);
$smarty->assign('form', $form);
$smarty->display('user/denyrequest.tpl');

function sendmessage_submit(Pieform $form, $values) {
    global $USER, $SESSION, $id;
    send_user_message($USER, $values['message']);
    $SESSION->add_ok_msg(get_string('messagesent', 'group'));
    switch (param_alpha('returnto', 'myfriends')) {
        case 'find':
            redirect('/user/find.php');
            break;
        case 'view':
            redirect('/user/view.php?id=' . $id);
            break;
        default:
            redirect('/user/myfriends.php');
            break;
    }
}

?>
