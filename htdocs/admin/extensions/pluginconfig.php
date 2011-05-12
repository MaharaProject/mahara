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

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configextensions/pluginadmin');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('pluginadmin', 'admin'));
require_once('pieforms/pieform.php');

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
    'value' => get_string('save'),
);

$form = pieform($form);

$smarty = smarty();
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
        call_static_method($classname, 'save_config_options', $values);
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

function pluginconfig_validate(PieForm $form, $values) {
    global $plugintype, $pluginname, $classname;

    if (method_exists($classname, 'validate_config_options')) {
        call_static_method($classname, 'validate_config_options', $form, $values);
    }
}
