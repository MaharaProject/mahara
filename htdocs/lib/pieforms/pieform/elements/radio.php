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
 * Renders a set of radio buttons for a form
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_render_radio($element, Pieform $form) {
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

    foreach ($element['options'] as $value => $text) {
        $uid = $id . substr(md5(microtime()), 0, 4);
        $element['id'] = $uid;
        $result .= '<input type="radio"'
            . Pieform::element_attributes($element)
            . ' value="' . Pieform::hsc($value) . '"'
            . (($form_value == $value) ? ' checked="checked"' : '')
            . "> <label for=\"$uid\">" . Pieform::hsc($text) . "</label>$separator";
    }
    $result = substr($result, 0, -strlen($separator));
    
    return $result;
}

/**
 * radio doesn't need a function to get a value from phpland because it comes
 * through correctly from the request... however in javascript land things are
 * harder.
 *
 * @todo maybe later: make the get_value_js functions return a javascript function,
 * to keep their stuff in its own scope. Maybe. If js scoping rules mean this will help.
 */
function pieform_get_value_js_radio($element, Pieform $form) {
    $formname = $form->get_name();
    $name = $element['name'];
    return <<<EOF
    var radio = filter(function(radio) { return radio.checked; }, document.forms['$formname'].elements['$name']);
    data['$name'] = radio[0].value;

EOF;
}

function pieform_render_radio_set_attributes($element) {
    $element['nolabel'] = true;
    $element['rules']['validateoptions'] = true;
    return $element;
}

?>
