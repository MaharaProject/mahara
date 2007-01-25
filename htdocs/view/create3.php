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
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'view');
require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('createviewstep3', 'view'));
require_once('template.php');

$createid = param_integer('createid');
$data = $SESSION->get('create_' . $createid);
$artefacts = param_variable('template', array());
$parsed_template = template_locate($data['template']);

function validate_artefacts(&$artefacts) {
    global $parsed_template;

    if (isset($parsed_template['parseddata'])) {
        $template_data = $parsed_template['parseddata'];
    }
    else {
        $template_data = $parsed_template['cacheddata'];
    }

    $template_fields = array();

    foreach ($template_data as $block) {
        if ($block['type'] == 'block') {
            $template_fields[$block['data']['id']] = $block['data'];
        }
    }

    foreach ($artefacts as $block => &$data) {
        if (!isset($template_fields[$block])) {
            unset($artefacts[$block]);
            next;
        }
        // @todo martyn more validation ;)

        $data['type'] = $template_fields[$block]['type'];
    }
}

if(!isset($data['artefacts'])) {
    $data['artefacts'] = array();
};


if (param_boolean('submit')) {
    validate_artefacts($artefacts);

    $data['artefacts'] = $artefacts;

    log_debug($data);

    $SESSION->set('create_' . $createid, $data);
    redirect('/view/create4.php?createid=' . $createid);
}

if (param_boolean('back')) {
    validate_artefacts($artefacts);

    $data['artefacts'] = $artefacts;

    $SESSION->set('create_' . $createid, $data);

    redirect('/view/create2.php?createid=' . $createid);
}

if (param_boolean('cancel')) {
    redirect('/view/');
}

// Get the list of root things for the tree
$rootinfo = "var data = [";
foreach (plugins_installed('artefact') as $artefacttype) {
    safe_require('artefact', $artefacttype->name);
    if ($artefacttype->active) {
        foreach (call_static_method('PluginArtefact' . ucfirst($artefacttype->name), 'get_toplevel_artefact_types') as $type) {
            $rootinfo .= json_encode(array(
                'id'         => $artefacttype->name,
                'isartefact' => false,
                'container'  => true,
                'text'       => get_string($type, "artefact.{$artefacttype->name}"),
                'pluginname' => $artefacttype->name
            )) . ',';
        }
    }
}
$rootinfo = substr($rootinfo, 0, -1) . '];';

$template = template_render($parsed_template, TEMPLATE_RENDER_EDITMODE, array_merge($data, $data['artefacts']));

$headers = array();
if (isset($parsed_template['css'])) {
    $headers[] = '<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'view/template.css.php?template=' . $data['template'] . '">';
}

$smarty = smarty(
    array('collapsabletree', 'move', 'tablerenderer'),
    $headers,
    array(
        'view' => array(
            'chooseformat',
            'format.listself',
            'format.listchildren',
            'format.renderfull',
            'format.rendermetadata',
            'empty_block',
            'empty_label',
        ),
    )
);
$smarty->assign('rootinfo', $rootinfo);
$smarty->assign('plusicon', theme_get_url('images/plus.png'));
$smarty->assign('minusicon', theme_get_url('images/minus.png'));

$smarty->assign('template', $template);

$smarty->display('view/create3.tpl');

?>
