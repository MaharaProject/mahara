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
 * Renders a submit button
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_submit(Pieform $form, $element) {/*{{{*/
    if (isset($element['confirm'])) {
        $element['onclick'] = 'return confirm(' . json_encode($element['confirm']) . ');';
    }

    $element['class'] .= ' btn';

    return '<input type="submit"'
        . $form->element_attributes($element)
        . ' value="' . Pieform::hsc($form->get_value($element)) . '">';
}/*}}}*/

function pieform_element_submit_set_attributes($element) {/*{{{*/
    $element['submitelement'] = true;
    return $element;
}/*}}}*/
