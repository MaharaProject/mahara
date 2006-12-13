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

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configusers');
define('SUBMENUITEM', 'adminnotifications');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

require_once('pieforms/pieform.php');

$prefix = get_config('dbprefix');

$sql = 'SELECT u.*, a.activity, a.method 
    FROM ' . $prefix . 'usr u 
    LEFT JOIN ' . $prefix . 'usr_activity_preference a ON a.usr = u.id
    WHERE u.admin = ?';

$admins  = get_records_sql_array($sql, array(1));

$users = array();
foreach ($admins as $u) {
    if (!array_key_exists($u->id, $users)) {
        $users[$u->id] = array('user' => $u,
                               'methods' => array());
    }
    $users[$u->id]['methods'][$u->activity] = $u->method;
}

$types   = get_records_array('activity_type', 'admin', 1);
$methods = plugins_installed('notification');
$options = array('none' => ucfirst(get_string('none')));
foreach ($methods as $m) {
    $options[$m->name] =  get_string('name', 'notification.' . $m->name);
}

$form = array(
    'name'       => 'adminnotifications',
    'method'     => 'post',
    'ajaxpost'   => true,
    'plugintype' => 'core', 
    'pluginname' => 'admin', 
    'renderer'   => 'multicolumntable',
    'elements'   => array()
);

// build up the header
foreach ($types as $type) {
    $form['elements']['header' . $type->name] = array(
        'title' => ' ', 
        'type'  => 'html',
        'class' => 'header',
        'value' => get_string('type' . $type->name, 'activity'),
    );        
}

foreach ($users as $id => $user) {
    foreach ($types as $type) {
        $form['elements']['admin-' . $id . '-' . $type->name] = array(
            'title'   => full_name($user['user']),
            'type'    => 'select',
            'options' => $options,
            'defaultvalue' => ((array_key_exists($type->name, $user['methods'])) 
                               ? $user['methods'][$type->name]
                               : 'none'),
        );
    }
}

$form['elements']['submit'] = array(
    'type' => 'submit',
    'value' =>get_string('save')
);


$smarty = smarty();
$smarty->assign('form', pieform($form));
$smarty->display('admin/users/notifications.tpl');

function adminnotifications_submit($values) {
    foreach ($values as $key => $value) {
        if (!preg_match('/^admin\-(\d+)\-([a-z]+)$/', $key, $m)) {
            continue;
        }
        if ($value == 'none') {
            $value = null;
        }
        set_activity_preference($m[1], $m[2], $value);
    }

    json_reply(false, get_string('notificationssaved', 'admin'));
    exit;
}

?>
