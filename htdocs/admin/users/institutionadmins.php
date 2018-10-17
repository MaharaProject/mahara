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
define('INSTITUTIONALADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('institutionadmins', 'admin'));
define('MENUITEM', 'manageinstitutions/institutionadmins');

require_once('institution.php');
$s = institution_selector_for_page(param_alphanum('institution', false),
                                   get_config('wwwroot') . 'admin/users/institutionadmins.php');
$institution = $s['institution'];

$smarty = smarty();
setpageicon($smarty, 'icon-university');

if ($institution === false) {
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

// Get users who are currently admins
$adminusers = get_column_sql('SELECT ui.usr
    FROM {usr_institution} ui
    LEFT JOIN  {usr} u ON ui.usr = u.id
    WHERE ui.admin = 1
    AND ui.institution = ?
    AND u.deleted = 0', array($institution));

$form = array(
    'name' => 'adminusers',
    'checkdirtychange' => false,
    'elements' => array(
        'users' => array(
            'type' => 'userlist',
            'title' => get_string('adminusers', 'admin'),
            'defaultvalue' => $adminusers,
            'lefttitle' => get_string('institutionmembers', 'admin'),
            'righttitle' => get_string('currentadmins', 'admin'),
            'leftarrowlabel' => get_string('makeadminsintousers', 'admin'),
            'rightarrowlabel' => get_string('makeusersintoadmins', 'admin'),
            'searchparams' => array('limit' => 100, 'query' => '', 'member' => 1,
                                    'institution' => $institution),
            'searchscript' => 'admin/users/userinstitutionsearch.json.php',
        ),
        'institution' => array(
            'type' => 'hidden',
            'value' => $institution,
        ),
        'submit' => array(
            'class' => 'btn-primary',
            'type' => 'submit',
            'value' => get_string('submit')
        )
    )
);

function adminusers_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    $inst = $values['institution'];
    if (empty($inst) || !$USER->can_edit_institution($inst)) {
        $SESSION->add_error_msg(get_string('notadminforinstitution', 'admin'));
        redirect('/admin/users/institutionadmins.php');
    }

    db_begin();
    execute_sql('UPDATE {usr_institution}
        SET "admin" = 0
        WHERE "admin" = 1 AND institution = ' . db_quote($inst));
    if ($values['users']) {
        execute_sql('UPDATE {usr_institution}
            SET "admin" = 1
            WHERE usr IN (' . join(',', array_map('intval', $values['users'])) . ') AND institution = ' . db_quote($inst));
    }
    require_once('activity.php');
    activity_add_admin_defaults($values['users']);
    db_commit();
    $SESSION->add_ok_msg(get_string('adminusersupdated', 'admin'));
    redirect('/admin/users/institutionadmins.php?institution=' . $inst);
}

$smarty->assign('institutionselector', $s['institutionselector']);
$smarty->assign('INLINEJAVASCRIPT', $s['institutionselectorjs'] . '
jQuery(function($) {
    formchangemanager.add(\'adminusers\');
    formchangemanager.unbindForm(\'adminusers\');
});');
$smarty->assign('adminusersform', pieform($form));
$smarty->display('admin/users/institutionadmins.tpl');
