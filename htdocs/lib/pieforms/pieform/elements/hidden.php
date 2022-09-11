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
 * Renders a hidden element.
 *
 * @param Pieform $form  The form to render the element for
 * @param array $element The element to render
 * @return string        The HTML for the element
 */
function pieform_element_hidden(Pieform $form, $element) {/*{{{*/
    if (!array_key_exists('value', $element)) {
        throw new PieformException('The hidden element "' . $element['name'] . '" must have a value set');
    }
    if (!empty($element['sesskey']) && $form->get_property('method') != 'post') {
        throw new PieformException('Sesskey values should be POSTed');
    }
    $value = $form->get_value($element);
    if (is_array($value)) {
        $result = '';
        foreach ($value as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $subk => $subv) {
                    $result .= '<input type="hidden" name="' . Pieform::hsc($element['name'])
                        . '[' . Pieform::hsc($k) . '][' . Pieform::hsc($subk) . ']" value="' . Pieform::hsc($subv) . "\">\n";
                }
            }
            else {
                $result .= '<input type="hidden" name="' . Pieform::hsc($element['name'])
                    . '[' . Pieform::hsc($k) . ']" value="' . Pieform::hsc($v) . "\">\n";
            }
        }
        return $result;
    }
    return '<input type="hidden"'
        . $form->element_attributes($element, array('accesskey', 'onclick', 'size', 'style', 'tabindex'))
        . ' value="' . Pieform::hsc($form->get_value($element)) . "\">\n";
}/*}}}*/

/**
 * Returns the value for a hidden element. Hidden elements only listen to the
 * 'value' index, and not to GET/POST, unless the 'sesskey' property is set
 * on the element. Or, if the element has the "dynamic" tag set, which indicates
 * that it's a hidden field that is meant to be updated by Javascript.
 */
function pieform_element_hidden_get_value(Pieform $form, $element) {/*{{{*/
    if (!empty($element['dynamic']) || (!empty($element['sesskey']) && $form->is_submitted())) {
        return isset($_POST[$element['name']]) ? $_POST[$element['name']] : null;
    }
    return $element['value'];
}/*}}}*/
