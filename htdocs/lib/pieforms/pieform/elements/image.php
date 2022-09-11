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
 * Renders an <input type="image"> button
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_image(Pieform $form, $element) {/*{{{*/
    if (!isset($element['src'])) {
        throw new PieformException('"image" elements must have a "src" for the image');
    }
    if (!isset($element['value'])) {
        $element['value'] = true;
    }
    if (isset($element['confirm'])) {
        $element['onclick'] = 'return confirm(' . json_encode($element['confirm']) . ');';
    }
    if (!isset($element['alt'])) {
        if (isset($element['elementtitle'])) {
            $element['alt'] = $element['elementtitle'];
        }
        else {
            $element['alt'] = '';
        }
    }
    return '<input type="image" src="' . Pieform::hsc($element['src']) . '"'
        . ' alt="' . Pieform::hsc($element['alt']) . '"'
        . $form->element_attributes($element)
        . ' value="' . Pieform::hsc($form->get_value($element)) . '">';
}/*}}}*/

function pieform_element_image_set_attributes($element) {/*{{{*/
    $element['submitelement'] = true;
    return $element;
}/*}}}*/

function pieform_element_image_get_value(Pieform $form, $element) {/*{{{*/
    if (isset($element['value'])) {
        return $element['value'];
    }

    $global = $form->get_property('method') == 'get' ? $_GET : $_POST;
    if ($form->is_submitted() && isset($global[$element['name'] . '_x'])) {
        return true;
    }

    return null;
}/*}}}*/
