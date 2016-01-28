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
define('TITLE', get_string('denyfriendrequest', 'group'));
require_once('searchlib.php');
safe_require('search', 'internal');

$id = param_integer('id');

if (!record_exists('usr_friend_request', 'owner', $USER->get('id'), 'requester', $id)
    || !($user = get_record('usr', 'id', $id, 'deleted', 0))) {
    throw new AccessDeniedException(get_string('cantdenyrequest', 'group'));
}

$user->introduction = get_field('artefact', 'title', 'artefacttype', 'introduction', 'owner', $id);

$returnto = param_alpha('returnto', 'myfriends');
$offset = param_integer('offset', 0);
switch ($returnto) {
    case 'find':
        $goto = 'user/find.php';
        break;
    case 'view':
        $goto = profile_url($user, false);
        break;
     default:
        $goto = 'user/myfriends.php';
}
$goto .= (strpos($goto,'?') ? '&' : '?') . 'offset=' . $offset;
$goto = get_config('wwwroot') . $goto;

$form = pieform(array(
    'name' => 'denyrequest',
    'autofocus' => false,
    'elements' => array(
        'reason' => array(
            'type'  => 'textarea',
            'title' => get_string('rejectfriendshipreason', 'group'),
            'cols'  => 50,
            'rows'  => 4,
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'class' => 'btn-primary',
            'value' => array(get_string('denyfriendrequestlower', 'group'), get_string('cancel')),
            'goto' => $goto,
        )
    )
));

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->assign('user', $user);
$smarty->display('user/denyrequest.tpl');

function denyrequest_submit(Pieform $form, $values) {
    global $USER, $SESSION, $id;

    $loggedinid = $USER->get('id');
    $user = get_record('usr', 'id', $id);

    // friend db record
    $f = new StdClass;
    $f->ctime = db_format_timestamp(time());

    // notification info
    $n = new StdClass;
    $n->url = profile_url($USER, false);
    $n->users = array($user->id);
    $n->fromuser = $USER->get('id');
    $lang = get_user_language($user->id);
    $displayname = display_name($USER, $user);
    $n->urltext = $displayname;

    delete_records('usr_friend_request', 'owner', $loggedinid, 'requester', $id);
    $n->subject = get_string_from_language($lang, 'friendrequestrejectedsubject', 'group');
    if (isset($values['reason']) && !empty($values['reason'])) {
        $n->message = get_string_from_language($lang, 'friendrequestrejectedmessagereason', 'group', $displayname) . $values['reason'];
    }
    else {
        $n->message = get_string_from_language($lang, 'friendrequestrejectedmessage', 'group', $displayname);
    }
    require_once('activity.php');
    activity_occurred('maharamessage', $n);

    handle_event('removefriendrequest', array('owner' => $loggedinid, 'requester' => $id));

    $SESSION->add_ok_msg(get_string('friendformrejectsuccess', 'group'));
    $offset = param_integer('offset', 0);
    switch (param_alpha('returnto', 'myfriends')) {
        case 'find':
            $goto = 'user/find.php';
            break;
        case 'view':
            $goto = profile_url($user, false);
            break;
        default:
            $goto = 'user/myfriends.php';
            break;
    }
    $goto .= (strpos($goto,'?')) ? '&offset=' . $offset : '?offset=' . $offset;
    $goto = get_config('wwwroot') . $goto;
    redirect($goto);
}
