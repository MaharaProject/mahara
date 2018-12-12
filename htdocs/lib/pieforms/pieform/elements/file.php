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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Renders a basic HTML <input type="file"> element.
 *
 * @param Pieform $form    The form to render the element for
 * @param array   $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_file(Pieform $form, $element) {/*{{{*/
    $result = '';
    $maxfilesize = '';
    if (isset($element['maxfilesize']) && is_int($element['maxfilesize'])){
        $result = '<input type="hidden" name="MAX_FILE_SIZE" value="' . $element['maxfilesize'] . '"/>';
        $maxfilesize = $element['maxfilesize'];
    }
    $result .= '<div class="' . (empty($element['description']) ? 'align-with-input file' : 'align-with-input-desc') . '"><input type="file"' . $form->element_attributes($element) .
        (isset($element['accept']) ? ' accept="' . Pieform::hsc($element['accept']) . '"' : '') .
        '>';
    if (!$maxfilesize) {
        // not supplied by form element
        $maxfilesize = get_max_upload_size(false);
    }
    $maxuploadsize = display_size($maxfilesize);
    $result .= '<br><span class="file-description">(' . get_string('maxuploadsize', 'artefact.file') . ' ' . $maxuploadsize . ')</span></div>';
    return $result;
}/*}}}*/

function pieform_element_file_get_value(Pieform $form, $element) {/*{{{*/
    if (isset($_FILES[$element['name']])) {
        if (!$_FILES[$element['name']]['error']) {
            return $_FILES[$element['name']];
        }
        return null;
    }
}/*}}}*/

function pieform_element_file_set_attributes($element) {/*{{{*/
    $element['needsmultipart'] = true;
    return $element;
}/*}}}*/
