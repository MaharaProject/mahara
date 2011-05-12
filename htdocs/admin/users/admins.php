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
require_once('pieforms/pieform.php');
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
            'filter' => false,
            'lefttitle' => get_string('potentialadmins', 'admin'),
            'righttitle' => get_string('currentadmins', 'admin'),
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
            'type' => 'submit',
            'value' => get_string('submit')
        )
    )
));

function adminusers_submit(Pieform $form, $values) {
    global $SESSION;
    
    db_begin();
    execute_sql('UPDATE {usr}
        SET admin = 0
        WHERE admin = 1');
    execute_sql('UPDATE {usr}
        SET admin = 1
        WHERE id IN (' . join(',', array_map('intval', $values['users'])) . ')');
    activity_add_admin_defaults($values['users']);
    db_commit();
    $SESSION->add_ok_msg(get_string('adminusersupdated', 'admin'));
    redirect('/admin/users/admins.php');
}

$smarty = smarty();
$smarty->assign('adminusersform', $form);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/users/admin.tpl');
