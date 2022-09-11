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
 * Checks whether the given element's value is less than another element.
 *
 * Typically useful for dates.
 *
 * @param Pieform $form      The form the rule is being applied to
 * @param string  $value     The value to check
 * @param array   $element   The element to check
 * @param string  $otherelement The other element to check for
 * @return string            The error message, if the value is invalid.
 */
function pieform_rule_before(Pieform $form, $value, $element, $otherelement) {/*{{{*/
    $otherelement = $form->get_element($otherelement);
    $othervalue   = $form->get_value($otherelement);
    if ($value != '' && $othervalue != '' && intval($value) > intval($othervalue)) {
        return sprintf($form->i18n('rule', 'before', 'before', $element), $otherelement['title']);
    }
    return '';
}/*}}}*/
