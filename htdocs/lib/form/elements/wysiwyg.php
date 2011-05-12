<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
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
 * @subpackage form
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

$_PIEFORM_WYSIWYGS = array();

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
    global $_PIEFORM_WYSIWYGS;
    $_PIEFORM_WYSIWYGS[] = $form->get_name() . '_' . $element['name'];
    if (is_html_editor_enabled()) {
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

    if (is_html_editor_enabled()) {
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

function pieform_element_wysiwyg_rule_required(Pieform $form, $value, $element, $check) {
    return $check && strip_tags($value) === '' ? $form->i18n('rule', 'required', 'required', $element) : '';
}

function pieform_element_wysiwyg_get_headdata() {
    global $_PIEFORM_WYSIWYGS;
    if (is_html_editor_enabled()) {
        $result = '<script type="text/javascript">'
         . "\nvar editor_to_focus;"
         . "\nPieformManager.connect('onsubmit', null, tinyMCE.triggerSave);"
         . "\nPieformManager.connect('onload', null, function() {\n";
        foreach ($_PIEFORM_WYSIWYGS as $editor) {
            $result .= "    tinyMCE.execCommand('mceAddControl', false, '$editor');\n";
            $result .= "    $('{$editor}').focus = function() {\n";
            $result .= "        editor_to_focus = '$editor';\n";
            $result .= "    };\n";
        }
        $result .= "});\nPieformManager.connect('onreply', null, function() {\n";
        foreach ($_PIEFORM_WYSIWYGS as $editor) {
            $result .= "    tinyMCE.execCommand('mceRemoveControl', false, '$editor');\n";
        }
        $result .= "});</script>";
        return array('tinymce', $result);
    }
    return array();
}

function pieform_element_wysiwyg_get_value(Pieform $form, $element) {
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if (isset($element['value'])) {
        return $element['value'];
    }
    else if (isset($global[$element['name']])) {
        $value = $global[$element['name']];
        if (!is_html_editor_enabled()) {
            $value = format_whitespace($value);
        }
        return $value;
    }
    else if (isset($element['defaultvalue'])) {
        return $element['defaultvalue'];
    }
    return null;
}

/**
 * Extension by Mahara. This api function returns the javascript required to
 * set up the element, assuming the element has been placed in the page using
 * javascript. This feature is used in the views interface.
 *
 * In theory, this could go upstream to pieforms itself
 *
 * @param Pieform $form     The form
 * @param array   $element  The element
 */
function pieform_element_wysiwyg_views_js(Pieform $form, $element) {
    if (is_html_editor_enabled()) {
        $formname = json_encode($form->get_name());
        $editor = json_encode($form->get_name() . '_' . $element['name']);
        return "\ntinyMCE.idCounter=0;"
            . "\ntinyMCE.execCommand('mceAddControl', false, $editor);"
            . "\nPieformManager.connect('onsubmit', $formname, tinyMCE.triggerSave);"
            . "\nPieformManager.connect('onreply', $formname, function () {"
            . "\n  tinyMCE.execCommand('mceRemoveControl', false, $editor);"
            . "});";
    }
    return '';
}
