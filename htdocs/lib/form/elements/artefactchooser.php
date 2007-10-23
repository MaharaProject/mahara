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
 * @subpackage form-element
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Provides a mechanism for choosing one or more artefacts from a list of them.
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_artefactchooser(Pieform $form, $element) {
    global $USER;

    $smarty = smarty_core();
    $value = $form->get_value($element);

    // internal configuration
    $offsetname = $form->get_name() . '_' . $element['name'] . '_o';
    $offset = param_integer($offsetname, 0);

    $select = 'owner = ' . $USER->get('id');
    if (!empty($element['artefacttypes'])) {
        $select .= ' AND artefacttype IN(' . implode(',', array_map('db_quote', $element['artefacttypes'])) . ')';
    }
    $artefacts = get_records_select_array('artefact', $select, null, 'title', '*', $offset, $element['limit']);
    $totalartefacts = count_records_select('artefact', $select);

    foreach ($artefacts as &$artefact) {
        safe_require('artefact', get_field('artefact_installed_type', 'plugin', 'name', $artefact->artefacttype));
        $artefact->icon = call_static_method(generate_artefact_class_name($artefact->artefacttype), 'get_icon', $artefact->id);
        $artefact->hovertitle =  ($artefact->artefacttype == 'profileicon') ? $artefact->note : $artefact->title;
        $artefact->description = ($artefact->artefacttype == 'profileicon') ? $artefact->title : $artefact->description;
    }

    $smarty->assign('elementname', $element['name']);
    $smarty->assign('artefacts', $artefacts);
    $smarty->assign('count', $totalartefacts);
    //$smarty->assign('limit', $element['limit']);
    //$smarty->assign('offset', $offset);
    //$smarty->assign('offsetname', $offsetname);
    $smarty->assign('selectone', $element['selectone']);
    $smarty->assign('value', $value);
    $smarty->assign('datatable', $element['name'] . '_data');
    $baseurl = View::make_base_url();
    $pagination = build_pagination(array(
        'id' => $element['name'] . '_pagination',
        'url' => View::make_base_url(),
        'count' => $totalartefacts,
        'limit' => $element['limit'],
        'offset' => $offset,
        'offsetname' => $offsetname,
        'datatable' => $element['name'] . '_data',
        'jsonscript' => 'view/artefactchooser.json.php',
        'firsttext' => '',
        'previoustext' => '',
        'nexttext' => '',
        'lasttext' => '',
        'numbersincludefirstlast' => false,
    ));
    $smarty->assign('pagination', $pagination['html']);

    return $smarty->fetch('form/artefactchooser.tpl');
}

function pieform_element_artefactchooser_get_value(Pieform $form, $element) {
    $name = $element['name'];

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    if (isset($global[$name])) {
        $value = $global[$name];

        if ($value == '') {
            return ($element['selectone']) ? null : array();
        }

        if (preg_match('/^(\d+(,\d+)*)$/',$value)) {
            return ($element['selectone']) ? intval($value) : array_map('intval', explode(',', $value));
        }

        throw new PieformException("Invalid value for artefactchooser form element '$name' = '$value'");
    }

    if (isset($element['defaultvalue'])) {
        return $element['defaultvalue'];
    }

    return null;
}

//function pieform_element_artefactchooser_rule_required(Pieform $form, $value, $element) {
//    if (is_array($value) && count($value)) {
//        return null;
//    }
//
//    return $form->i18n('rule', 'required', 'required', $element);
//}

function pieform_element_artefactchooser_set_attributes($element) {
    if (!isset($element['selectone'])) {
        $element['selectone'] = true;
    }
    if (!isset($element['limit'])) {
        $element['limit'] = 10;
    }
    return $element;
}

/*
function pieform_element_artefactchooser_get_headdata() {
    return array('paginator');
}
*/
/**
 * Extension by Mahara. This api function returns the javascript required to set up the element, assuming the element has been placed in the page using javascript. This feature is used in the views interface.
 *
 * In theory, this could go upstream to pieforms itself
 *
 * @param Pieform $form     The form
 * @param array   $element  The element
 * @param string  $key      The name of the element (might be available as $element['name'], should check that)
 */
function pieform_element_artefactchooser_views_js(Pieform $form, $element) {
    log_debug('pieform_element_artefactchooser_views_js');
    log_debug($element);
    return 'new Paginator(' . json_encode($element['name'] . '_pagination') . ', ' . json_encode($element['name'] . '_data') . ', "view/artefactchooser.json.php");';
}

?>
