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
 * Renders a basic HTML <input type="file"> element.
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_render_file($element, Pieform $form) {
    return '<input type="file"'
        . $form->element_attributes($element) . '>';
}

function pieform_get_value_file($element, Pieform $form) {
    if (isset($_FILES[$element['name']])) {
        if (!$_FILES[$element['name']]['error']) {
            return $_FILES[$element['name']];
        }
        return null;
    }
}

function pieform_is_empty_file($value, $element) {
    if (isset($_FILES[$element['name']]) && !$_FILES[$element['name']]['error']) {
        return false;
    }
    return true;
}

// @todo: provide a mechanism for elements to claim they deal with files.
// If this is triggered, the forms is forced to POST and the enctype stuff
// is added.
// @todo is enctype required for ajax submission of files?
?>
