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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'view');
require(dirname(dirname(__FILE__)) . '/init.php');

$createid = param_integer('createid');
if (param_boolean('back')) {
    redirect(get_config('wwwroot') . 'view/create2.php?createid=' . $createid);
}

if (param_boolean('cancel')) {
    redirect(get_config('wwwroot') . 'view/');
}

$smarty = smarty(array('collapsabletree'));

$data = $SESSION->get('create_' . $createid);

// Get the list of root things for the tree
$rootinfo = "var data = [";
foreach (plugins_installed('artefact') as $artefacttype) {
    safe_require('artefact', $artefacttype->name);
    if ($artefacttype->active) {
        foreach (call_static_method('PluginArtefact' . ucfirst($artefacttype->name), 'get_toplevel_artefact_types') as $type) {
            $rootinfo .= json_encode(array(
                'id' => $artefacttype->name,
                'container' => true,
                'text' => get_string($type, "artefact.{$artefacttype->name}"),
                'pluginname' => $artefacttype->name
            )) . ',';
        }
    }
}
$rootinfo = substr($rootinfo, 0, -1) . '];';

$smarty->assign('rootinfo', $rootinfo);
$smarty->assign('plusicon', theme_get_image_path('plus.png'));
$smarty->assign('minusicon', theme_get_image_path('minus.png'));
$smarty->display('view/create3.tpl');

?>
