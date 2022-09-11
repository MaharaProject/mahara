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
 * Provides a basic checkbox input.
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_checkbox(Pieform $form, $element) {/*{{{*/

    $smarty = smarty_core();

    $baseattributes = $form->element_attributes($element);

    $checked = false;
    if (isset($element['rules']['required'])) {
        throw new PieformException("For pieform_element_checkbox, 'required' is not allowed as a rule. Radio should be used instead.");
    }
    if (!empty($element['value'])) {
        $checked = true;
    }
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if ($form->is_submitted()) {
        if (isset($global[$element['name']])) {
            $checked = true;
        }
        else if ((isset($element['disabled']) && $element['disabled']) && isset($element['defaultvalue'])) {
            $checked = (bool) $element['defaultvalue'];
        }
    }
    else if (!$form->is_submitted() && !empty($element['defaultvalue'])) {
        $checked = true;
    }
    else if (!empty($element['readonly']) && !empty($element['defaultvalue'])) {
        $checked = true;
    }

    $arialabel = '';
    if (!empty($element['arialabel'])) {
        $arialabel = Pieform::hsc($element['title']);
    }

    $smarty->assign('baseattributes', $baseattributes);
    $smarty->assign('arialabel', $arialabel);
    $smarty->assign('checked', $checked);

    return $smarty->fetch('form/checkbox.tpl');

}/*}}}*/

function pieform_element_checkbox_get_value(Pieform $form, $element) {/*{{{*/
    $name = $element['name'];
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    if (isset($element['value'])) {
        return $element['value'];
    }

    if ($form->is_submitted()) {
        if(isset($global[$name])) {
            return true;
        }
        else if (isset($element['disabled']) && $element['disabled']) {
            return (bool) $element['defaultvalue'];
        }
        return false;
    }

    if (isset($element['defaultvalue'])) {
        return $element['defaultvalue'];
    }

    return false;
}/*}}}*/
