<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'settings/notifications');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'notifications');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
define('TITLE', get_string('notifications'));
require_once('pieforms/pieform.php');
require_once(get_config('libroot') . 'activity.php');

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
    $dv = $USER->get_activity_preference($type->id);
    if (empty($dv)) {
        $dv = call_static_method(generate_activity_class_name($type->name, $type->plugintype, $type->pluginname), 'default_notification_method');
    }
    if (!empty($type->plugintype)) {
        $section = $type->plugintype . '.' . $type->pluginname;
    } 
    else {
        $section = 'activity';
    }
    if ($dv == 'email' && !isset($maildisabledmsg) && get_account_preference($USER->get('id'),'maildisabled')) {
        $SESSION->add_error_msg(get_string('maildisableddescription', 'account', get_config('wwwroot') . 'account/index.php'), false);
        $maildisabledmsg = true;
    }
    $elements['activity_'.$type->id] = array(
        'defaultvalue' => $dv,
        'type' => 'select',
        'title' => get_string('type' . $type->name, $section),
        'options' => $options, 
        'rules' => array(
            'required' => true
        )
    );
    if (!empty($type->admin)) {
        $elements['activity_'.$type->id]['rules']['required'] = false;
        $elements['activity_'.$type->id]['options']['none'] = get_string('none');
    }

}

$elements['submit'] = array(
    'type' => 'submit',
    'value' => get_string('save'),
);


$prefsform = pieform(array(
    'name'        => 'activityprefs',
    'method'      => 'post',
    'jsform'      => true,
    'renderer'    => 'table',
    'plugintype ' => 'core',
    'pluginname'  => 'account',
    'elements'    => $elements,
));

$smarty = smarty();
$smarty->assign('pagedescription', get_string('prefsdescr', 'activity'));
$smarty->assign('form', $prefsform);
$smarty->assign('PAGEHEADING', get_config('dropdownmenu') ? get_string('settings') : TITLE);
$smarty->display('form.tpl');

function activityprefs_submit(Pieform $form, $values) {
    global $activitytypes, $admintypes, $USER;
    
    $userid = $USER->get('id');
    foreach ($activitytypes as $type) {
        if ($values['activity_'.$type->id] == 'none') {
            $USER->set_activity_preference($type->id, null);
        } 
        else {
            $USER->set_activity_preference($type->id, $values['activity_'.$type->id]);
        }
    }
    $form->json_reply(PIEFORM_OK, get_string('prefssaved', 'account'));
}
