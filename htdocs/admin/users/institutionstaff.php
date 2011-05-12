<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

// NOTE: This script is VERY SIMILAR to the adminusers.php script, a bug fixed
// here might need to be fixed there too.
define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('institutionstaff', 'admin'));
require_once('pieforms/pieform.php');
define('MENUITEM', 'manageinstitutions/institutionstaff');

require_once('institution.php');
$s = institution_selector_for_page(param_alphanum('institution', false),
                                   get_config('wwwroot') . 'admin/users/institutionstaff.php');
$institution = $s['institution'];

$smarty = smarty();
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
    'elements' => array(
        'users' => array(
            'type' => 'userlist',
            'title' => get_string('staffusers', 'admin'),
            'defaultvalue' => $staffusers,
            'filter' => false,
            'lefttitle' => get_string('institutionmembers', 'admin'),
            'righttitle' => get_string('institutionstaff', 'admin'),
            'searchparams' => array('limit' => 100, 'query' => '', 'member' => 1, 
                                    'institution' => $institution),
            'searchscript' => 'admin/users/userinstitutionsearch.json.php',
        ),
        'institution' => array(
            'type' => 'hidden',
            'value' => $institution,
        ),
        'submit' => array(
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
$smarty->assign('INLINEJAVASCRIPT', $s['institutionselectorjs']);
$smarty->assign('staffusersform', pieform($form));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/users/institutionstaff.tpl');
