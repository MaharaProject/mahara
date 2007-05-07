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
define('ADMIN', 1);
define('MENUITEM', 'configusers');
define('SUBMENUITEM', 'staffusers');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('staffusers', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'staffusers');
require_once('pieforms/pieform.php');
$smarty = smarty();

// Get users who are currently staff
$staffusers = get_column('usr', 'id', 'staff', 1);

$form = array(
    'name' => 'staffusers',
    'elements' => array(
        'users' => array(
            'type' => 'userlist',
            'title' => get_string('staffusers', 'admin'),
            'defaultvalue' => $staffusers,
            'filter' => false,
            'lefttitle' => get_string('potentialstaff', 'admin'),
            'righttitle' => get_string('currentstaff', 'admin')
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('submit')
        )
    )
);

function staffusers_submit(Pieform $form, $values) {
    global $SESSION;
    $table = get_config('dbprefix') . 'usr';
    
    db_begin();
    execute_sql('UPDATE ' . $table . '
        SET staff = 0
        WHERE staff = 1');
    if ($values['users']) {
        execute_sql('UPDATE ' . $table . '
            SET staff = 1
            WHERE id IN (' . join(',', $values['users']) . ')');
    }
    db_commit();
    $SESSION->add_ok_msg(get_string('staffusersupdated', 'admin'));
    redirect('/admin/users/staff.php');
}

$smarty->assign('staffusersform', pieform($form));
$smarty->display('admin/users/staff.tpl');

?>
