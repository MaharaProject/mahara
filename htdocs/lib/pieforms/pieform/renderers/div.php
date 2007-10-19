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
 * @subpackage renderer
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Renders form elements inside <div>s.
 *
 * @param Pieform $form         The form the element is being rendered for
 * @param string $builtelement The element, already built
 * @param array  $rawelement   The element in raw form, for looking up
 *                             information about it.
 * @return string              The element rendered inside an appropriate
 *                             container.
 */
function pieform_renderer_div(Pieform $form, $builtelement, $rawelement) {
    $formname = $form->get_name();
    // Set the class of the enclosing <div> to match that of the element
    $result = '<div';
    if (isset($rawelement['name'])) {
        $result .= ' id="' . $formname . '_' . $rawelement['name'] . '_container"';
    }
    if ($rawelement['class']) {
        $result .= ' class="' . $rawelement['class'] . '"';
    }
    $result .= '>';

    if (isset($rawelement['title']) && $rawelement['title'] !== '' && $rawelement['type'] != 'fieldset') {
        if (!empty($rawelement['nolabel'])) {
            // Don't bother with a label for the element
            $result .= Pieform::hsc($rawelement['title']);
        }
        else {
            $result .= '<label for="' . $formname . '_' . $rawelement['id'] . '">' . Pieform::hsc($rawelement['title']) . '</label>';
        }
        if ($form->get_property('requiredmarker') && !empty($rawelement['rules']['required'])) {
            $result .= ' <span class="requiredmarker">*</span>';
        }
    }

    $result .= $builtelement;

    // Contextual help
    if (!empty($rawelement['help'])) {
        $function = $form->get_property('helpcallback');
        if (function_exists($function)) {
            $result .= $function($form, $rawelement);
        } 
        else {
            $result .= ' <span class="help"><a href="#" title="' . Pieform::hsc($rawelement['help']) . '">?</a></span>';
        }
    }

    // Description - optional description of the element, or other note that should be visible
    // on the form itself (without the user having to hover over contextual help 
    if ((!$form->has_errors() || $form->get_property('showdescriptiononerror')) && !empty($rawelement['description'])) {
        $result .= '<div class="description"> ' . Pieform::hsc($rawelement['description']) . "</div>";
    }

    if (!empty($rawelement['error'])) {
        $result .= '<div class="errmsg">' . Pieform::hsc($rawelement['error']) . '</div>';
    }

    $result .= "</div>\n";
    return $result;
}


function pieform_renderer_div_get_js($id) {
    $result = <<<EOF
// Given a message and form element name, should set an error on the element
function {$id}_set_error(message, element) {
    element = '{$id}_' + element;
    addElementClass(element + '_container', 'error');
    addElementClass(element, 'error');
   insertSiblingNodesAfter(element + '_container', DIV({'class': 'errmsg'}, message));
}
function {$id}_remove_all_errors() {
    forEach(getElementsByTagAndClassName('DIV', 'errmsg', '{$id}'), function(div) {
        removeElement(div);
    });
    forEach(getElementsByTagAndClassName(null, 'error', '{$id}'), function(div) {
        removeElementClass(div, 'error');
    });
}
EOF;
    return $result;
}

?>
