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
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');

$plugintype = param_alpha('plugintype');
$pluginname = param_alpha('pluginname');

safe_require($plugintype, $pluginname);
if ($plugintype == 'artefact') {
    $type = param_alpha('type');
    $classname = generate_artefact_class_name($type);
}
else {
    $type = null;
    $classname = generate_class_name($plugintype, $pluginname);
}

if (!call_static_method($classname, 'has_config')) {
    throw new InvalidArgumentException("$classname doesn't have config options available");
}

$form = call_static_method($classname, 'get_config_options');

if (isset($form['submitfunction'])) {
    $submitfunction = $form['submitfunction'];
}
if (isset($form['validatefunction'])) {
    $validatefunction = $form['validatefunction'];
}

$form['submitfunction'] = 'pluginconfig_submit';
$form['validatefunction'] = 'pluginconfig_validate';
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

$smarty = smarty();
$smarty->assign('form', pieform($form));
$smarty->assign('plugintype', $plugintype);
$smarty->assign('pluginname', $pluginname);
$smarty->assign('type', $type);
$smarty->display('admin/plugins/pluginconfig.tpl');


function pluginconfig_submit($values) {
    $success = false;
    global $submitfunction, $plugintype, $pluginname, $classname;
    if (!empty($submitfunction)) {
        try {
            call_static_method($classname, $submitfunction, $values);
            $success = true;
        }
        catch (Exception $e) {
            $success = false;
        }
    }
    else {
        // call set_plugin_config and stuffs
    }
    if ($success) {
        json_reply(false, get_string('settingssaved'));
    }
    else {
        json_reply('local', get_string('settingssavefailed'));
    }
}

function pluginconfig_validate(PieForm $form, $values) {
    global $validatefunction, $plugintype, $pluginname, $classname;
    if (!empty($validatefunction)) {
        call_static_method($classname, $validatefunction, $form, $values);
    }
}
?>
