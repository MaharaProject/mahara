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
 * @subpackage form
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Renders a textarea, but with extra javascript to turn it into a wysiwyg
 * textarea.
 *
 * @todo support resizable.
 *
 * @param array   $element The element to render
 * @param Pieform $form    The form to render the element for
 * @return string          The HTML for the element
 */
function pieform_element_wysiwyg(Pieform $form, $element) {
    global $USER;
    if ($USER->get_account_preference('wysiwyg')) {
        if (!$form->get_property('elementclasses')) {
            $element['class'] = isset($element['class']) && $element['class'] !== '' ? $element['class'] . ' wysiwyg' : 'wysiwyg';
        }
    }
    $rows = $cols = $style = '';
    if (isset($element['height'])) {
        $style .= 'height:' . $element['height'] . ';';
        $rows   = (intval($element['height'] > 0)) ? ceil(intval($element['height']) / 10) : 1;
    }
    elseif (isset($element['rows'])) {
        $rows = $element['rows'];
    }
    else {
        log_warn('No value for rows or height specified for textarea ' . $element['name']);
    }

    if (isset($element['width'])) {
        $style .= 'width:' . $element['width'] . ';';
        $cols   = (intval($element['width'] > 0)) ? ceil(intval($element['width']) / 10) : 1;
    }
    elseif (isset($element['cols'])) {
        $cols = $element['cols'];
    }
    else {
        log_warn('No value for cols or width specified for textarea ' . $element['name']);
    }
    $element['style'] = (isset($element['style'])) ? $style . $element['style'] : $style;

    if ($USER->get_account_preference('wysiwyg')) {
        $value = Pieform::hsc($form->get_value($element));
    }
    else {
        // Replace <br>s as added by wysiwyg editor or nl2br with a newline
        $value = preg_replace("#<br />\s#", "\n", $form->get_value($element));
        // As placed in the value by the wysiwyg editor
        $value = str_replace('</p><p>', "\n\n", $value);
        // Find the last </p> and replace with newlines
        $value = preg_replace('#</p>\s#', "\n", $value);
        $value = strip_tags($value);
    }

    return '<textarea'
        . (($rows) ? ' rows="' . $rows . '"' : '')
        . (($cols) ? ' cols="' . $cols . '"' : '')
        . $form->element_attributes($element, array('maxlength', 'size'))
        . '>' . $value . '</textarea>';
}

function pieform_element_wysiwyg_rule_required(Pieform $form, $value, $element) {
    return strip_tags($value) === '' ? $form->i18n('rule', 'required', 'required', $element) : '';
}

function pieform_element_wysiwyg_get_headdata() {
    global $USER;
    if ($USER->get_account_preference('wysiwyg') || defined('PUBLIC')) {
        return array('tinymce');
    }
    return array();
}

function pieform_element_wysiwyg_get_value(Pieform $form, $element) {
    global $USER;
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if (isset($element['value'])) {
        log_debug('returning value');
        return $element['value'];
    }
    else if (isset($global[$element['name']])) {
        $value = $global[$element['name']];
        if (!get_account_preference($USER->get('id'), 'wysiwyg')) {
            $value = format_whitespace($value);
        }
        else {
            $value = clean_text($value);
        }
        return $value;
    }
    else if (isset($element['defaultvalue'])) {
        return $element['defaultvalue'];
    }
    return null;
}

?>
