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
 * @subpackage form
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Default renderer - renders form elements inside <div>s.
 */
function form_renderer_div($builtelement, $rawelement) {
    // Set the class of the enclosing <div> to match that of the element
    $result = '<div';
    if ($rawelement['class']) {
        $result .= ' class="' . $rawelement['class'] . '"';
    }
    $result .= '>';

    if (isset($rawelement['title']) && $rawelement['type'] != 'fieldset') {
        $result .= '<label for="' . $rawelement['id'] . '">' . hsc($rawelement['title']) . '</label>';
    }

    $result .= $builtelement;

    // Contextual help
    if (isset($rawelement['help'])) {
        $result .= ' <span class="help"><a href="#" title="' . hsc($rawelement['help']) . '">?</a></span>';
    }

    // Description - optional description of the element, or other note that should be visible
    // on the form itself (without the user having to hover over contextual help 
    if (isset($rawelement['description'])) {
        $result .= '<div class="description"> ' . hsc($rawelement['description']) . "</div>";
    }

    if (isset($rawelement['error'])) {
        $result .= '<div class="errmsg">' . hsc($rawelement['error']) . '</div>';
    }

    $result .= "</div>\n";
    return $result;
}

?>
