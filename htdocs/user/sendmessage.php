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
define('MENUITEM', 'groups/findfriends');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require('searchlib.php');
safe_require('search', 'internal');

$id = param_integer('id');
$replytoid = param_integer('replyto', null);
$replyto = false;
if (!is_null($replytoid)) {
    $replyto = get_record_sql('
        SELECT
            a.subject, a.message, a.url
        FROM {notification_internal_activity} a
            JOIN {activity_type} t ON a.type = t.id
        WHERE t.name = ? AND a.id = ? AND a.usr = ?',
        array('usermessage', $replytoid, $USER->get('id')));
    if (!$replyto) {
        throw new AccessDeniedException(get_string('cantviewmessage', 'group'));
    }
    // Make sure the message was sent by the user being replied to
    $bits = parse_url($replyto->url);
    parse_str($bits['query'], $params);
    if (empty($params['id']) || $params['id'] != $id) {
        throw new AccessDeniedException(get_string('cantviewmessage', 'group'));
    }
}

$returnto = param_alpha('returnto', 'myfriends');

$user = get_record('usr', 'id', $id, 'deleted', 0);

if (!$user || !can_send_message($USER->to_stdclass(), $id)) {
	throw new AccessDeniedException(get_string('cantmessageuser', 'group'));
}

$user->introduction = get_field('artefact', 'title', 'artefacttype', 'introduction', 'owner', $id);

$quote = '';
if ($replyto) {
    $replyto->lines = split("\n", $replyto->message);
    foreach ($replyto->lines as $line) {
        $quote .= "\n> " . wordwrap($line, 75, "\n> ");
    }
    define('TITLE', get_string('viewmessage', 'group'));
}
else {
    define('TITLE', get_string('sendmessageto', 'group', display_name($id)));
}


$form = pieform(array(
    'name' => 'sendmessage',
    'autofocus' => false,
    'elements' => array(
        'message' => array(
            'type'  => 'textarea',
            'title' => $replyto ? get_string('Reply', 'group') : get_string('message'),
            'cols'  => 80,
            'rows'  => 10,
            'defaultvalue' => $quote,
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array($replyto ? get_string('Reply', 'group') : get_string('sendmessage', 'group'), get_string('cancel')),
            'goto' => get_config('wwwroot') . ($returnto == 'find' ? 'user/find.php' : ($returnto == 'view' ? 'user/view.php?id=' . $id : 'user/myfriends.php')),
        )
    )
));

$smarty = smarty();
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('form', $form);
$smarty->assign('user', $user);
$smarty->assign('replyto', $replyto);
$smarty->display('user/sendmessage.tpl');

function sendmessage_submit(Pieform $form, $values) {
    global $USER, $SESSION, $id;
    $user = get_record('usr', 'id', $id);
    send_user_message($user, $values['message']);
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
