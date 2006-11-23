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
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('INSTALLER', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require(get_config('libroot') . 'upgrade.php');

$install = param_boolean('install');
if (!$install) {
    $name    = param_variable('name');
}

if ($install) {
    if (!get_config('installed')) {
        try {
            // Install the default institution
            db_begin();
            $institution = new StdClass;
            $institution->name = 'mahara';
            $institution->displayname = 'No Institution';
            $institution->authplugin  = 'internal';
            insert_record('institution', $institution);

            // Insert the root user
            $user = new StdClass;
            $user->username = 'root';
            $user->password = 'mahara';
            $user->institution = 'mahara';
            $user->passwordchange = 1;
            $user->admin = 1;
            $user->firstname = 'Admin';
            $user->lastname = 'User';
            $user->email = 'admin@example.org';
            $user->id = insert_record('usr', $user, 'id', true);
            set_profile_field($user->id, 'email', $user->email);
            set_profile_field($user->id, 'firstname', $user->firstname);
            set_profile_field($user->id, 'lastname', $user->lastname);

            set_config('installed', true);
            db_commit();
        }
        catch (SQLException $e) {
            echo json_encode(array(
                'success' => 0,
                'errormessage' => $e->getMessage()
            ));
            exit;
        }
    }

    echo json_encode(array('success' => 1));
    exit;
}

$upgrade = check_upgrades($name);
$data = array(
    'key'        => $name
);             

if (!empty($upgrade)) {
    $data['newversion'] = $upgrade->torelease . ' (' . $upgrade->to . ')' ;
    if ($name == 'core') {
        $funname = 'upgrade_core';
    } 
    else {
        $funname = 'upgrade_plugin';
    }
    try {
        $funname($upgrade);
        $data['success'] = 1;
        $data['install'] = $upgrade->install;
    } 
    catch (Exception $e) {
        $data['errormessage'] = $e->getMessage();
        $data['success']      = 0;
    }
}
else {
    $data['success'] = 1;
    $data['errormessage'] = get_string('nothingtoupgrade','admin');
}

// @todo json_reply?
echo json_encode($data);    
?>
