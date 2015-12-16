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
 * Renders an <input type="button"> element.
 *
 * The element must have the 'value' field set.
 *
 * @param Pieform $form    The form to render the element for
 * @param array   $element The element to render
 * @return string          The HTML for the element
 * @todo rename to inputbutton
 */
function pieform_element_button(Pieform $form, $element) {
    if (!isset($element['value'])) {
        throw new PieformException('Button elements must have a value');
    }

    if (isset($element['confirm'])) {
        $element['data-confirm'] = Pieform::hsc($element['confirm']);
    }

    $element['class'] .= ' btn';

    if (isset($element['usebuttontag']) && $element['usebuttontag'] === true) {
        $value = '';
        $action = '';
        $type = 'type="submit" ';

        if (isset($element['content'])) {
            $content = $element['content'];
            $value = 'value="'. Pieform::hsc($element['value']) . '" ';
        } else {
            $content = $element['value'];
        }

        if (isset($element['action'])) {
            $action = 'formaction="' . Pieform::hsc($element['action']) . '" ';
        }

        $button = '<button '
        . $value . $action . $type
        . $form->element_attributes($element)
        . '>'
        .  $content
        . '</button>';

    } else {
        $button = '<input type="button"'
        . $form->element_attributes($element)
        . ' value="' . Pieform::hsc($element['value']) . '">';
    }

    return $button;
}

function pieform_element_button_set_attributes($element) {
    if (isset($element['usebuttontag']) && $element['usebuttontag']) {
        $element['submitelement'] = true;
    }
    return $element;
}
