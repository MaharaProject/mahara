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
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'account');
define('SUBMENUITEM', 'activityprefs');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once('pieforms/pieform.php');

$activitytypes = get_records('activity_type', 'admin', 0);
$notifications = plugins_installed('notification');

$elements = array();

foreach ($activitytypes as $type) {
    if (!$dv = $SESSION->get_activity_preference($type->name)) {
        $dv = 'internal';
    }
    $elements[$type->name] = array(
        'defaultvalue' => $dv,
        'type' => 'select',
        'title' => get_string('type' . $type->name, 'activity'),
        'options' => array(),
        'rules' => array(
            'required' => true
        )
    );

    foreach ($notifications as $n) {
         $elements[$type->name]['options'][$n->name] = get_string('name', 'notification.' . $n->name);
    }
}

$elements['submit'] = array(
    'type' => 'submit',
    'value' => get_string('save'),
);


$prefsform = array(
    'name'        => 'activityprefs',
    'method'      => 'post',
    'ajaxpost'    => true,
    'plugintype ' => 'core',
    'pluginname'  => 'account',
    'elements'    => $elements,
);

$smarty = smarty();
$smarty->assign('prefsdescr', get_string('prefsdescr', 'activity'));
$smarty->assign('form', pieform($prefsform));
$smarty->display('account/activity/preferences/index.tpl');

function activityprefs_submit($values) {
    global $activitytypes, $SESSION;
    
    $userid = $SESSION->get('id');
    foreach ($activitytypes as $type) {
        $SESSION->set_activity_preference($type->name, $values[$type->name]);
    }
    json_reply(false, get_string('prefssaved', 'account'));
    exit;
}


?>
