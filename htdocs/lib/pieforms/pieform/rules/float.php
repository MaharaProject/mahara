<?php
/**
 * Pieforms: Advanced web forms made easy
 * @package    pieform
 * @subpackage rule
 * @author     Dianne Tennent <dianne.tennent@catalyst.net.nz>
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Returns whether the given field is a number, including floating point numbers
 *
 * @param Pieform $form      The form the rule is being applied to
 * @param string  $value     The value to check
 * @param array   $element   The element to check
 * @return string            The error message, if there is something wrong with the value.
 */
function pieform_rule_float(Pieform $form, $value, $element) {/*{{{*/
    if (!is_numeric($value) || floatval($value) != $value) {
        return $form->i18n('rule', 'float', 'float', $element);
    }
    return '';
}/*}}}*/