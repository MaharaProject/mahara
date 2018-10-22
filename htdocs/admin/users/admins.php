<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// NOTE: This script is VERY SIMILAR to the staffusers.php script, a bug fixed
// here might need to be fixed there too.
define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configusers/adminusers');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('adminusers', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'adminusers');
require_once('activity.php');

// Get users who are currently administrators
// @todo later, exclude the user with uid 1
$adminusers = get_column('usr', 'id', 'admin', 1, 'deleted', 0);

$form = pieform(array(
    'name' => 'adminusers',
    'elements' => array(
        'users' => array(
            'type' => 'userlist',
            'title' => get_string('adminusers', 'admin'),
            'defaultvalue' => $adminusers,
            'lefttitle' => get_string('potentialadmins', 'admin'),
            'righttitle' => get_string('currentadmins', 'admin'),
            'leftarrowlabel' => get_string('makeadminsintousers', 'admin'),
            'rightarrowlabel' => get_string('makeusersintoadmins', 'admin'),
            'searchparams' => array(
                'query' => '',
                'limit' => 250,
                'orderby' => 'lastname',
            ),
            'rules' => array(
                'required' => true
            )
        ),
        'submit' => array(
            'class' => 'btn-primary',
            'type' => 'submit',
            'value' => get_string('submit')
        )
    )
));

function adminusers_submit(Pieform $form, $values) {
    global $SESSION;

    db_begin();
    execute_sql('UPDATE {usr}
        SET "admin" = 0
        WHERE "admin" = 1');
    execute_sql('UPDATE {usr}
        SET "admin" = 1
        WHERE id IN (' . join(',', array_map('intval', $values['users'])) . ')');
    activity_add_admin_defaults($values['users']);
    db_commit();
    $SESSION->add_ok_msg(get_string('adminusersupdated', 'admin'));
    redirect('/admin/users/admins.php');
}

$smarty = smarty();
setpageicon($smarty, 'icon-user');

$smarty->assign('adminusersform', $form);
$smarty->display('admin/users/admin.tpl');
