<?php
/**
 * Pieforms: Advanced web forms made easy
 * @package    pieform
 * @subpackage rule
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Returns whether the given field is an integer
 *
 * @param Pieform $form      The form the rule is being applied to
 * @param string  $value     The value to check
 * @param array   $element   The element to check
 * @return string            The error message, if there is something wrong with
 *                           the address.
 */
function pieform_rule_integer(Pieform $form, $value, $element) {/*{{{*/
    if (!is_numeric($value) || floor($value) != $value) {
        return $form->i18n('rule', 'integer', 'integer', $element);
    }
    return '';
}/*}}}*/
