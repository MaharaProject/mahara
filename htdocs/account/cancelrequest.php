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
define('MENUITEM', 'settings/preferences');

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('deleteaccountuser', 'account', display_name($USER, null, false, false, true)));

if (!$USER->can_delete_self()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$cancelrequestform = pieform(array(
    'name' => 'cancelrequest',
    'plugintype' => 'core',
    'pluginname' => 'account',
    'elements'   => array(
        'user' => array(
              'type' => 'hidden',
              'value' => $USER->id,
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'class' => 'btn-secondary',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . "account/index.php",
        ),
    ),
));

function cancelrequest_submit(Pieform $form, $values) {
    global $SESSION;
    if ($request = get_record('usr_pendingdeletion', 'usr', $values['user'])) {
        delete_records('usr_pendingdeletion', 'id', $request->id);

        $userid = $values['user'];
        $user = new User;
        $user->find_by_id($userid);

        $admins = $user->get_approval_admins();
        $user->notify_admins_pending_deletion($admins, '', 2);

        $SESSION->add_ok_msg(get_string('deleterequestcanceled', 'account'));
    }
    redirect('/account/index.php');
}

$smarty = smarty();
$smarty->assign('cancelrequestform', $cancelrequestform);
$smarty->assign('userdisplayname', display_name($USER));
$smarty->display('account/cancelrequest.tpl');
