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
 * @package    pieforms
 * @subpackage element
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Renders a fieldset. Fieldsets contain other elements, and do not count as a
 * "true" element, in that they do not have a value and cannot be validated.
 *
 * @param array $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string        The HTML for the element
 */
function pieform_render_fieldset($element, Pieform $form) {
    $result = "\n<fieldset>\n";
    if (isset($element['legend'])) {
        $result .= '<legend>' . Pieform::hsc($element['legend']) . "</legend>\n";
    }

    foreach ($element['elements'] as $subname => $subelement) {
        if ($subelement['type'] == 'hidden') {
            throw new PieformError("You cannot put hidden elements in fieldsets");
        }
        $result .= "\t" . pieform_render_element($subelement, $form);
    }

    $result .= "</fieldset>\n";
    return $result;
}

?>
