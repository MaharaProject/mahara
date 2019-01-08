<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configextensions/pluginadmin');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('pluginadmin', 'admin'));

$plugintype = param_alpha('plugintype');
$pluginname = param_variable('pluginname');

define('SECTION_PLUGINTYPE', $plugintype);
define('SECTION_PLUGINNAME', $pluginname);
define('SECTION_PAGE', 'pluginconfig');

safe_require($plugintype, $pluginname);
$enable  = param_integer('enable', 0);
$disable = param_integer('disable', 0);

if ($disable && !call_static_method(generate_class_name($plugintype, $pluginname), 'can_be_disabled')) {
    throw new UserException("Plugin $plugintype $pluginname cannot be disabled");
}

if ($enable || $disable) {
    require_once(get_config('libroot') . 'upgrade.php');
    clear_menu_cache();
    activate_plugin_form($plugintype, get_record($plugintype . '_installed', 'name', $pluginname));
}

if ($plugintype == 'artefact') {
    $type = param_alpha('type');
    $classname = generate_artefact_class_name($type);
}
else {
    $type = '';
    $classname = generate_class_name($plugintype, $pluginname);
}

if (!call_static_method($classname, 'has_config')) {
    throw new InvalidArgumentException("$classname doesn't have config options available");
}

$form = call_static_method($classname, 'get_config_options');
if (method_exists($classname, 'get_config_options_css')) {
    $formcss = call_static_method($classname, 'get_config_options_css');
}
else {
    $formcss = array();
}

if (!array_key_exists('class', $form)) {
    $form['class'] = 'card card-body';
}
$form['plugintype'] = $plugintype;
$form['pluginname'] = $pluginname;
$form['name'] = 'pluginconfig';
$form['pluginconfigform'] = true;
$form['jsform'] = true;
$form['successcallback'] = 'pluginconfig_submit';
$form['validatecallback'] = 'pluginconfig_validate';
$form['elements']['plugintype']  = array(
    'type' => 'hidden',
    'value' => $plugintype
);
$form['elements']['pluginname'] = array(
    'type' => 'hidden',
    'value' => $pluginname
);
$form['elements']['type'] = array(
    'type' => 'hidden',
    'value' => $type
);
$form['elements']['save'] = array(
    'type'  => 'submit',
    'class' => 'btn-primary',
    'value' => get_string('save'),
);

$form = pieform($form);

$smarty = smarty(array(), $formcss);
$smarty->assign('form', $form);
$smarty->assign('plugintype', $plugintype);
$smarty->assign('pluginname', $pluginname);
$smarty->assign('type', $type);
$heading = get_string('pluginadmin', 'admin') . ': ' . $plugintype . ': ' . $pluginname;
if ($type) {
    $heading .= ': ' . $type;
}
$smarty->assign('PAGEHEADING', $heading);
$smarty->display('admin/extensions/pluginconfig.tpl');


function pluginconfig_submit(Pieform $form, $values) {
    $success = false;
    global $plugintype, $pluginname, $classname;

    try {
        call_static_method($classname, 'save_config_options', $form, $values);
        $success = true;
    }
    catch (Exception $e) {
        $success = false;
    }

    if ($success) {
        clear_menu_cache();
        $form->json_reply(PIEFORM_OK, get_string('settingssaved'));
    }
    else {
        $form->json_reply(PIEFORM_ERR, array('message' => get_string('settingssavefailed')));
    }
}

function pluginconfig_validate(PieForm $form, $values) {
    global $plugintype, $pluginname, $classname;
    if (is_callable($classname . '::validate_config_options')) {
        call_static_method($classname, 'validate_config_options', $form, $values);
    }
}
