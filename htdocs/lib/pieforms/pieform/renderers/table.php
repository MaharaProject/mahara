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
 * Renders form elements inside a <table>.
 *
 * @param Pieform $form         The form the element is being rendered for
 * @param string  $builtelement The element, already built
 * @param array   $rawelement   The element in raw form, for looking up
 *                              information about it.
 * @return string               The element rendered inside an appropriate
 *                              container.
 */
function pieform_renderer_table(Pieform $form, $builtelement, $rawelement) {
    $formname = $form->get_name();
    if ($rawelement['type'] == 'fieldset') {
        // Add table tags to the build element, to preserve HTML compliance
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
    
    $result = "\t<tr";
    $result .= ' id="' . $formname . '_' . $rawelement['name'] . '_container"';
    // Set the class of the enclosing <tr> to match that of the element
    if ($rawelement['class']) {
        $result .= ' class="' . $rawelement['class'] . '"';
    }
    $result .= ">\n\t\t";

    $result .= '<th>';
    if (isset($rawelement['title']) && $rawelement['title'] !== '') {
        if (!empty($rawelement['nolabel'])) {
            // Don't bother with a label for the element
            $result .= Pieform::hsc($rawelement['title']);
        }
        else {
            $result .= '<label for="' . $formname . '_' . $rawelement['id'] . '">' . Pieform::hsc($rawelement['title']) . '</label>';
        }
    }
    $result .= "</th>\n\t\t<td>";
    $result .= $builtelement;

    // Contextual help
    if (!empty($rawelement['help'])) {
        $result .= ' <span class="help"><a href="#" title="' . Pieform::hsc($rawelement['help']) . '">?</a></span>';
    }

    $result .= "</td>\n\t</tr>\n";

    // Description - optional description of the element, or other note that should be visible
    // on the form itself (without the user having to hover over contextual help 
    if (!empty($rawelement['description'])) {
        $result .= "\t<tr>\n\t\t<td colspan=\"2\" class=\"description\">";
        $result .= $rawelement['description'];
        $result .= "</td>\n\t</tr>\n";
    }

    if (!empty($rawelement['error'])) {
        $result .= "\t<tr>\n\t\t<td colspan=\"2\" class=\"errmsg\">";
        $result .= $rawelement['error'];
        $result .= "</td>\n\t</tr>\n";
    }

    return $result;
}

function pieform_renderer_table_header() {
    return "<table cellspacing=\"0\"><tbody>\n";
}

function pieform_renderer_table_footer() {
    return "</tbody></table>\n";
}

function pieform_renderer_table_get_js($id) {
    $result = <<<EOF
function {$id}_set_error(message, element) {
    element = $('{$id}_' + element + '_container');
    var container = getFirstElementByTagAndClassName('TD', null, element);
    addElementClass(container, 'error');
    addElementClass(container.firstChild, 'error');
    insertSiblingNodesAfter(element, TR(null, TD({'colspan': 2, 'class': 'errmsg'}, message)));
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

?>
