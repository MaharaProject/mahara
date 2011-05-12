<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @package    mahara
 * @subpackage form-renderer
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Renders form elements inside a <table>.
 *
 * @param Pieform $form    The form the element is being rendered for
 * @param string  $element The element to be rendered
 * @return string          The element rendered inside an appropriate container
 */
function pieform_renderer_maharatable(Pieform $form, $element) {
    $formname = $form->get_name();
    if ($element['type'] == 'fieldset') {
        // Add table tags to the build element, to preserve HTML compliance
        $builtelement = $element['html'];
        if (0 === strpos($builtelement, "\n<fieldset")) {
            $closelegendpos = strpos($builtelement, '</legend>');
            if ($closelegendpos !== false) {
                $closelegendpos += 9;
                $builtelement = substr($builtelement, 0, $closelegendpos) . '<table>' . substr($builtelement, $closelegendpos);
            }
            else {
                $pos = strpos($builtelement, '>') + 1;
                $builtelement = substr($builtelement, 0, $pos) . '<table>' . substr($builtelement, $pos);
            }
        }
        else {
            $builtelement = substr($builtelement, 0, 11) . '<table>' . substr($builtelement, 11);
        }
        $builtelement = substr($builtelement, 0, -12) . '</table></fieldset>';

        $result = "\t<tr>\n\t\t<td colspan=\"2\">";
        $result .= $builtelement;
        $result .= "</td>\n\t</tr>";
        return $result;
    }
    
    $result = '';
    if (isset($element['labelhtml']) && $element['labelhtml'] !== '') {
        $result .= "\t<tr";
        $result .= ' id="' . $formname . '_' . Pieform::hsc($element['name']) . '_header"';
        // Set the class of the enclosing <tr> to match that of the element
        if ($element['class']) {
            $result .= ' class="' . Pieform::hsc($element['class']) . '"';
        }
        $result .= ">\n\t\t";

        $result .= '<th>';
        $result .= $element['labelhtml'];
        $result .= "</th>\n\t</tr>\n";
    }
    $result .= "\t<tr id=\"{$formname}_{$element['name']}_container\"";
    if ($element['class']) {
        $result .= ' class="' . Pieform::hsc($element['class']) . '"';
    }
    $result .= ">\n\t\t<td>";

    // Wrap WYSIWYG elements in a table with two cells side by side, one for the element and one for the help icon
    if (!empty($element['help']) && $element['type'] == 'wysiwyg') {
        $result .= '<table class="help-wrapper"><tr><td>';
    }

    // Add the element itself
    $result .= $element['html'];

    if (!empty($element['help']) && $element['type'] == 'wysiwyg') {
        $result .= '</td><td>';
    }

    // Contextual help
    if (!empty($element['help'])) {
        $result .= get_help_icon($form->get_property('plugintype'), 
                                 $form->get_property('pluginname'), 
                                 $form->get_name(), $element['name']);
        if ($element['type'] == 'wysiwyg') {
            $result .= '</td></tr></table>';
        }
    }

    $result .= "</td>\n\t</tr>\n";

    // Description - optional description of the element, or other note that should be visible
    // on the form itself (without the user having to hover over contextual help 
    if ((!$form->has_errors() || $form->get_property('showdescriptiononerror')) && !empty($element['description'])) {
        $result .= "\t<tr>\n\t\t<td class=\"description\">";
        $result .= $element['description'];
        $result .= "</td>\n\t</tr>\n";
    }

    if (!empty($element['error'])) {
        $result .= "\t<tr>\n\t\t<td class=\"errmsg\">";
        $result .= $element['error'];
        $result .= "</td>\n\t</tr>\n";
    }

    return $result;
}

function pieform_renderer_maharatable_header() {
    return "<table cellspacing=\"0\" border=\"0\" class=\"maharatable\"><tbody>\n";
}

function pieform_renderer_maharatable_footer() {
    return "</tbody></table>\n";
}


// @todo table renderer - probably doesn't need the remove_error function for the same reason that
// this one doesn't (all errors are removed on form submit). Also should set classes on elements.
function pieform_renderer_maharatable_get_js($id) {
    $result = <<<EOF
function {$id}_set_error(message, element) {
    element = '{$id}_' + element + '_container';
    var container = getFirstElementByTagAndClassName('TD', null, $(element));
    addElementClass(container, 'error');
    addElementClass(container.firstChild, 'error');
    insertSiblingNodesAfter($(element), TR({'id': '{$id}_error_' + element}, TD({'class': 'errmsg'}, message)));
}
function {$id}_remove_all_errors() {
    forEach(getElementsByTagAndClassName('TD', 'errmsg', $('$id')), function(item) {
        removeElement(item.parentNode);
    });
    forEach(getElementsByTagAndClassName('TD', 'error', $('$id')), function(item) {
        removeElementClass(item, 'error');
        removeElementClass(item.firstChild, 'error');
    });
}
EOF;
    return $result;
}
