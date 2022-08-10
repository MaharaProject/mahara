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
 * Renders a "cancel" button. Custom buttons are rendered nearly the same as
 * normal submit buttons, only their name is changed (for use by the Pieform
 * class internally).
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_cancel(Pieform $form, $element) {/*{{{*/
    if (!isset($element['value'])) {
        throw new PieformException('Cancel elements must have a value');
    }

    if (isset($element['confirm'])) {
        $element['onclick'] = 'return confirm(' . json_encode($element['confirm']) . ');';
    }

    $attributes = $form->element_attributes($element);
    $attributes = preg_replace('/name="(.*)"/', 'name="cancel_$1"', $attributes);
    $attributes = preg_replace('/id="(.*)"/', 'id="cancel_$1"', $attributes);
    return '<input type="submit"'
        . $attributes
        . ' value="' . Pieform::hsc($element['value']) . '">';
}/*}}}*/

function pieform_element_cancel_set_attributes($element) {/*{{{*/
    $element['cancelelement'] = true;
    return $element;
}/*}}}*/
