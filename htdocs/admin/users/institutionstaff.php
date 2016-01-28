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
define('TITLE', get_string('institutionstaff', 'admin'));
define('MENUITEM', 'manageinstitutions/institutionstaff');

require_once('institution.php');
$s = institution_selector_for_page(param_alphanum('institution', false),
                                   get_config('wwwroot') . 'admin/users/institutionstaff.php');
$institution = $s['institution'];

$smarty = smarty();

setpageicon($smarty, 'icon-university');

if ($institution === false) {
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

// Get users who are currently staff
$staffusers = get_column_sql('SELECT ui.usr
    FROM {usr_institution} ui
    LEFT JOIN  {usr} u ON ui.usr = u.id
    WHERE ui.staff = 1
    AND ui.institution = ?
    AND u.deleted = 0', array($institution));

$form = array(
    'name' => 'staffusers',
    'checkdirtychange' => false,
    'elements' => array(
        'users' => array(
            'type' => 'userlist',
            'title' => get_string('staffusers', 'admin'),
            'defaultvalue' => $staffusers,
            'lefttitle' => get_string('institutionmembers', 'admin'),
            'righttitle' => get_string('institutionstaff', 'admin'),
            'leftarrowlabel' => get_string('makestaffintousers', 'admin'),
            'rightarrowlabel' => get_string('makeusersintostaff', 'admin'),
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

function staffusers_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    $inst = $values['institution'];
    if (empty($inst) || !$USER->can_edit_institution($inst)) {
        $SESSION->add_error_msg(get_string('notadminforinstitution', 'admin'));
        redirect('/admin/users/institutionstaff.php');
    }

    db_begin();
    execute_sql('UPDATE {usr_institution}
        SET staff = 0
        WHERE staff = 1 AND institution = ' . db_quote($inst));
    if ($values['users']) {
        execute_sql('UPDATE {usr_institution}
            SET staff = 1
            WHERE usr IN (' . join(',', array_map('intval', $values['users'])) . ') AND institution = ' . db_quote($inst));
    }
    db_commit();
    $SESSION->add_ok_msg(get_string('staffusersupdated', 'admin'));
    redirect('/admin/users/institutionstaff.php?institution=' . $inst);
}

$smarty->assign('institutionselector', $s['institutionselector']);
$smarty->assign('INLINEJAVASCRIPT', $s['institutionselectorjs'] . '
addLoadEvent(function() {
    formchangemanager.add(\'staffusers\');
    formchangemanager.unbindForm(\'staffusers\');
});');
$smarty->assign('staffusersform', pieform($form));
$smarty->display('admin/users/institutionstaff.tpl');
