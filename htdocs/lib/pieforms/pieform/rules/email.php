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
 * Returns whether the given field is a valid e-mail address.
 *
 * Currently, the check is [anything]@[anything]. Someone is welcome to write
 * something better, this was made just for testing.
 *
 * @param Pieform $form    The form the rule is being applied to
 * @param string  $value   The e-mail address to check
 * @param array   $element The element to check
 * @return string          The error message, if there is something wrong with
 *                         the address.
 */
function pieform_rule_email(Pieform $form, $value, $element) {/*{{{*/
    if (!sanitize_email($value)) {
        return $form->i18n('rule', 'email', 'email', $element);
    }
    return '';
}
