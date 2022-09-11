<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configextensions/pluginadmin');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('pluginadmin', 'admin'));

$plugintype = param_alpha('plugintype');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', $plugintype);

require_once(get_config('docroot') . $plugintype . '/lib.php');
$classname = 'Plugin' . ucfirst($plugintype);

if (!call_static_method($classname, 'has_base_config')) {
    throw new InvalidArgumentException("$classname doesn't have config options available");
}

if (method_exists($classname, 'get_base_config_options_css')) {
    $formcss = call_static_method($classname, 'get_base_config_options_css');
}
else {
    $formcss = array();
}

if (method_exists($classname, 'get_base_config_options_js')) {
    $formjs = call_static_method($classname, 'get_base_config_options_js');
}
else {
    $formjs = '';
}

$form = call_static_method($classname, 'get_base_config_options');
if (!array_key_exists('class', $form)) {
    $form['class'] = 'card card-body';
}
$form['plugintype'] = $plugintype;
$form['name'] = 'pluginconfig';
$form['pluginconfigform'] = true;
$form['jsform'] = true;
$form['successcallback'] = 'plugintypeconfig_submit';
$form['validatecallback'] = 'plugintypeconfig_validate';
$form['elements']['plugintype']  = array(
    'type' => 'hidden',
    'value' => $plugintype
);
$form['elements']['save'] = array(
    'type'  => 'submit',
    'class' => 'btn-primary',
    'value' => get_string('save'),
);

$form = pieform($form);

$smarty = smarty(array('js/jquery/jquery-ui/js/jquery-ui.min.js','js/jquery/jquery-ui/js/jquery-ui.touch-punch.min.js'), $formcss);
$smarty->assign('form', $form);
$smarty->assign('plugintype', $plugintype);
$smarty->assign('plugintypedescription', (string_exists('plugintypedescription_' . $plugintype, 'admin') ? get_string('plugintypedescription_' . $plugintype, 'admin') : false));
$heading = get_string('pluginadmin', 'admin') . ': ' . $plugintype;
$smarty->assign('PAGEHEADING', $heading);
$smarty->assign('INLINEJAVASCRIPT', $formjs);
$smarty->display('admin/extensions/pluginconfig.tpl');


function plugintypeconfig_submit(Pieform $form, $values) {
    global $plugintype, $classname;

    $success = true;

    if (is_callable($classname . '::save_base_config_options')) {
        $success = false;
        try {
            call_static_method($classname, 'save_base_config_options', $form, $values);
            $success = true;
        }
        catch (Exception $e) {
            $success = false;
        }
    }

    if ($success) {
        $form->json_reply(PIEFORM_OK, get_string('settingssaved'));
    }
    else {
        $form->json_reply(PIEFORM_ERR, array('message' => get_string('settingssavefailed')));
    }
}

function plugintypeconfig_validate(PieForm $form, $values) {
    global $plugintype, $classname;
    if (is_callable($classname . '::validate_base_config_options')) {
        call_static_method($classname, 'validate_base_config_options', $form, $values);
    }
}
