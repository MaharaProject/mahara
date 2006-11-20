<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage form-element
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Provides an email list, with verification to enable addresses
 *
 * @param array $element The element to render
 * @param Form  $form    The form to render the element for
 * @return string        The HTML for the element
 */
function form_render_emaillist($element, $form) {
    $smarty = smarty();

    $smarty->left_delimiter = '{{';
    $smarty->right_delimiter = '}}';

    $value = $form->get_value($element);

    if (!is_array($value) && isset($element['defaultvalue']) && is_array($element['defaultvalue'])) {
        $value = $element['defaultvalue'];
    }
    if (!isset($value['validated'])) {
        $value['validated'] = array();
    }

    if (!isset($value['unvalidated'])) {
        $value['unvalidated'] = array();
    }

    if (!isset($value['default'])) {
        $value['default'] = '';
    }
    
    if (is_array($value) && count($value)) {
        $smarty->assign('validated', $value['validated']);
        $smarty->assign('unvalidated', $value['unvalidated']);
        $smarty->assign('default', $value['default']);
    }

    $smarty->assign('name', $element['name']);

    return $smarty->fetch('form/emaillist.tpl');
}

function form_get_value_emaillist($element, Form $form) {
    $name = $element['name'];

    $global = ($form->get_method() == 'get') ? $_GET : $_POST;

    if (!isset($global[$name . '_valid']) || !is_array($global[$name . '_valid'])) {
        return null;
    }

    $value = array();

    $value['default'] = $global[$name . '_selected'];
    $value['validated'] = $global[$name . '_valid'];

    if (isset($global[$name . '_invalid']) && is_array($global[$name . '_invalid'])) {
        $value['unvalidated'] = $global[$name . '_invalid'];
    }

    return $value;
}

function form_get_value_js_emaillist($element, Form $form) {
    $formname = $form->get_name();
    $name = $element['name'];
    return <<<EOF
    var valid_list = document.forms['$formname'].elements['{$name}_valid\[\]'];
    var invalid_list = document.forms['$formname'].elements['{$name}_invalid\[\]'];

    data['{$name}_valid\[\]'] = new Array();
    data['{$name}_invalid\[\]'] = new Array();

    if (valid_list) {
        if (valid_list.length) {
            for (var i = 0; i < valid_list.length; i++) {
                if (valid_list[i].value) {
                    data['{$name}_valid\[\]'].push(valid_list[i].value);
                }
            }
        }
        else {
            data['{$name}_valid\[\]'].push(valid_list.value);
        }
    }

    if (invalid_list) {
        if (invalid_list.length) {
            for (var i = 0; i < invalid_list.length; i++) {
                if (invalid_list[i].value) {
                    data['{$name}_invalid\[\]'].push(invalid_list[i].value);
                }
            }
        }
        else {
            data['{$name}_invalid\[\]'].push(invalid_list.value);
        }
    }

    var emailselected = document.forms['$formname'].elements['${name}_selected'];

    if ( emailselected ) {
        if (!emailselected.length) {
            emailselected = [emailselected];
        }
        emailselected = filter(function(elem) { return elem.checked; }, emailselected);

        if (emailselected && emailselected[0]) {
            data['{$name}_selected'] = emailselected[0].value;
        }
    }

EOF;
}

?>
