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
define('MENUITEM', 'groups');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
$groupid = param_integer('id');

define('GROUP', $groupid);
$group = group_current_group();

if (!$group->request
    || record_exists('group_member', 'group', $groupid, 'member', $USER->get('id'))
    || record_exists('group_member_request', 'group', $groupid, 'member', $USER->get('id'))) {
    throw new AccessDeniedException(get_string('cannotrequestjoingroup', 'group'));
}

define('TITLE', $group->name);

$goto = get_config('wwwroot') . 'group/index.php';

$form = pieform(array(
    'name' => 'requestjoingroup',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'reason' => array(
            'type' => 'textarea',
            'title' => get_string('reason'),
            'cols'  => 50,
            'rows'  => 4,
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'class' => 'btn-primary',
            'value' => array(get_string('request', 'group'), get_string('cancel')),
            'goto' => $goto
        )
    ),
));

$smarty = smarty();
$smarty->assign('subheading', get_string('requestjoinspecifiedgroup', 'group', $group->name));
$smarty->assign('form', $form);
$smarty->display('group/requestjoin.tpl');

function requestjoingroup_submit(Pieform $form, $values) {
    global $SESSION, $USER, $group, $goto;
    insert_record(
        'group_member_request',
        (object)array(
            'group' => $group->id,
            'member' => $USER->get('id'),
            'ctime' => db_format_timestamp(time()),
            'reason' => isset($values['reason']) ? $values['reason'] : null
        )
    );
    // Send request to all group admins
    require_once('activity.php');
    $message = (object) array(
        'key' => 'grouprequestmessage',
        'section' => 'group',
        'args' => array(
            display_name($USER, 0, true),
            $group->name
        )
    );

    if (isset($values['reason']) && $values['reason'] != '') {
        $message->key = 'grouprequestmessagereason';
        $message->args[] = $values['reason'];
    }

    activity_occurred('groupmessage', array(
        'group'   => $group->id,
        'roles'   => array('admin'),
        'url'     => 'group/members.php?id=' . $group->id . '&membershiptype=request',
        'strings' => (object) array(
            'subject' => (object) array(
                'key' => 'grouprequestsubject',
                'section' => 'group',
            ),
            'message' => $message,
            'urltext' => (object) array(
                'key'     => 'pendingmembers',
                'section' => 'group',
            ),
        ),
    ));

    $SESSION->add_ok_msg(get_string('grouprequestsent', 'group'));
    redirect($goto);
}
