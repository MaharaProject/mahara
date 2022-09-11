<?php
/**
 * Assigning institution support administrators
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// NOTE: This script is VERY SIMILAR to the adminusers.php script, a bug fixed
// here might need to be fixed there too.
define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('institutionsupportadmin', 'admin'));
define('MENUITEM', 'manageinstitutions/institutionsupportadmins');

require_once('institution.php');
$s = institution_selector_for_page(param_alphanum('institution', false),
                                   get_config('wwwroot') . 'admin/users/institutionsupportadmins.php');
$institution = $s['institution'];

if ($institution === false) {
    $smarty = smarty();
    setpageicon($smarty, 'icon-university');
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

// Get users who are currently supportadmins
$supportadminusers = get_column_sql('SELECT ui.usr
    FROM {usr_institution} ui
    LEFT JOIN  {usr} u ON ui.usr = u.id
    WHERE ui.supportadmin = 1
    AND ui.institution = ?
    AND u.deleted = 0', array($institution));

$form = array(
    'name' => 'supportadminusers',
    'checkdirtychange' => false,
    'elements' => array(
        'users' => array(
            'type' => 'userlist',
            'title' => get_string('searchforaperson', 'admin'),
            'defaultvalue' => $supportadminusers,
            'lefttitle' => get_string('institutionmembers', 'admin'),
            'righttitle' => get_string('institutionsupportadmin', 'admin'),
            'leftarrowlabel' => get_string('makesupportadminintousers', 'admin'),
            'rightarrowlabel' => get_string('makeusersintosupportadmin', 'admin'),
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

function supportadminusers_validate(Pieform $form, $values) {
    // If the institution has no members show error
    if (!(get_column('usr_institution', 'usr', 'institution', $values['institution']))) {
        $form->set_error(null, get_string('nousersselected', 'admin'));
    }
}

function supportadminusers_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    $inst = $values['institution'];
    if (empty($inst) || !$USER->can_edit_institution($inst)) {
        $SESSION->add_error_msg(get_string('notadminforinstitution', 'admin'));
        redirect('/admin/users/institutionsupportadmins.php');
    }

    db_begin();
    execute_sql('UPDATE {usr_institution}
        SET supportadmin = 0
        WHERE supportadmin = 1 AND institution = ' . db_quote($inst));
    if ($values['users']) {
        execute_sql('UPDATE {usr_institution}
            SET supportadmin = 1
            WHERE usr IN (' . join(',', array_map('intval', $values['users'])) . ') AND institution = ' . db_quote($inst));
    }
    db_commit();
    $SESSION->add_ok_msg(get_string('supportadminusersupdated', 'admin'));
    redirect('/admin/users/institutionsupportadmins.php?institution=' . $inst);
}
$form = pieform($form);
$smarty = smarty();
setpageicon($smarty, 'icon-university');
$smarty->assign('institutionselector', $s['institutionselector']);
$smarty->assign('INLINEJAVASCRIPT', $s['institutionselectorjs'] . '
jQuery(function($) {
  formchangemanager.add(\'supportadminusers\');
  formchangemanager.unbindForm(\'supportadminusers\');
});');
$smarty->assign('staffusersform', $form);
$smarty->display('admin/users/institutionsupportadmin.tpl');
