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
 * Checks whether the given value is shorter than the allowed length.
 *
 * @param PieForm $form      The form the rule is being applied to
 * @param string  $value     The value to check
 * @param array   $element   The element to check
 * @param int     $minlength The length to check for
 * @return string            The error message, if the value is invalid.
 */
function pieform_rule_minlength(Pieform $form, $value, $element, $minlength) {/*{{{*/
    if (strlen($value) < $minlength) {
        return sprintf($form->i18n('rule', 'minlength', 'minlength', $element), $minlength);
    }
    return '';
}/*}}}*/
