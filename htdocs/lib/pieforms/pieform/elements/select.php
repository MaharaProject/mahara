<?php
/**
 * Pieforms: Advanced web forms made easy
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @package    pieform
 * @subpackage element
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Renders a dropdown list, including support for multiple choices.
 *
 * Configuration options:
 *
 * In addition to the standard pieform element configuration options, the select element
 * can take these. Each one must include an "options", "optgroups", or "optionurl"
 * parameter, because otherwise it won't have any options.
 *
 * - "options" (array): The list of items in the menu. A little confusingly, the array key
 * for each array entry is used as the "value" attribute for the <option> tag. Each array
 * value should usually be a string, in which case it will be displayed as the text between
 * the <option> tags. Or for more advanced functionality each array value can be an array
 * with these keys:
 * --- "value": The string to display inside the <option> tags
 * --- "disabled" (boolean default false): Set to true to disable this option
 * --- "style": CSS styling to set on this option
 * --- "label": sets a "label" attribute on the <option> tag
 *
 * - "optgroups" (array of arrays): Provide this INSTEAD of "options" if you want to use
 * optgroups. This should be an array of arrays. Each entry in the array represents an
 * optgroup (the key is ignored). Each optgroup array should contain an item with the
 * key "label", which will be the user-displayed label, and an item with the key "options",
 * which should be a list of options to go in that outgroup, with the same format as a
 * standard "options" array.
 *
 * - "multiple" (boolean, default false): Indicates whether to allow multiple items to be selected
 *
 * - "collapseifoneoption" (boolean, default true): If there's only one option, print it as a string
 * instead of as a menu.
 *
 * - "allowother" (boolean, default false): Says to add an "other" free text field to the menu. If there
 * is a menu option with value "other", then selecting this will display the free text
 * field. If there is no menu option with value "other", one will be added to the option
 * list.
 *
 * - "width" (deprecated): How wide to make this select element in pixels. Better to do this with CSS.
 *
 * - "height" (deprecated): How tall to make this select element in pixels. Better to do this with CSS.
 *
 * The select field can also be turned into a Select2 field via having the select2.js library loaded in page
 * and adding the following to the array:
 * - 'class' => 'js-select2'
 * - 'isSelect2' => true,
 *
 * You can also make the default read only via
 * - 'defaultvaluereadonly' => true,
 *
 * @todo Currently, putting a junk defaultvalue/value for a multiple select
 * does not trigger any kind of error, it should perhaps trigger a
 * Pieform::info
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_select(Pieform $form, $element) {

    $wrapperclass = !empty($element['isSelect2']) ? '' : 'picker';

    if (!empty($element['multiple'])) {
        $element['name'] .= '[]';
    }
    if (!empty($element['allowother']) and !isset($element['options']['other'])) {
        $element['options']['other'] = get_string('element.select.other', 'pieforms');
    }

    $optionsavailable = true;
    $options = pieform_element_select_get_options($element);
    if (empty($options)) {
        $optionsavailable = false;
        Pieform::info('Select elements should have at least one option');
    }

    if (!empty($element['collapseifoneoption']) && is_array($options) && count($options) == 1) {
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $value = $value['value'];
            }

            $result = Pieform::hsc($value) . '<input type="hidden" id="' . $form->make_id($element, true) . '" name="' . Pieform::hsc($element['name']) . '" value="' . Pieform::hsc($key) . '">';
        }
        return $result;
    }

    $result = '<span class="' . $wrapperclass . '"><select'
        . $form->element_attributes($element)
        . (!empty($element['multiple']) ? ' multiple="multiple"' : '')
        . (!empty($element['allowother']) ? ' onChange="pieform_select_other(this);"' : '')
        . (!empty($element['width']) ? ' style="width: ' . $element['width'] . 'px;' : ' style="')
        . (!empty($element['height']) ? ' height: ' . $element['height'] . 'px;"' : '"')
        . ">\n";
    if (!$optionsavailable) {
        $result .= "\t<option></option>\n</select></span>";
        return $result;
    }

    $values = $form->get_value($element);
    $optionselected = false;
    if (!empty($element['allowother'])) {
        $use_other = $values;
        foreach ($element['options'] as $key => $value) {
            if ((!is_array($values) && $key == $values) || (is_array($values) && in_array($key, $values))) {
                unset($use_other);
                break;
            }
        }
        if (isset($use_other)) {
            $values = 'other';
        }
    }

    if (empty($element['optgroups'])) {
        $result .= pieform_element_select_render_options($element['options'], $values, $optionselected, $element);
    }
    else {
        foreach ($element['optgroups'] as $optgroup) {
            $result .= "\t<optgroup label=\"" . hsc($optgroup['label']) . '">' . "\n"
                . pieform_element_select_render_options($optgroup['options'], $values, $optionselected, $element)
                . "\t</optgroup>\n";
        }
    }

    if (!$optionselected && !is_array($values) && $values !== null) {
        Pieform::info('Invalid value for select "' . $element['name'] .'"');
    }

    $result .= '</select></span>';

    if (!empty($element['allowother'])) {
        $other_attrib = array(
            'name' => $element['name'] . '_other',
            'id' => $element['id'] . '_other',
        );
        if (isset($use_other)) {
            $other_value = ' value="' . hsc($use_other) . '"';
            $other_attrib['class'] = 'text form-control secondary-textbox';
        }
        else {
            $other_attrib['class'] = 'text form-control secondary-textbox d-none';
            $other_value = '';
        }
        $result .= '<label for="' . $form->make_id($other_attrib, true) . '" class="accessible-hidden sr-only">' . get_string('licenseotherurl') . '</label>'
                . '<input type="text" '
                . $form->element_attributes($other_attrib)
                . $other_value
                . ">\n";
    }
    return $result;
}

function pieform_element_select_render_options($options, $values, &$optionselected, $element) {
    $result = '';
    foreach ($options as $key => $value) {
        // Select the element if it's in the values or if there are no values
        // and this is the first option
        $stringvalue = !is_array($values) && (string)$key === (string)$values;

        $inarrayvalue = is_array($values) && in_array($key, $values);
        $firstoption = isset($values[0]) && $values[0] === null && !$optionselected;

        if ($stringvalue || $inarrayvalue || $firstoption) {
            $selected = ' selected="selected"';
            $optionselected = true;
        }
        else {
            $selected = '';
        }

        // Disable the option if necessary
        if (is_array($value) && !empty($value['disabled'])) {
            $disabled = ' disabled="disabled"';
        }
        else {
            $disabled = '';
        }

        // Add a label if necessary. None of the common browsers actually render
        // this properly at the moment, but that may change in future.
        if (is_array($value) && isset($value['label'])) {
            $label = ' label="' . Pieform::hsc($value['label']) . '"';
        }
        else {
            $label = '';
        }

        // Add a CSS style for option
        if (is_array($value) && isset($value['style'])) {
            $style = ' style="' . Pieform::hsc($value['style']) . '"';
        }
        else {
            $style = '';
        }

        // Add a class for when the default option should not be un-selected
        $class = '';
        if (!empty($element['isSelect2']) && !empty($element['defaultvaluereadonly']) && ($stringvalue || $inarrayvalue)) {
            $class = ' class="readonly"';
        }

        // Get the value to display/put in the value attribute
        if (is_array($value)) {
            if (!isset($value['value'])) {
                Pieform::info('No value set for option "' . $key . '" of select element "' . $element['name'] . '"');
                $value = '';
            }
            else {
                $value = $value['value'];
            }
        }

        $result .= "\t<option value=\"" . Pieform::hsc($key) . "\"{$selected}{$label}{$disabled}{$style}{$class}>" . Pieform::hsc($value) . "</option>\n";
    }

    return $result;
}

function pieform_element_select_set_attributes($element) {
    if (!isset($element['collapseifoneoption'])) {
        $element['collapseifoneoption'] = true;
    }
    if (empty($element['allowother'])) {
        $element['rules']['validateoptions'] = true;
    }
    return $element;
}

function pieform_element_select_get_value(Pieform $form, $element) {
    if (empty($element['multiple'])) {
        $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
        if (isset($element['value'])) {
            $values = (array) $element['value'];
        }
        else if ($form->is_submitted() && isset($global[$element['name']])) {
            $values = (array) $global[$element['name']];
            if (!empty($element['allowother']) and $values[0] === 'other') {
                if (isset($global[$element['name'] . '_other'])) {
                    $values = (array) $global[$element['name'] . '_other'];
                }
                else {
                    $values = (array) $element['defaultvalue'];
                }
            }
        }
        else if (isset($element['defaultvalue'])) {
            $values = (array) $element['defaultvalue'];
        }
        else {
            $values = array(null);
        }

        if (count($values) != 1) {
            Pieform::info('The select element "' . $element['name'] . '" has '
                . 'more than one value, but has not been declared multiple');
        }
        return $values[0];
    }
    else {
        $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
        if (isset($element['value'])) {
            $values = (array) $element['value'];
        }
        else if ($form->is_submitted() && isset($global[$element['name']])) {
            $values = (array) $global[$element['name']];
        }
        else if (!$form->is_submitted() && isset($element['defaultvalue'])) {
            $values = (array) $element['defaultvalue'];
        }
        else {
            $values = array();
        }
    }

    return $values;
}

function pieform_element_select_get_options($element) {
    if (!isset($element['options']) && !isset($element['optgroups'])) {
        return false;
    }

    if (empty($element['optgroups'])) {
        return $element['options'];
    }

    $options = array();

    foreach ($element['optgroups'] as $optgroup) {
        $options = $options + $optgroup['options'];
    }

    return $options;
}

function pieform_element_select_get_inlinejs() {
    return <<<EOF
    function pieform_select_other(el) {
        var element = jQuery(el);
        var other = jQuery('#' + element.prop('id') + '_other');
        if (element.val() == 'other') {
            other.removeClass('d-none');
        }
        else {
            other.addClass('d-none');
        }
    }
EOF;
}

function pieform_element_select_get_headdata() {
    $result  = '<script>' . "\n";
    $result .= pieform_element_select_get_inlinejs();
    $result .= '</script>' . "\n";
    return array($result);
}
