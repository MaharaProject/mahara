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

function pieform_renderer_oneline_header() {
    return '<div>';
}

function pieform_renderer_oneline_footer() {
    return '</div>';
}

/**
 * Renders form elements all on one line.
 *
 * @param Pieform $form        The form the element is being rendered for
 * @param string $builtelement The element, already built
 * @param array  $rawelement   The element in raw form, for looking up
 *                             information about it.
 * @return string              The element rendered inside an appropriate
 *                             container.
 */
function pieform_renderer_oneline(Pieform $form, $builtelement, $rawelement) {
    $formname = $form->get_name();
    // Set the class of the enclosing <div> to match that of the element
    $result = '<span';
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
            $result .= '<label for="' . $rawelement['id'] . '">' . Pieform::hsc($rawelement['title']) . '</label>';
        }
        if ($form->get_property('requiredmarker') && !empty($rawelement['rules']['required'])) {
            $result .= ' <span class="requiredmarker">*</span>';
        }
    }

    $result .= $builtelement;

    $result .= "</span>";
    return $result;
}

function pieform_renderer_oneline_get_js($id) {
        return <<<EOF
function {$id}_remove_all_errors () {}
function {$id}_set_error () {}
EOF;
}

?>
