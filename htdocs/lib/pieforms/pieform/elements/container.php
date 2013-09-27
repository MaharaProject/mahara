<?php
/**
 * Pieforms: Advanced web forms made easy
 * Copyright (C) 2012 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @package    pieforms
 * @subpackage element
 * @author     Hugh Davenport <hugh@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  (C) 2012 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Renders a container. Containers contain other elements, and do not count as a
 * "true" element, in that they do not have a value and cannot be validated.
 *
 * Similar to a fieldset, except with no wrapper, apart from the div produced by
 * the pieform
 *
 * @param Pieform $form    The form to render the element for
 * @param array   $element The element to render
 * @return string          The HTML for the element
 */
function pieform_element_container(Pieform $form, $element) {/*{{{*/
    $result = "";
    foreach ($element['elements'] as $subname => $subelement) {
        $result .= "\t" . pieform_render_element($form, $subelement);
    }
    return $result;
}/*}}}*/
