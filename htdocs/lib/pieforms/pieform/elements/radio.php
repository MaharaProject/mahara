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
 * Renders a set of radio buttons for a form
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_element_radio(Pieform $form, $element) {/*{{{*/
    if (!isset($element['options']) || !is_array($element['options']) || count($element['options']) < 1) {
        throw new PieformException('Radio elements should have at least one option');
    }
    
    $result = '';
    $form_value = $form->get_value($element);
    $id = $element['id'];

    $separator = "\n";
    if (isset($element['separator'])) {
        $separator = $element['separator'] . $separator;
    }

    foreach ($element['options'] as $value => $data) {
        $uid = $id . substr(md5(microtime()), 0, 4);
        $element['id'] = $uid;
        if (is_array($data)) {
            $text = $data['text'];
            $description = (isset($data['description'])) ? $data['description'] : '';
        }
        else {
            $text = $data;
            $description = '';
        }
        $result .= '<input type="radio"'
            . $form->element_attributes($element)
            . ' value="' . Pieform::hsc($value) . '"'
            . (($form_value == $value) ? ' checked="checked"' : '')
            . '> <label for="' . $form->get_name() . '_' . $uid . '">' . Pieform::hsc($text) . "</label>"
            . ($description != '' ? '<div class="radio-description">' . $description . '</div>' : '')
            . $separator;
    }
    $result = substr($result, 0, -strlen($separator));
    
    return $result;
}/*}}}*/

function pieform_element_radio_set_attributes($element) {/*{{{*/
    $element['rules']['validateoptions'] = true;
    return $element;
}/*}}}*/
