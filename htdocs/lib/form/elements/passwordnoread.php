<?php
/**
 *
 * @package    mahara
 * @subpackage form-element
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Provides a password field that doesn't send the password text back to the user's browser.
 * If the value of the field is empty, then you get a standard input type=password.
 * If the field is non-empty, you get a link that says "Change password?" and if you click
 * on that, you get an input type=password.
 * The "Change password?" text can be customized by providing an "expandtext" value.
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_passwordnoread(Pieform $form, $element) {
    if (isset($element['defaultvalue']) && $element['defaultvalue'] == '') {
        return '<input type="password"'
                . $form->element_attributes($element)
                . ' value="">';
    }
    else {
        $inputid = hsc($form->get_name() . '_' . $element['name']);
        $linktext = isset($element['expandtext']) ? hsc($element['expandtext']) : get_string('changepassword');
        $html = '<a href="" '
                . "onclick=\""
                . "jQuery('#${inputid}_expand').addClass('d-none'); "
                . "jQuery('#{$inputid}').attr('name', '{$element['name']}');"
                . "jQuery('#{$inputid}').removeClass('d-none'); "
                . "return false;"
                . "\" id=\"${inputid}_expand\">" . $linktext . '</a>';
                $element['class'] .= ' d-none';
        // This password input starts out invisible, and with a placeholder name (so that
        // it won't be processed by the form). When you click the link, it becomes visible
        // and gains its real name.
        // TODO: Non-JS version.
        return $html . '<input type="password" name="' . hsc($element['name'] . '_placeholder') . '" '. $form->element_attributes($element, array('name')) . ' value="">';
    }
}

/**
 * Return the value of the element. This returns an array with the defaultvalue (if supplied),
 * and the value newly submitted in this form (if supplied)
 *
 * @param Pieform $form
 * @param array $element
 * @return array with two keys, 'defaultvalue' and 'submittedvalue'
 */
function pieform_element_passwordnoread_get_value(Pieform $form, $element) {
    $ret = array();
    if (isset($element['defaultvalue'])) {
        $ret['defaultvalue'] = $element['defaultvalue'];
    }
    else {
        $ret['defaultvalue'] = null;
    }
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if ($form->is_submitted() && isset($global[$element['name']])) {
        $ret['submittedvalue'] = $global[$element['name']];
    }
    else {
        $ret['submittedvalue'] = null;
    }
    return $ret;
}
