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
define('TITLE', get_string('deleteaccount', 'account'));

if (!$USER->can_delete_self()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$deleteform = pieform(array(
    'name' => 'account_delete',
    'plugintype' => 'core',
    'pluginname' => 'account',
    'elements'   => array(
        'submit' => array(
            'class' => 'btn-default',
            'type' => 'submit',
            'value' => get_string('deleteaccount', 'mahara', display_username($USER), full_name($USER)),
        ),
    ),
));

function account_delete_submit(Pieform $form, $values) {
    global $SESSION, $USER;
    $userid = $USER->get('id');
    $USER->logout();
    delete_user($userid);
    $SESSION->add_ok_msg(get_string('accountdeleted', 'account'));
    redirect('/index.php');
}

$smarty = smarty();
$smarty->assign('delete_form', $deleteform);
$smarty->display('account/delete.tpl');
