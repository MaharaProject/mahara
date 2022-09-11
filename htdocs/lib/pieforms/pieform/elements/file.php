<?php
/**
 * Pieforms: Advanced web forms made easy
 * @package    pieform
 * @subpackage element
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
    // if validfiletypes set then only accept those types
    $accepts = get_config('validfiletypes') ? ' accept="' . Pieform::hsc('.' . str_replace(',', ',.', get_config('validfiletypes'))) . '"' : '';
    $accepts = isset($element['accept']) ? ' accept="' . Pieform::hsc($element['accept']) . '"' : $accepts;
    // if form element accept is set then only accept those types
    $result .= '<div class="' . (empty($element['description']) ? 'align-with-input file' : 'align-with-input-desc') . '"><input type="file"' . $form->element_attributes($element) . $accepts . '>';
    if (!$maxfilesize) {
        // not supplied by form element
        $maxfilesize = get_max_upload_size(false);
    }
    $maxuploadsize = display_size($maxfilesize);
    $result .= '<br><span class="file-description text-small text-midtone">(' . get_string('maxuploadsize', 'artefact.file') . ' ' . $maxuploadsize . ')</span></div>';
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
