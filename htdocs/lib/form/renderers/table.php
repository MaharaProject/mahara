<?php
/**
 * This program is part of Mahara
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
 * @package    mahara
 * @subpackage form-renderer
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Renders form elements inside a <table>.
 *
 * @param string $builtelement The element, already built
 * @param array  $rawelement   The element in raw form, for looking up
 *                             information about it.
 * @return string              The element rendered inside an appropriate
 *                             container.
 */
function form_renderer_table($builtelement, $rawelement) {
    if ($rawelement['type'] == 'fieldset') {
        // Add table tags to the build element, to preserve HTML compliance
        if (0 === strpos($builtelement, "\n<fieldset>\n<legend>")) {
            $closelegendpos = strpos($builtelement, '</legend>') + 9;
            $builtelement = substr($builtelement, 0, $closelegendpos) . '<table>' . substr($builtelement, $closelegendpos);
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
    // Set the class of the enclosing <tr> to match that of the element
    $result = "\t<tr";
    if ($rawelement['class']) {
        $result .= ' class="' . $rawelement['class'] . '"';
    }
    $result .= ">\n\t\t";

    $result .= '<th>';
    if (isset($rawelement['title']) && $rawelement['type'] != 'fieldset') {
        $result .= '<label for="' . $rawelement['id'] . '">' . hsc($rawelement['title']) . '</label>';
    }
    $result .= "</th>\n\t\t<td>";
    $result .= $builtelement;

    // Contextual help
    if (!empty($rawelement['help'])) {
        $result .= ' <span class="help"><a href="#" title="' . hsc($rawelement['help']) . '">?</a></span>';
    }

    $result .= "</td>\n\t</tr>\n";

    // Description - optional description of the element, or other note that should be visible
    // on the form itself (without the user having to hover over contextual help 
    if (!empty($rawelement['description'])) {
        $result .= "\t<tr>\n\t\t<td colspan=\"2\" class=\"description\">";
        $result .= hsc($rawelement['description']);
        $result .= "</td>\n\t</tr>\n";
    }

    if (!empty($rawelement['error'])) {
        $result .= "\t<tr>\n\t\t<td colspan=\"2\" class=\"errmsg\">";
        $result .= hsc($rawelement['error']);
        $result .= "</td>\n\t</tr>\n";
    }

    return $result;
}

function form_renderer_table_header() {
    return "<table cellspacing=\"0\" border=\"0\">\n";
}

function form_renderer_table_footer() {
    return "</table>\n";
}

?>
