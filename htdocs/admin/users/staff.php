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

// NOTE: This script is VERY SIMILAR to the adminusers.php script, a bug fixed
// here might need to be fixed there too.
define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configusers/staffusers');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('staffusers', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'staffusers');
require_once('activity.php');

// Get users who are currently staff
$staffusers = get_column('usr', 'id', 'staff', 1, 'deleted', 0);

$form = pieform(array(
    'name' => 'staffusers',
    'elements' => array(
        'users' => array(
            'type' => 'userlist',
            'title' => get_string('staffusers', 'admin'),
            'defaultvalue' => $staffusers,
            'lefttitle' => get_string('potentialstaff', 'admin'),
            'righttitle' => get_string('currentstaff', 'admin'),
            'leftarrowlabel' => get_string('makestaffintousers', 'admin'),
            'rightarrowlabel' => get_string('makeusersintostaff', 'admin'),
            'searchparams' => array(
                'query' => '',
                'limit' => 250,
                'orderby' => 'lastname',
            ),
        ),
        'submit' => array(
            'type' => 'submit',
            'class' => 'btn-primary',
            'value' => get_string('submit')
        )
    )
));

function staffusers_submit(Pieform $form, $values) {
    global $SESSION;

    db_begin();
    execute_sql('UPDATE {usr}
        SET staff = 0
        WHERE staff = 1');
    if ($values['users']) {
        execute_sql('UPDATE {usr}
            SET staff = 1
            WHERE id IN (' . join(',', array_map('intval', $values['users'])) . ')');
    }
    db_commit();
    $SESSION->add_ok_msg(get_string('staffusersupdated', 'admin'));
    redirect('/admin/users/staff.php');
}

$smarty = smarty();
setpageicon($smarty, 'icon-user');

$smarty->assign('staffusersform', $form);
$smarty->display('admin/users/staff.tpl');
