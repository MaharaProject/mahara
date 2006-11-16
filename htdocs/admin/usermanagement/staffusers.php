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
define('MENUITEM', 'usermanagement');
define('SUBMENUITEM', 'staffusers');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('form.php');
$smarty = smarty();

// Get users who are currently staff
$staffusers = get_column('usr', 'id', 'staff', 1);

$form = array(
    'name' => 'staffusers',
    'method' => 'post',
    'action' => '',
    'elements' => array(
        'users' => array(
            'type' => 'userlist',
            'title' => get_string('staffusers', 'admin'),
            'defaultvalue' => $staffusers,
            'filter' => false
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('submit')
        )
    )
);

function staffusers_submit($values) {
    global $SESSION;
    $table = get_config('dbprefix') . 'usr';
    
    db_begin();
    execute_sql('UPDATE ' . $table . ' SET staff = 0 WHERE staff = 1');
    execute_sql('UPDATE ' . $table . ' SET staff = 1 WHERE id IN (' . join(',', $values['users']) . ')');
    db_commit();
    $SESSION->add_ok_msg(get_string('staffusersupdated', 'admin'));
    redirect(get_config('wwwroot') . 'admin/usermanagement/staffusers.php');
}

$smarty->assign('staffusersform', form($form));
$smarty->display('admin/usermanagement/staffusers.tpl');

?>
