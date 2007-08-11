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
define('MENUITEM', 'myportfolio/views');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'edit');

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('editview', 'view'));
require_once('template.php');

$view_id = param_integer('viewid');
$artefacts = param_variable('template', array());

$view_data = get_record( 'view', 'id', $view_id, 'owner', $USER->get('id'));

if(!$view_data) {
    $SESSION->add_error_msg(get_string('canteditdontown', 'view'));
    redirect('/view/');
}

$data = array(
    'template'    => $view_data->template,
    'title'       => $view_data->title,
    'description' => $view_data->description,
    'ownerformat' => $view_data->ownerformat,
    'artefacts'   => array(),
);

$view_content = get_records_array('view_content', 'view', $view_id);
if ($view_content) {
    foreach ($view_content as &$label) {
        $data['artefacts'][$label->block] = array(
            'value' => $label->content,
        );
    }
}

$view_artefact = get_records_array('view_artefact', 'view', $view_id);
if ($view_artefact) {
    foreach ($view_artefact as &$artefact) {
        if (isset($data['artefacts'][$artefact->block])) {
            if (!is_array($data['artefacts'][$artefact->block]['id'])) {
                $data['artefacts'][$artefact->block]['id'] = array($data['artefacts'][$artefact->block]['id']);
            }
            $data['artefacts'][$artefact->block]['id'][] = $artefact->artefact;
        }
        else {
            $data['artefacts'][$artefact->block] = array(
                'id'     => $artefact->artefact,
                'format' => $artefact->format,
            );
        }
    }
}

// @todo load artefacts

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

if (param_boolean('submit')) {
    validate_artefacts($artefacts);

    $data['artefacts'] = $artefacts;

    db_begin();

    delete_records('view_content', 'view', $view_id);
    delete_records('view_artefact', 'view', $view_id);

    $time = db_format_timestamp(time());

    foreach ($data['artefacts'] as $block => $blockdata) {
        if ($blockdata['type'] == 'label') {
            $viewcontent          = new StdClass;
            $viewcontent->view    = $view_id;
            $viewcontent->content = $blockdata['value'];
            $viewcontent->block   = $block;
            $viewcontent->ctime   = $time;
            insert_record('view_content', $viewcontent);
        }
        else if ($blockdata['type'] == 'artefact') {
            $blockdata['id'] = (array)$blockdata['id'];
            foreach ($blockdata['id'] as $id) {
                $viewartefact           = new StdClass;
                $viewartefact->view     = $view_id;
                $viewartefact->artefact = $id;
                $viewartefact->block    = $block;
                $viewartefact->ctime    = $time;
                $viewartefact->format   = $blockdata['format'];
                insert_record('view_artefact', $viewartefact);
            }
        }
        else {
            throw new MaharaException('Unknown block data type, this simply should _not_ happen. Perhaps someone changed step3 and forgot to change this?');
        }
    }

    db_commit();
    activity_occurred('watchlist', (object) array('view' => $view_id,
                                                  'subject' => get_string('viewmodified')));

    handle_event('saveview', $view_id);

    

    $SESSION->add_ok_msg(get_string('viewinformationsaved', 'view'));
    redirect('/view/');
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

$template = template_render($parsed_template, TEMPLATE_RENDER_EDITMODE, array_merge($data, $data['artefacts']), $view_id);

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

$smarty->assign('EDITMODE', true);
$smarty->assign('viewid', $view_id);
$smarty->display('view/create3.tpl');

?>
