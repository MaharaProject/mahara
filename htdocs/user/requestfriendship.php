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

$id = param_integer('id');
$returnto = param_alpha('returnto', 'myfriends');

switch ($returnto) {
case 'find': $goto = 'user/find.php'; break;
case 'view': $goto = 'user/view.php?id=' . $id; break;
default:
    $goto = 'user/myfriends.php';
}
$goto = get_config('wwwroot') . $goto;

if (is_friend($id, $USER->get('id'))) {
    $SESSION->add_ok_msg(get_string('alreadyfriends', 'group', display_name($id)));
    redirect($goto);
}
else if (get_friend_request($id, $USER->get('id'))) {
    $SESSION->add_info_msg(get_string('friendshipalreadyrequestedowner', 'group', display_name($id)));
    redirect(get_config('wwwroot') . 'user/myfriends.php?filter=pending');
}

if (get_account_preference($id, 'friendscontrol') != 'auth'
    || $id == $USER->get('id')
    || !($user = get_record('usr', 'id', $id, 'deleted', 0))) {
    throw new AccessDeniedException(get_string('cantrequestfriendship', 'group'));
}

$user->introduction = get_field('artefact', 'title', 'artefacttype', 'introduction', 'owner', $id);

define('TITLE', get_string('sendfriendshiprequest', 'group', display_name($id)));

$form = pieform(array(
    'name' => 'requestfriendship',
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
            'value' => array(get_string('requestfriendship', 'group'), get_string('cancel')),
            'goto' => $goto,
        )
    )
));

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('form', $form);
$smarty->assign('user', $user);
$smarty->display('user/requestfriendship.tpl');

function requestfriendship_submit(Pieform $form, $values) {
    global $USER, $SESSION, $id, $goto;
    
    $loggedinid = $USER->get('id');
    $user = get_record('usr', 'id', $id);

    // friend db record
    $f = new StdClass;
    $f->ctime = db_format_timestamp(time());
    
    // notification info
    $n = new StdClass;
    $n->url = get_config('wwwroot') . 'user/view.php?id=' . $loggedinid;
    $n->users = array($user->id);
    $lang = get_user_language($user->id);
    $displayname = display_name($USER, $user);
    $n->strings->urltext = (object) array('key' => 'Requests');

    $f->owner     = $id;
    $f->requester = $loggedinid;
    $f->message    = $values['message'];
    insert_record('usr_friend_request', $f);
    $n->subject = get_string_from_language($lang, 'requestedfriendlistsubject', 'group');
    if (isset($values['message']) && !empty($values['message'])) {
        $n->message = get_string_from_language($lang, 'requestedfriendlistmessagereason', 'group', $displayname) . $values['message'];
    }
    else {
        $n->message = get_string_from_language($lang, 'requestedfriendlistmessage', 'group', $displayname);
    }
    require_once('activity.php');
    activity_occurred('maharamessage', $n);

    handle_event('addfriendrequest', array('requester' => $loggedinid, 'owner' => $id));

    $SESSION->add_ok_msg(get_string('friendformrequestsuccess', 'group', display_name($id)));
    redirect($goto);
}
