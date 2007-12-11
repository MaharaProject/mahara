<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage admin
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

// NOTE: This script is VERY SIMILAR to the adminusers.php script, a bug fixed
// here might need to be fixed there too.
define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'configusers/staffusers');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('staffusers', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'staffusers');
require_once('pieforms/pieform.php');
$smarty = smarty();

require_once('institution.php');
$institution = add_institution_selector_to_page(&$smarty, param_alphanum('institution', false), 
                                                get_config('wwwroot') . 'admin/users/institutionstaff.php');

// Get users who are currently staff
$staffusers = get_column('usr_institution', 'usr', 'staff', 1, 'institution', $institution);

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
            'searchparams' => array('limit' => 100, 'query' => '', 'raw' => true, 'action' => 'search',
                                    'institution' => $institution),
            'searchscript' => 'admin/users/search.json.php',
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
    if (empty($inst) || !$USER->is_institutional_admin($inst)) {
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
            WHERE usr IN (' . join(',', $values['users']) . ') AND institution = ' . db_quote($inst));
    }
    db_commit();
    $SESSION->add_ok_msg(get_string('staffusersupdated', 'admin'));
    redirect('/admin/users/institutionstaff.php?institution=' . $inst);
}

$smarty->assign('staffusersform', pieform($form));
$smarty->display('admin/users/institutionstaff.tpl');

?>
