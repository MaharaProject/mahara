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

$deleteform = array(
    'name' => 'account_delete',
    'plugintype' => 'core',
    'pluginname' => 'account',
);

$userid = $USER->get('id');
$user = new User;
$user->find_by_id($userid);
$requiresapproval = $user->requires_delete_approval();

if ($requiresapproval) {
    $elements = array(
        'reason' => array(
            'type'  => 'textarea',
            'title' => get_string('reason'),
            'cols'  => 50,
            'rows'  => 4,
            'rules' => array('required' => true),
        ),
        'submit' => array(
            'class' => 'btn-secondary',
            'type' => 'submitcancel',
            'value' => array(get_string('senddeletenotification', 'mahara'), get_string('back')),
            'goto' => get_config('wwwroot'). 'account/index.php',
        ),
    );
}
else {
    $authobj = AuthFactory::create($USER->authinstance);
    if (method_exists($authobj, 'change_password')) {
        $elements['confirmpassword'] = array(
            'type' => 'password',
            'title' => get_string('oldpassword'),
            'help'  => false,
            'rules' => array(
                'required' => true,
            ),
            'autocomplete' => 'off',
        );
    }
    $elements['submit'] = array(
        'class' => 'btn-secondary',
        'type' => 'submitcancel',
        'value' => array(get_string('deleteaccount1', 'mahara'), get_string('back')),
        'goto' => get_config('wwwroot'). 'account/index.php',
    );
}

$deleteform['elements'] = $elements;

$deleteform = pieform($deleteform);

function account_delete_validate(Pieform $form, $values) {
    global $USER;

    $authobj = AuthFactory::create($USER->authinstance);
    if (isset($values['confirmpassword'])) {
        if ($values['confirmpassword'] !== '') {
            try {
                if (!$authobj->authenticate_user_account($USER, $values['confirmpassword'])) {
                    $form->set_error('confirmpassword', get_string('oldpasswordincorrect', 'account'));
                    return;
                }
            }
            // propagate error correctly for User validation issues - this should
            // be catching AuthUnknownUserException and AuthInstanceException
            catch  (UserException $e) {
                $form->set_error('confirmpassword', $e->getMessage());
                return;
            }
        }
        else {
            $form->set_error('confirmpassword', get_string('mustspecifyoldpassword'));
        }
    }
}

function account_delete_submit(Pieform $form, $values) {
    global $SESSION, $USER, $user;
    $userid = $USER->get('id');

    // check if user needs approval to delete its account
    if (!$user->requires_delete_approval()) {
        $USER->logout();
        delete_user($userid);
        $SESSION->add_ok_msg(get_string('accountdeleted', 'account'));
    }
    else {
        $admins = $user->get_approval_admins();
        set_account_pending_deletion($userid, strip_tags(clean_html($values['reason'])));
        $user->notify_admins_pending_deletion($admins, $values['reason']);
        $SESSION->add_ok_msg(get_string('pendingdeletionemailsent', 'account'));
    }
    redirect('/account/index.php');
}

$smarty = smarty();
$smarty->assign('requiresapproval', $requiresapproval);
$smarty->assign('delete_form', $deleteform);
$smarty->assign('fullname', full_name($USER));
$smarty->assign('displayusername', display_username($USER));
$smarty->display('account/delete.tpl');
