<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2013 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage form-element
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2013 Catalyst IT Ltd http://catalyst.net.nz
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
                . "addElementClass('${inputid}_expand', 'hidden'); "
                . "jQuery('#{$inputid}').attr('name', '{$element['name']}');"
                . "removeElementClass('{$inputid}', 'hidden'); "
                . "return false;"
                . "\" id=\"${inputid}_expand\">" . $linktext . '</a>';
                $element['class'] .= ' hidden';
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
