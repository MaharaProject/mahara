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
 * Provides a basic checkbox input.
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_render_checkbox($element, Pieform $form) {
    $checked = false;
    // @todo use of 'value' and 'checked' here is ambiguous, need to write
    // test cases and pick just one of them
    if (!empty($element['value'])) {
        $checked = true;
    }
    if ($form->get_value($element)) {
        $checked = true;
    }
    else if (!$form->is_submitted() && !empty($element['checked'])) {
        $checked = true;
    }

    return '<input type="checkbox"'
        . Pieform::element_attributes($element)
        . ($checked ? ' checked="checked"' : '')
        . '>';
}

function pieform_get_value_js_checkbox($element, Pieform $form) {
    $formname = $form->get_name();
    $name = $element['name'];
    return <<<EOF
    if (document.forms['$formname'].elements['$name'].checked) {
        data['$name'] = 'on';
    }

EOF;
}


?>
