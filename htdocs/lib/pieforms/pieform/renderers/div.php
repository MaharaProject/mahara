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
 * @subpackage renderer
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Renders form elements inside <div>s.
 *
 * @param Pieform $form    The form the element is being rendered for
 * @param array   $element The element to be rendered
 * @return string          The element rendered inside an appropriate container
 */
function pieform_renderer_div(Pieform $form, $element) {/*{{{*/
    $formname = $form->get_name();
    // Set the class of the enclosing <div> to match that of the element
    $result = '<div';
    if (isset($element['name'])) {
        $result .= ' id="' . $formname . '_' .  Pieform::hsc($element['name']) . '_container"';
    }
    if (!empty($element['class'])) {
        $result .= ' class="' . Pieform::hsc($element['class']) . '"';
    }
    $result .= '>';

    if (isset($element['labelhtml'])) {
        $result .= $element['labelhtml'];
    }

    //$result .= $builtelement;
    $result .= $element['html'];

    if (isset($element['helphtml'])) {
        $result .= ' ' . $element['helphtml'];
    }

    // Description - optional description of the element, or other note that should be visible
    // on the form itself (without the user having to hover over contextual help 
    if ((!$form->has_errors() || $form->get_property('showdescriptiononerror')) && !empty($element['description'])) {
        $result .= '<div class="description"> ' . $element['description'] . "</div>";
    }

    if (!empty($element['error'])) {
        $result .= '<div class="errmsg">' . $element['error'] . '</div>';
    }

    $result .= "</div>\n";
    return $result;
}/*}}}*/
