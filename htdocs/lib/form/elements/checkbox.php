<?php
/**
 * This program is part of Mahara
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
 * @package    mahara
 * @subpackage form-element
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Provides a basic checkbox input.
 *
 * @param array $element The element to render
 * @param Form  $form    The form to render the element for
 * @return string        The HTML for the element
 */
function form_render_checkbox($element, Form $form) {
    $checked = false;
    if ($form->is_submitted()) {
        if (!empty($element['value'])) {
            $checked = true;
        }
        if ($form->get_value($element)) {
            $checked = true;
        }
    }
    else {
        if (!empty($element['value'])) {
            $checked = true;
        }
        else if (!empty($element['defaultvalue'])) {
            $checked = true;
        }
    }

    return '<input type="checkbox"'
        . Form::element_attributes($element)
        . ($checked ? ' checked="checked"' : '')
        . '>';
}

function form_get_value_js_checkbox($element, Form $form) {
    $formname = $form->get_name();
    $name = $element['name'];
    return <<<EOF
    if (document.forms['$formname'].elements['$name'].checked) {
        data['$name'] = 'on';
    }

EOF;
}

function form_get_value_checkbox($element, Form $form) {
    $global = ($form->get_method() == 'get') ? $_GET : $_POST;
    if (isset($global[$element['name']])) {
        return 'on';
    }
    return null;
}

?>
