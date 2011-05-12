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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Provides a basic checkbox input.
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_checkbox(Pieform $form, $element) {/*{{{*/
    $checked = false;
    if (isset($element['rules']['required'])){
        throw new PieformException("For pieform_element_checkbox, 'required' is not allowed as a rule. Radio should be used instead.");
    }
    if (!empty($element['value'])) {
        $checked = true;
    }
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if ($form->is_submitted() && isset($global[$element['name']])) {
        $checked = true;
    }
    else if (!$form->is_submitted() && !empty($element['defaultvalue'])) {
        $checked = true;
    }

    return '<input type="checkbox"'
        . $form->element_attributes($element)
        . ($checked ? ' checked="checked"' : '')
        . '>';
}/*}}}*/

function pieform_element_checkbox_get_value(Pieform $form, $element) {/*{{{*/
    $name = $element['name'];
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    if (isset($element['value'])) {
        return $element['value'];
    }

    if ($form->is_submitted()) {
        if(isset($global[$name])) {
            return true;
        }
        return false;
    }

    if (isset($element['defaultvalue'])) {
        return $element['defaultvalue'];
    }

    return false;
}/*}}}*/
