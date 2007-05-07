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
define('MENUITEM', 'configextensions');
define('SUBMENUITEM', 'pluginadmin');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('pluginadmin', 'admin'));
require_once('pieforms/pieform.php');

$plugintype = param_alpha('plugintype');
$pluginname = param_alpha('pluginname');

define('SECTION_PLUGINTYPE', $plugintype);
define('SECTION_PLUGINNAME', $pluginname);
define('SECTION_PAGE', 'pluginconfig');

safe_require($plugintype, $pluginname);
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

$smarty = smarty();
$smarty->assign('form', pieform($form));
$smarty->assign('plugintype', $plugintype);
$smarty->assign('pluginname', $pluginname);
$smarty->assign('type', $type);
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
?>
