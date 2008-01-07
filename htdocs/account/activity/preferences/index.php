<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'settings/activitypreferences');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'activitypreferences');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once('pieforms/pieform.php');

$activitytypes = get_records_array('activity_type', 'admin', 0);
if ($USER->get('admin') || $USER->is_institutional_admin()) {
    $admintypes = get_records_array('activity_type', 'admin', 1);
    $activitytypes = array_merge($activitytypes , $admintypes);
}

$notifications = plugins_installed('notification');

$elements = array();
$options = array();
foreach ($notifications as $n) {
     $options[$n->name] = get_string('name', 'notification.' . $n->name);
}

foreach ($activitytypes as $type) {
    $dv = $USER->get_activity_preference($type->name);
    if (empty($dv)) {
        if (!empty($type->admin) && $USER->get('admin')) {
            $dv = 'none';
        } else {
            $dv = 'internal';
        }
    }
    $elements[$type->name] = array(
        'defaultvalue' => $dv,
        'type' => 'select',
        'title' => get_string('type' . $type->name, 'activity'),
        'options' => $options, 
        'rules' => array(
            'required' => true
        )
    );
    if (!empty($type->admin)) {
        $elements[$type->name]['rules']['required'] = false;
        $elements[$type->name]['options']['none'] = get_string('none');
    }

}

$elements['submit'] = array(
    'type' => 'submit',
    'value' => get_string('save'),
);


$prefsform = array(
    'name'        => 'activityprefs',
    'method'      => 'post',
    'jsform'      => true,
    'renderer'    => 'table',
    'plugintype ' => 'core',
    'pluginname'  => 'account',
    'elements'    => $elements,
);

$smarty = smarty();
$smarty->assign('prefsdescr', get_string('prefsdescr', 'activity'));
$smarty->assign('form', pieform($prefsform));
$smarty->display('account/activity/preferences/index.tpl');

function activityprefs_submit(Pieform $form, $values) {
    global $activitytypes, $admintypes, $USER;
    
    $userid = $USER->get('id');
    foreach ($activitytypes as $type) {
        if ($values[$type->name] == 'none') {
            $USER->set_activity_preference($type->name, null);
        } 
        else {
            $USER->set_activity_preference($type->name, $values[$type->name]);
        }
    }
    $form->json_reply(PIEFORM_OK, get_string('prefssaved', 'account'));
}


?>
