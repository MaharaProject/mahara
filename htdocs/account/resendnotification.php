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

$deleteform = pieform(array(
    'name' => 'account_resend',
    'plugintype' => 'core',
    'pluginname' => 'account',
    'elements'   => array(
          'message' => array(
              'type'  => 'textarea',
              'title' => get_string('message'),
              'cols'  => 50,
              'rows'  => 4,
              'rules' => array('required' => true),
          ),
        'submit' => array(
            'class' => 'btn-secondary',
            'type' => 'submit',
            'value' => get_string('resenddeletionnotification', 'account'),
        ),
    ),
));

function account_resend_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    $userid = $USER->get('id');
    $user = new User;
    $user->find_by_id($userid);

    $admins = $user->get_approval_admins();
    $user->notify_admins_pending_deletion($admins, strip_tags(clean_html($values['message'])), 1);

    redirect('/account/index.php');
}

$smarty = smarty();
$smarty->assign('delete_form', $deleteform);
$smarty->display('account/resendnotification.tpl');
