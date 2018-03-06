<?php
/**
 *
 * @package    pieforms
 * @subpackage element
 * @author     Gregor Anzelj <gregor.anzelj@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Provides a password policy chooser, in the form of a minimum length dropdown box
 * and password type (uppercase, lowercase, numerals, special symbols) dropdown box.
 *
 * @param Pieform  $form    The form to render the element for
 * @param array $element    The element to render
 * @return string           The HTML for the element
 */
function pieform_element_passwordpolicy(Pieform $form, $element) {/*{{{*/
    $name = Pieform::hsc($element['name']);
    $min = (isset($element['minlength'])) && intval($element['minlength']) >= 8 ? intval($element['minlength']) : 8;
    $max = (isset($element['maxlength'])) && intval($element['maxlength']) > intval($element['minlength']) ? intval($element['maxlength']) : 20;

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    // Get the value of the element for rendering.
    if (isset($element['value'])) {
        $value = $element['value'];
    }
    else if ($form->is_submitted() && isset($global[$name . '_number']) && isset($global[$name . '_format'])) {
        $value = $global[$name . '_number'] . '_' . $global[$name . '_format'];
    }
    else if (isset($element['defaultvalue'])) {
        $value = $element['defaultvalue'];
    }
    else {
        $value = '8_ulns'; // 8 characters - upper and lowercase letters, numbers, symbols
    }
    list($numbervalue, $formatvalue) = explode('_', $value);

    // Number dropdown
    $label = get_string('passwordpolicylength', 'admin');
    $number = '<label for="' . $name . '_number" class="accessible-hidden sr-only">' . $label . '</label>';
    $number .= '<span class="picker"><select class="form-control select" ';
    $number .= 'name="' . $name . '_number" id="' . $name . '_number"' . ' tabindex="' . Pieform::hsc($element['tabindex']) . '"';
    if (isset($element['description'])) {
        $number .= ' aria-describedby="' . $form->element_descriptors($element) . '"';
    }
    $number .= ">\n";
    for ($i = $min; $i <= $max; $i++) {
        $number .= "\t<option value=\"$i\"" . (($numbervalue == $i) ? ' selected="selected"' : '') . '>' . $i . "</option>\n";
    }
    $number .= "</select></span>\n";

    // Format dropdown
    $label = get_string('passwordpolicytype', 'admin');
    $format = '<label for="' . $name . '_format" class="accessible-hidden sr-only">' . $label . '</label>';
    $format .= '<span class="picker"><select class="form-control select" ';
    $format .= 'name="' . $name . '_format" id="' . $name . '_format"' . ' tabindex="' . Pieform::hsc($element['tabindex']) . '"';
    if (isset($element['description'])) {
        $format .= ' aria-describedby="' . $form->element_descriptors($element) . '"';
    }
    $format .= ">\n";
    foreach (pieform_element_passwordpolicy_get_formats() as $f) {
        $format .= "\t<option value=\"$f\"" . (($formatvalue == $f) ? ' selected="selected"' : '') . '>'
            . $form->i18n('element', 'passwordpolicy', $f, $element) . "</option>\n";
    }
    $format .= "</select></span>\n";

    return $number . $format;
}/*}}}*/

/**
 * Gets the value of the date element from the request and converts it into a
 * unix timestamp.
 *
 * @param Pieform $form    The form the element is attached to
 * @param array   $element The element to get the value for
 */
function pieform_element_passwordpolicy_get_value(Pieform $form, $element) {/*{{{*/
    $name = Pieform::hsc($element['name']);
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if ($form->is_submitted() && isset($global[$name . '_number']) && isset($global[$name . '_format'])) {
        return $global[$name . '_number'] . '_' . $global[$name . '_format'];
    }

    return '8_ulns';
}/*}}}*/


function pieform_element_passwordpolicy_get_formats() {/*{{{*/
    return array(
        'ul',   // Uppercase and lowercase letters
        'uln',  // Uppercase and lowercase letters, numbers
        'ulns', // Uppercase and lowercase letters, numbers, symbols
    );
}/*}}}*/
