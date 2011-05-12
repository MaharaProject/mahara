<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @package    mahara
 * @subpackage form-element
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Provides a control that allows the user to input where in a list they want 
 * to put something. Handy for indicating the order of some objects.
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_weight(Pieform $form, $element) {
    pieform_element_weight_validate_element($element);

    $default = (isset($element['defaultvalue'])) ? intval($element['defaultvalue']) : 0;
    $result = '<input type="radio"'. $form->element_attributes($element) . ' value="0"';
    if ($default == 0) {
        $result .= ' checked="checked"';
    }
    $result .= '>';

    $i = 0;
    foreach ($element['existing'] as $existing) {
        $i++;
        $result .= "<br>" . Pieform::hsc($existing['title']);
        $result .= "<br><input type=\"radio\"" . $form->element_attributes($element) . " value=\"$i\"";
        if ($i == $default) {
            $result .= ' checked="checked"';
        }
        $result .= '>';
    }

    return $result;

    return '<input type="text"'
        . $form->element_attributes($element)
        . ' value="' . Pieform::hsc($form->get_value($element)) . '">';
}

function pieform_element_weight_set_attributes($element) {
    $element['returnpolicy'] = 'renumber-from-zero';
    return $element;
}

function pieform_element_weight_get_value(Pieform $form, $element) {
    pieform_element_weight_validate_element($element);
    $name = $element['name'];

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    $value = null;
    if (isset($element['value'])) {
        $value = $element['value'];
    }
    else if (isset($global[$name])) {
        $value = $global[$name];
    }
    else if (isset($element['defaultvalue'])) {
        $value = $element['defaultvalue'];
    }

    // How should we return the value? In theory, there could be several ways 
    // in which the caller wants the data to be returned. For now, only one 
    // "policy" is implemented
    switch ($element['returnpolicy']) {
    case 'renumber-from-zero':
        return pieform_element_weight_returnpolicy_renumber_from_zero($element, $value);
    }

    return null;
}

function pieform_element_weight_validate_element($element) {
    if (!isset($element['existing'])) {
        throw new PieformException('weight element requires "existing" data');
    }

    if (!is_array($element['existing'])) {
        throw new PieformException('"existing" data should be an array of hashes, each with keys "id", "weight" and "title"');
    }

    foreach ($element['existing'] as $existing) {
        if (!array_key_exists('id', $existing) || !array_key_exists('weight', $existing) || !array_key_exists('title', $existing)) {
            throw new PieformException('Elements of the "existing" data should have "id", "weight" and "title" keys');
        }
    }
}

/**
 * Returns an array of weight => id pairs.
 *
 * The new element has an id of 'null'
 */
function pieform_element_weight_returnpolicy_renumber_from_zero($element, $value) {
    $element['existing'] = pieform_element_weight_renumber_existing_from_zero($element['existing']);

    $element['existing'][] = array('weight' => $value - 0.5, 'id' => null);
    usort($element['existing'], create_function('$a, $b', 'return $a["weight"] > $b["weight"];'));

    $return = array();
    foreach ($element['existing'] as $existing) {
        $return[] = $existing['id'];
    }
    return $return;
}

function pieform_element_weight_renumber_existing_from_zero($existing) {
    usort($existing, create_function('$a, $b', 'return $a["weight"] > $b["weight"];'));

    $i = 0;
    foreach ($existing as &$item) {
        $item['weight'] = $i++;
    }
    return $existing;
}
