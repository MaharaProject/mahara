<?php
/**
 * Pieforms: Advanced web forms made easy
 * @package    pieform
 * @subpackage rule
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Makes sure that the submitted value is specified in the 'options' index of
 * the element. This prevents malicious people from doing things like
 * submitting values that aren't in a select box.
 *
 * @param Pieform $form      The form the rule is being applied to
 * @param string  $field     The field to check
 * @param string  $element   The element being checked
 * @return string            The error message, if the value is invalid.
 */
function pieform_rule_validateoptions(Pieform $form, $field, $element) {/*{{{*/
    // Get the value into an array as a key if it's a scalar, since
    // the actual check involves array keys
    $field = (array) $field;

    $options = $element['type'] == 'select' ? pieform_element_select_get_options($element) : $element['options'];
    $allowedvalues = array_keys($options);
    foreach ($field as $key) {
        if (!in_array($key, $allowedvalues)) {
            return sprintf($form->i18n('rule', 'validateoptions', 'validateoptions', $element), Pieform::hsc($key));
        }
    }
    return '';
}/*}}}*/
