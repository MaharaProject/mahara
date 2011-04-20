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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Provides a basic text field input.
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_text(Pieform $form, $element) {/*{{{*/
    $value = Pieform::hsc($form->get_value($element));
    $html = '';

    // If hidewhenempty is set, the text box is hidden by a link which expands it.
    if (!empty($element['hidewhenempty']) && $value == '') {
        $inputid = hsc($form->get_name() . '_' . $element['name']);
        $linktext = $element['expandtext'] ? hsc($element['expandtext']) : get_string('edit');
        $html .= '<a href="" '
            . "onclick=\"addElementClass('${inputid}_expand', 'hidden'); removeElementClass('{$inputid}', 'hidden'); return false;\""
            . "id=\"${inputid}_expand\">" . $linktext . '</a>';
        $element['class'] .= ' hidden';
    }

    return $html . '<input type="text"' . $form->element_attributes($element) . ' value="' . $value . '">';
}/*}}}*/
