<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups/findfriends');
require(dirname(dirname(__FILE__)) . '/init.php');

$id = param_integer('id');

if (get_account_preference($id, 'friendscontrol') != 'auth'
    || $id == $USER->get('id')
    || !($user = get_record('usr', 'id', $id, 'deleted', 0))) {
    throw new AccessDeniedException(get_string('cantrequestfriendship', 'group'));
}

$user->introduction = get_field('artefact', 'title', 'artefacttype', 'introduction', 'owner', $id);

define('TITLE', get_string('sendfriendshiprequest', 'group', display_name($id)));

$returnto = param_alpha('returnto', 'myfriends');
$offset = param_integer('offset', 0);
switch ($returnto) {
case 'find': $goto = 'user/find.php'; break;
case 'view': $goto = profile_url($user, false); break;
default:
    $goto = 'user/myfriends.php';
}
$goto .= (strpos($goto,'?')) ? '&offset=' . $offset : '?offset=' . $offset;
$goto = get_config('wwwroot') . $goto;

if (is_friend($id, $USER->get('id'))) {
    $SESSION->add_ok_msg(get_string('alreadyfriends', 'group', display_name($id)));
    redirect($goto);
}
else if (get_friend_request($id, $USER->get('id'))) {
    $SESSION->add_info_msg(get_string('friendshipalreadyrequestedowner', 'group', display_name($id)));
    redirect(get_config('wwwroot') . 'user/myfriends.php?filter=pending');
}

$form = pieform(array(
    'name' => 'requestfriendship',
    'autofocus' => false,
    'elements' => array(
        'message' => array(
            'type'  => 'textarea',
            'title' => get_string('message'),
            'cols'  => 50,
            'rows'  => 4,
            'rules' => array(
                'required' => true,
                'maxlength' => 255,
            ),
        ),
        'submit' => array(
            'class' => 'btn-primary',
            'type' => 'submitcancel',
            'value' => array(get_string('requestfriendship', 'group'), get_string('cancel')),
            'goto' => $goto,
        )
    )
));

$smarty = smarty();
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
    $n->url = profile_url($USER, false);
    $n->users = array($user->id);
    $n->fromuser = $loggedinid;
    $lang = get_user_language($user->id);
    $displayname = display_name($USER, $user);
    $n->strings = new stdClass;
    $n->strings->urltext = (object) array('key' => 'Requests');

    $f->owner     = $id;
    $f->requester = $loggedinid;
    $f->message    = $values['message'];
    insert_record('usr_friend_request', $f);
    $n->subject = get_string_from_language($lang, 'requestedfriendlistsubject', 'group');
    if (isset($values['message']) && !empty($values['message'])) {
        $n->message = get_string_from_language($lang, 'requestedfriendlistmessageexplanation', 'group', $displayname) . $values['message'];
    }
    else {
        $n->message = get_string_from_language($lang, 'requestedfriendlistinboxmessage', 'group', $displayname);
    }
    require_once('activity.php');
    activity_occurred('maharamessage', $n);

    handle_event('addfriendrequest', array('requester' => $loggedinid, 'owner' => $id));

    $SESSION->add_ok_msg(get_string('friendformrequestsuccess', 'group', display_name($id)));
    redirect($goto);
}
