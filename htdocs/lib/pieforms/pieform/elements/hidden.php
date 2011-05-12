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
 * Renders a hidden element.
 *
 * @param Pieform $form  The form to render the element for
 * @param array $element The element to render
 * @return string        The HTML for the element
 */
function pieform_element_hidden(Pieform $form, $element) {/*{{{*/
    if (!array_key_exists('value', $element)) {
        throw new PieformException('The hidden element "' . $element['name'] . '" must have a value set');
    }
    if (!empty($element['sesskey']) && $form->get_property('method') != 'post') {
        throw new PieformException('Sesskey values should be POSTed');
    }
    $value = $form->get_value($element);
    if (is_array($value)) {
        $result = '';
        foreach ($value as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $subk => $subv) {
                    $result .= '<input type="hidden" name="' . Pieform::hsc($element['name'])
                        . '[' . Pieform::hsc($k) . '][' . Pieform::hsc($subk) . ']" value="' . Pieform::hsc($subv) . "\">\n";
                }
            }
            else {
                $result .= '<input type="hidden" name="' . Pieform::hsc($element['name'])
                    . '[' . Pieform::hsc($k) . ']" value="' . Pieform::hsc($v) . "\">\n";
            }
        }
        return $result;
    }
    return '<input type="hidden"'
        . $form->element_attributes($element, array('accesskey', 'onclick', 'size', 'style', 'tabindex'))
        . ' value="' . Pieform::hsc($form->get_value($element)) . "\">\n";
}/*}}}*/

/**
 * Returns the value for a hidden element. Hidden elements only listen to the
 * 'value' index, and not to GET/POST, unless the 'sesskey' property is set
 * on the element.
 */
function pieform_element_hidden_get_value(Pieform $form, $element) {/*{{{*/
    if (!empty($element['sesskey']) && $form->is_submitted()) {
        return isset($_POST[$element['name']]) ? $_POST[$element['name']] : null;
    }
    return $element['value'];
}/*}}}*/
