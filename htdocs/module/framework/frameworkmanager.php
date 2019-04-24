<?php
/**
 *
 * @package    mahara
 * @subpackage module-framework
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configextensions/frameworks');
define('SECTION_PLUGINTYPE', 'module');
define('SECTION_PLUGINNAME', 'framework');
define('SECTION_PAGE', 'frameworkmanager');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('Framework', 'module.framework'));

safe_require('module', 'framework');

$frameworkid  = param_integer('id', 0);
$framework = new Framework($frameworkid);

define('SUBSECTIONHEADING', $framework->get('name'));
$plugintype = 'module';
$pluginname = 'framework';
$classname = 'Framework';

$form = $framework->get_framework_config_options();
if (!array_key_exists('class', $form)) {
    $form['class'] = 'card card-body';
}

$form['name'] = 'frameworkconfig';
$form['frameworkconfigform'] = true;
$form['jsform'] = true;
$form['successcallback'] = 'frameworkconfig_submit';
$form['elements']['plugintype']  = array(
    'type' => 'hidden',
    'value' => $plugintype
);
$form['elements']['pluginname'] = array(
    'type' => 'hidden',
    'value' => $pluginname
);
$form['elements']['framework'] = array(
    'type' => 'hidden',
    'value' => $frameworkid
);
$form['elements']['save'] = array(
    'type'  => 'submitcancel',
    'class' => 'btn-primary',
    'value' => array(
        get_string('save'),
        get_string('cancel')
    ),
    'goto' => get_config('wwwroot') . 'module/framework/frameworks.php',
);

$form = pieform($form);

$smarty = smarty();
$smarty->assign('SUBPAGENAV', PluginModuleFramework::submenu_items('overview'));
$smarty->assign('form', $form);
$smarty->assign('plugintype', $plugintype);
$smarty->assign('pluginname', $pluginname);
$smarty->display('module:framework:frameworkmanager.tpl');


function frameworkconfig_submit(Pieform $form, $values) {
    $success = false;
    global $plugintype, $pluginname, $classname, $USER;

    if (!is_plugin_active($pluginname, $plugintype)) {
        $SESSION->add_error_msg(get_string('needtoactivate', 'module.framework'));
    }
    if (!$USER->get('admin')) {
        $SESSION->add_error_msg(get_string('accessdenied'));
    }
    try {
        $frameworkid = $values['framework'];
        $framework = new Framework($frameworkid);
        $framework->save_config_options($form, $values);
        $success = true;
    }
    catch (Exception $e) {
        $success = false;
    }

    if ($success) {
        $form->json_reply(PIEFORM_OK, get_string('settingssaved'));
    }
    else {
        $form->json_reply(PIEFORM_ERR, array('message' => get_string('settingssavefailed')));
    }
}
