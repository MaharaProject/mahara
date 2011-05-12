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
 * Renders form elements inside a <table>.
 *
 * @param Pieform $form    The form the element is being rendered for
 * @param array   $element The element to be rendered
 * @return string          The element rendered inside an appropriate container
 */
function pieform_renderer_table(Pieform $form, $element) {/*{{{*/
    $formname = $form->get_name();
    if ($element['type'] == 'fieldset') {
        // Add table tags to the build element, to preserve HTML compliance
        $builtelement = $element['html'];
        if (0 === strpos($builtelement, "\n<fieldset")) {
            $closelegendpos = strpos($builtelement, '</legend>');
            if ($closelegendpos !== false) {
                $closelegendpos += 9;
                $builtelement = substr($builtelement, 0, $closelegendpos) . '<table><tbody>' . substr($builtelement, $closelegendpos);
            }
            else {
                $pos = strpos($builtelement, '>') + 1;
                $builtelement = substr($builtelement, 0, $pos) . '<table><tbody>' . substr($builtelement, $pos);
            }
        }
        else {
            $builtelement = substr($builtelement, 0, 11) . '<table><tbody>' . substr($builtelement, 11);
        }
        $builtelement = substr($builtelement, 0, -12) . '</tbody></table></fieldset>';

        $result = "\t<tr>\n\t\t<td colspan=\"2\">";
        $result .= $builtelement;
        $result .= "</td>\n\t</tr>";
        return $result;
    }
    
    $result = "\t<tr";
    $result .= ' id="' . $formname . '_' . $element['name'] . '_container"';
    // Set the class of the enclosing <tr> to match that of the element
    if (!empty($element['class'])) {
        $result .= ' class="' . $element['class'] . '"';
    }
    $result .= ">\n\t\t";

    $result .= '<th>';
    if (isset($element['labelhtml'])) {
        $result .= $element['labelhtml'];
    }
    $result .= "</th>\n\t\t<td>";
    $result .= $element['html'];
    if (isset($element['helphtml'])) {
        $result .= ' ' . $element['helphtml'];
    }
    $result .= "</td>\n\t</tr>\n";

    // Description - optional description of the element, or other note that should be visible
    // on the form itself (without the user having to hover over contextual help 
    if ((!$form->has_errors() || $form->get_property('showdescriptiononerror')) && !empty($element['description'])) {
        if ($form->get_property('descriptionintwocells')) {
            $result .= "\t<tr>\n\t\t<td></td><td class=\"description\">";
        }
        else {
            $result .= "\t<tr>\n\t\t<td colspan=\"2\" class=\"description\">";
        }
        $result .= $element['description'];
        $result .= "</td>\n\t</tr>\n";
    }

    if (!empty($element['error'])) {
        $result .= "\t<tr>\n\t\t<td colspan=\"2\" class=\"errmsg\">";
        $result .= $element['error'];
        $result .= "</td>\n\t</tr>\n";
    }

    return $result;
}/*}}}*/

function pieform_renderer_table_header() {/*{{{*/
    return "<table cellspacing=\"0\"><tbody>\n";
}/*}}}*/

function pieform_renderer_table_footer() {/*{{{*/
    return "</tbody></table>\n";
}/*}}}*/
