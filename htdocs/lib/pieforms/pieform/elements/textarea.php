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

// TODO: plugins as classes...
global $_PIEFORM_TEXTAREAS;
$_PIEFORM_TEXTAREAS = array();

/**
 * Renders a basic HTML <textarea> element.
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_element_textarea(Pieform $form, $element) {/*{{{*/
    global $_PIEFORM_TEXTAREAS;
    $rows = $cols = $style = '';
    if (isset($element['height'])) {
        $style .= 'height:' . $element['height'] . ';';
        $rows   = (intval($element['height'] > 0)) ? ceil(intval($element['height']) / 10) : 1;
    }
    elseif (isset($element['rows'])) {
        $rows = $element['rows'];
    }
    else {
        Pieform::info('No value for rows or height specified for textarea "' . $element['name'] . '"');
    }

    if (isset($element['width'])) {
        $style .= 'width:' . $element['width'] . ';';
        $cols   = (intval($element['width'] > 0)) ? ceil(intval($element['width']) / 10) : 1;
    }
    elseif (isset($element['cols'])) {
        $cols = $element['cols'];
    }
    else {
        Pieform::info('No value for cols or width specified for textarea "' . $element['name'] . '"');
    }
    $element['style'] = (isset($element['style'])) ? $style . $element['style'] : $style;
    $fullwidth = (!empty($element['fullwidth'])) ? 'true' : 'false';
    if (!empty($element['resizable'])) {
        $element['class'] = (isset($element['class']) && $element['class']) ? $element['class'] . ' resizable' : 'resizable';
        $_PIEFORM_TEXTAREAS[] = array('formname' => $form->get_name(), 'elementname' => $form->get_name() . '_' . $element['id'], 'fullwidth' => $fullwidth);
    }

    return '<textarea'
        . (($rows) ? ' rows="' . $rows . '"' : '')
        . (($cols) ? ' cols="' . $cols . '"' : '')
        . $form->element_attributes($element, array('size'))
        . '>' . Pieform::hsc($form->get_value($element)) . '</textarea>';
}/*}}}*/

function pieform_element_textarea_get_value(Pieform $form, $element) {/*{{{*/
    if (isset($element['value'])) {
        return $element['value'];
    }

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if ($form->is_submitted() && isset($global[$element['name']])) {
        return str_replace("\r\n", "\n", $global[$element['name']]);
    }

    if (isset($element['defaultvalue'])) {
        return $element['defaultvalue'];
    }

    return '';
}/*}}}*/
