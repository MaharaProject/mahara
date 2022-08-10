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
 * @param string  $check   Whether to check the element
 * @return string         The error message, if the value is invalid.
 */
function pieform_rule_required(Pieform $form, $value, $element, $check) {/*{{{*/
    if ($check && ($value == '' || $value == array())) {
        return $form->i18n('rule', 'required', 'required', $element);
    }
    return '';
}/*}}}*/
