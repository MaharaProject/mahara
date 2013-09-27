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
require_once('pieforms/pieform.php');
define('TITLE', get_string('denyfriendrequest', 'group'));
require_once('searchlib.php');
safe_require('search', 'internal');

$id = param_integer('id');
$returnto = param_alpha('returnto', 'myfriends');

if (!record_exists('usr_friend_request', 'owner', $USER->get('id'), 'requester', $id)
    || !($user = get_record('usr', 'id', $id, 'deleted', 0))) {
    throw new AccessDeniedException(get_string('cantdenyrequest', 'group'));
}

$user->introduction = get_field('artefact', 'title', 'artefacttype', 'introduction', 'owner', $id);

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
            'value' => array(get_string('denyfriendrequestlower', 'group'), get_string('cancel')),
            'goto' => get_config('wwwroot') . ($returnto == 'find' ? 'user/find.php' : ($returnto == 'view' ? profile_url($user, false) : 'user/myfriends.php')),
        )
    )
));

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
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
    switch (param_alpha('returnto', 'myfriends')) {
        case 'find':
            redirect('/user/find.php');
            break;
        case 'view':
            redirect(profile_url($user));
            break;
        default:
            redirect('/user/myfriends.php');
            break;
    }
}
