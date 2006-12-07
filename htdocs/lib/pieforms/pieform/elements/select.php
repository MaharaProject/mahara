<?php
/**
 * This program is part of Pieforms
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
 * @package    pieform
 * @subpackage element
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Renders a dropdown list, including support for multiple choices.
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_render_select($element, Pieform $form) {
    if (!empty($element['multiple'])) {
        $element['name'] .= '[]';
    }

    if (!empty($element['collapseifoneoption']) && count($element['options']) == 1) {
        foreach ($element['options'] as $key => $value) {
            $result = $value . '<input type="hidden" name="' . $element['name'] . '" value="' . $key . '">';
        }
        return $result;
    }

    $result = '<select'
        . $form->element_attributes($element)
        . (!empty($element['multiple']) ? ' multiple="multiple"' : '')
        . ">\n";
    if (!isset($element['options']) || !is_array($element['options']) || count($element['options']) < 1) {
        $result .= "\t<option></option>\n";
        log_warn('Select elements should have at least one option');
    }

    if (empty($element['multiple'])) {
        $values = array($form->get_value($element)); 
    }
    else {
        if (isset($element['value'])) {
            $values = (array) $element['value'];
        }
        else if (isset($_POST[$element['name']])) {
            $values = (array) $_POST[$element['name']];
        }
        else if (isset($element['defaultvalue'])) {
            $values = (array) $element['defaultvalue'];
        }
        else {
            $values = array();
        }
    }
    foreach ($element['options'] as $key => $value) {
        if (in_array($key, $values)) {
            $selected = ' selected="selected"';
        }
        else {
            $selected = '';
        }
        $result .= "\t<option value=\"" . Pieform::hsc($key) . "\"$selected>" . Pieform::hsc($value) . "</option>\n";
    }

    $result .= '</select>';
    return $result;
}

function pieform_get_value_js_select($element, Pieform $form) {
    $formname = $form->get_name();
    $name = $element['name'];
    if ($element['collapseifoneoption']) {
        return "    data['$name'] = document.forms['$formname'].elements['$name'].value;\n";
    }
    return <<<EOF
    var select = filter(function(option) { return option.selected; }, document.forms['$formname'].elements['$name'].options);
    data['$name'] = map(function(o) { return o.value; }, select);

EOF;
}

function pieform_render_select_set_attributes($element) {
    $element['collapseifoneoption'] = true;
    $element['rules']['validateoptions'] = true;
    return $element;
}

?>
