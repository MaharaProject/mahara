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
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require('view.php');

// TODO how should this script work this out?
$artefacttypes = array('image', 'profileicon'); // can be passed to the js
$offset = param_integer('cb_2_artefactid_o', 0); // already have the id of the form so this isn't too bad
$limit = 3; // can be passed to the js

// TODO stolen from lib/form/elements/artefactchooser.php, should be in one place
$select = 'owner = ' . $USER->get('id');
if (!empty($artefacttypes)) {
    $select .= ' AND artefacttype IN(' . implode(',', array_map('db_quote', $artefacttypes)) . ')';
}
$artefacts = get_records_select_array('artefact', $select, null, 'title', '*', $offset, $limit);
$totalartefacts = count_records_select('artefact', $select);

$result = '';
foreach ($artefacts as &$artefact) {
    safe_require('artefact', get_field('artefact_installed_type', 'plugin', 'name', $artefact->artefacttype));
    $artefact->icon = call_static_method(generate_artefact_class_name($artefact->artefacttype), 'get_icon', $artefact->id);
    $artefact->hovertitle =  ($artefact->artefacttype == 'profileicon') ? $artefact->note : $artefact->title;
    $artefact->description = ($artefact->artefacttype == 'profileicon') ? $artefact->title : $artefact->description;

    $smarty = smarty_core();
    $smarty->assign('artefact', $artefact);
    $result .= $smarty->fetch('form/artefactchooser-element.tpl') . "\n";
}

// How to get this??
$pagination = build_pagination(array(
    'id' => 'artefactid_pagination',
    'url' => View::make_base_url(),
    'count' => $totalartefacts,
    'limit' => $limit,
    'offset' => $offset,
    'offsetname' => 'cb_2_artefactid_o',
    'datatable' => 'artefactid_data',
    'jsonscript' => 'view/artefactchooser.json.php',
    'firsttext' => '',
    'previoustext' => '',
    'nexttext' => '',
    'lasttext' => '',
    'numbersincludefirstlast' => false,
));

json_reply(false, array('message' => null, 'data' => array('tablerows' => $result, 'pagination' => $pagination['html'], 'pagination_js' => $pagination['js'])));


//$view = new View(param_integer('id'));
//$change = param_boolean('change', false);
//$action = param_alphanumext('action', '');
//
//// we actually ned to process stuff
//if ($change) {
//    try {
//        $returndata = $view->process_changes();
//        json_reply(false, $returndata);
//    }
//    catch (Exception $e) {
//        json_reply(true, $e->getMessage());
//    }
//}
//// else we're just reading data...
//switch ($action) {
//case 'blocktype_list':
//    $category = param_alpha('c');
//    $data = View::build_blocktype_list($category, true);
//    json_reply(false, array('message' => false, 'data' => $data));
//    break;
//}
//
//json_reply(true, get_string('noviewcontrolaction', 'error', $action));

?>
