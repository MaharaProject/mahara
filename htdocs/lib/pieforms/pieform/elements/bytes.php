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
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Provides a size chooser, with a text box for a number and a
 * select box to choose the units, in bytes, kilobytes, megabytes or gigabytes
 *
 * @param Pieform $form    The form to render the element for
 * @param array   $element The element to render
 * @return string          The HTML for the element
 */
function pieform_element_bytes(Pieform $form, $element) {/*{{{*/
    $formname = $form->get_name();
    $result = '';
    $name = Pieform::hsc($element['name']);
    if (!isset($element['defaultvalue'])) {
        $element['defaultvalue'] = null;
    }

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    // Get the value of the element for rendering.
    if (isset($element['value'])) {
        $bytes = $element['value'];
        $values = pieform_element_bytes_get_bytes_from_bytes($element['value']);
    }
    else if ($form->is_submitted()
             && isset($global[$element['name']])
             && isset($global[$element['name'] . '_units'])) {
        $values = array('number' => $global[$element['name']],
                        'units'  => $global[$element['name'] . '_units']);
        $bytes = $values['number'] * pieform_element_bytes_in($values['units']);
    }
    else if (isset($element['defaultvalue'])) {
        $bytes = $element['defaultvalue'];
        $values = pieform_element_bytes_get_bytes_from_bytes($bytes);
    }
    else {
        $values = array('number' => '0', 'units' => 'bytes');
        $bytes = 0;
    }

    // @todo probably create with an actual input element, as tabindex doesn't work here for one thing
    // Same with the select. And do the events using mochikit signal instead of dom events
    $numberinput = '<input';
    $numberinput .= ' type="text" size="6" name="' . $name . '"';
    $numberinput .= ' id="' . $formname . '_' . $name . '" value="' . Pieform::hsc($values['number']) . '" tabindex="' . Pieform::hsc($element['tabindex']) . '"';
    $numberinput .= (isset($element['error']) ? ' class="error"' : '') . ">\n";

    $uselect = '<select name="' . $name . '_units" id="' . $formname . '_' . $name . '_units"' . ' tabindex="' . Pieform::hsc($element['tabindex']) . "\">\n";
    foreach (pieform_element_bytes_get_bytes_units() as $u) {
        $uselect .= "\t<option value=\"$u\"" . (($values['units'] == $u) ? ' selected="selected"' : '') . '>'
            . $form->i18n('element', 'bytes', $u, $element) . "</option>\n";
    }
    $uselect .= "</select>\n";

    return $numberinput . $uselect;
}/*}}}*/

/**
 * Gets the value of the expiry element and converts it to a time in seconds.
 *
 * @param Pieform $form    The form the element is attached to
 * @param array   $element The element to get the value for
 * @return int             The number of seconds until expiry
 */
function pieform_element_bytes_get_value(Pieform $form, $element) {/*{{{*/
    $name = $element['name'];

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    $unit = $global[$name . '_units'];
    $allunits = pieform_element_bytes_get_bytes_units();
    $number = $global[$name];

    if (!is_numeric($number)) {
        $form->set_error($name, $form->i18n('element', 'bytes', 'invalidvalue', $element));
    }

    if (!in_array($unit,$allunits) || $number < 0) {
        return null;
    }
    return $number * pieform_element_bytes_in($unit);
}/*}}}*/

function pieform_element_bytes_in($units) {/*{{{*/
    switch ($units) {
        case 'gigabytes':
            return 1073741824;
            break;
        case 'megabytes':
            return 1048576;
            break;
        case 'kilobytes':
            return 1024;
            break;
        default:
            return 1;
            break;
    };
}/*}}}*/

function pieform_element_bytes_get_bytes_units() {/*{{{*/
    return array('bytes', 'kilobytes', 'megabytes', 'gigabytes');
}/*}}}*/

function pieform_element_bytes_get_bytes_from_bytes($bytes) {/*{{{*/
    if ($bytes == null) {
        return array('number' => '0', 'units' => 'bytes');
    }

    foreach (array('gigabytes', 'megabytes', 'kilobytes') as $units) {
        if ( $bytes >= pieform_element_bytes_in($units) ) {
            return array('number' => $bytes / pieform_element_bytes_in($units) , 'units' => $units);
        }
    }

    return array('number' => $bytes, 'units' => 'bytes');
}/*}}}*/
