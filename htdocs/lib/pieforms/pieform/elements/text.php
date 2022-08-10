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
 * Provides a basic text field input.
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_text(Pieform $form, $element) {/*{{{*/
    $value = Pieform::hsc($form->get_value($element));
    $html = '';

    // If hidewhenempty is set, the text box is hidden by a link which expands it.
    if (!empty($element['hidewhenempty']) && $value == '') {
        $inputid = hsc($form->get_name() . '_' . $element['name']);
        $linktext = $element['expandtext'] ? hsc($element['expandtext']) : get_string('edit');
        $html .= '<a class="btn btn-secondary" href="" '
            . "onclick=\"jQuery('#${inputid}_expand').addClass('d-none'); jQuery('#{$inputid}').removeClass('d-none'); return false;\""
            . "id=\"${inputid}_expand\">" . $linktext . '</a>';
        $element['class'] .= ' d-none';
    }

    return $html . '<input type="text"' . $form->element_attributes($element) . ' value="' . $value . '">';
}/*}}}*/
