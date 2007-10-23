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

$pagination_js = '';
/**
 * Provides a mechanism for choosing one or more artefacts from a list of them.
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_artefactchooser(Pieform $form, $element) {
    global $USER, $pagination_js;

    $value = $form->get_value($element);

    // internal configuration
    $offsetname = $form->get_name() . '_' . $element['name'] . '_o';
    $offset = param_integer($offsetname, 0);

    list($html, $pagination, $count) = View::build_artefactchooser_data($element['artefacttypes'], $offset, $offsetname, $element['limit'], $element['selectone'], $value, $element['name']);
    $smarty = smarty_core();
    $smarty->assign('datatable', $element['name'] . '_data');
    $smarty->assign('artefacts', $html);
    $smarty->assign('pagination', $pagination['html']);

    // Save the pagination javascript for later, when it is asked for. This is 
    // messy, but can't be helped until Pieforms goes to a more OO way of 
    // managing stuff.
    $pagination_js = $pagination['js'];

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

/**
 * Extension by Mahara. This api function returns the javascript required to 
 * set up the element, assuming the element has been placed in the page using 
 * javascript. This feature is used in the views interface.
 *
 * In theory, this could go upstream to pieforms itself
 *
 * @param Pieform $form     The form
 * @param array   $element  The element
 */
function pieform_element_artefactchooser_views_js(Pieform $form, $element) {
    global $pagination_js;
    return $pagination_js;
}

?>
