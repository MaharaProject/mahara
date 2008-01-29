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

function pieform_renderer_oneline_header() {/*{{{*/
    return '<div>';
}/*}}}*/

function pieform_renderer_oneline_footer() {/*{{{*/
    return '</div>';
}/*}}}*/

/**
 * Renders form elements all on one line.
 *
 * @param Pieform $form    The form the element is being rendered for
 * @param array   $element The element that is being rendered
 * @return string          The element rendered inside an appropriate container
 */
function pieform_renderer_oneline(Pieform $form, $element) {/*{{{*/
    $formname = $form->get_name();
    // Set the class of the enclosing <div> to match that of the element
    $result = '<span';
    if (isset($element['name'])) {
        $result .= ' id="' . $formname . '_' . $element['name'] . '_container"';
    }
    if (!empty($element['class'])) {
        $result .= ' class="' . $element['class'] . '"';
    }
    $result .= '>';

    if (isset($element['title']) && $element['title'] !== '' && $element['type'] != 'fieldset') {
        if (!empty($element['nolabel'])) {
            // Don't bother with a label for the element
            $result .= Pieform::hsc($element['title']);
        }
        else {
            $result .= '<label for="' . $element['id'] . '">' . Pieform::hsc($element['title']) . '</label>';
        }
        if ($form->get_property('requiredmarker') && !empty($element['rules']['required'])) {
            $result .= ' <span class="requiredmarker">*</span>';
        }
    }

    $result .= $element['html'];

    $result .= "</span>";
    return $result;
}/*}}}*/

?>
