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
 * Checks whether the field has been specified.
 *
 * @param Pieform $form    The form the rule is being applied to
 * @param string  $value   The value of the field
 * @param array   $element The element to check
 * @param string  $check   The oneof value to match on
 * @return string         The error message, if the value is invalid.
 */
function pieform_rule_oneof(Pieform $form, $value, $element, $check) {/*{{{*/
    if ($check) {
        $oneof = false;
        foreach ($form->get_property('elements') as $name => $element) {
            foreach ($element as $opt => $val) {
                if ($opt === 'type' && $val === 'fieldset') {
                    $oneof = pieform_rule_oneof_fieldset_element($form, $element, $check, $oneof);
                }
                else if ($opt == 'rules' && isset($val['oneof']) && !empty($val['oneof']) && $val['oneof'] === $check) {
                    $refelement = $form->get_element($name);
                    $refvalue = $form->get_value($refelement);
                    if (is_bool($refvalue)) {
                        $refvalue = $refvalue ? 'true' : 'false';
                    }
                    if (!empty($refvalue)) {
                        $oneof = true;
                    }
                }
            }
        }
        if (!$oneof) {
            return $form->i18n('rule', 'oneof', 'oneof', $element);
        }
    }
    return '';
}/*}}}*/

/**
 * Checks whether the field has been specified inside a fieldset
 *
 * If we have a fieldset in our form we should look into their elements to see
 * if the 'oneof' rule exists there
 * Note: for sanity if we are having oneof fields in a fieldset all options should
 * live in the same fieldset
 *
 * @param Pieform $form     The form the rule is being applied to
 * @param array   $fieldset The fieldset to check
 * @param string  $check    The oneof value to match on
 * @param boolean $oneof    Whether one of the matching elements is not empty
 * @return boolean
 */
function pieform_rule_oneof_fieldset_element(Pieform $form, $fieldset, $check, $oneof) {

    foreach ($fieldset['elements'] as $name => $element) {
        foreach ($element as $opt => $val) {
            if ($opt === 'type' && $val === 'fieldset') {
                $oneof = pieform_rule_oneof_fieldset_element($form, $element, $check, $oneof);
            }
            else if ($opt == 'rules' && isset($val['oneof']) && !empty($val['oneof']) && $val['oneof'] === $check) {
                $refelement = $form->get_element($name);
                $refvalue = $form->get_value($refelement);
                if (is_bool($refvalue)) {
                    $refvalue = $refvalue ? 'true' : 'false';
                }
                if (!empty($refvalue)) {
                    $oneof = true;
                }
            }
        }
    }
    return $oneof;
}