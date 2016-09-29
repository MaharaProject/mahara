<?php
/**
 *
 * @package    mahara
 * @subpackage form
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
    $_PIEFORM_WYSIWYGS[$form->get_name()] = $form->get_name() . '_' . $element['name'];
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
        . $form->element_attributes($element, array('size'))
        . '>' . $value . '</textarea>';
}

function pieform_element_wysiwyg_rule_required(Pieform $form, $value, $element, $check) {
    return $check && strip_tags($value, '<img><iframe><object><embed>') === '' ? $form->i18n('rule', 'required', 'required', $element) : '';
}

function pieform_element_wysiwyg_get_headdata() {
    global $_PIEFORM_WYSIWYGS;

    if (is_html_editor_enabled() && !empty($_PIEFORM_WYSIWYGS)) {
        $result = '<script type="application/javascript">'
         . "\nvar editor_to_focus;"
         . "\nPieformManager.connect('onsubmit', null, tinyMCE.triggerSave);"
         . "\nPieformManager.connect('onload', null, function() {\n";
        foreach ($_PIEFORM_WYSIWYGS as $name => $editor) {
            $result .= "    if (!arguments[0] || arguments[0]=='{$name}') {\n";
            $result .= "        tinyMCE.execCommand('mceAddEditor', false, '$editor');\n";
            $result .= "        $('{$editor}').focus = function() {\n";
            $result .= "            editor_to_focus = '$editor';\n";
            $result .= "        };\n";
            $result .= "    };\n";
        }
        $result .= "});\nPieformManager.connect('onreply', null, function() {\n";
        foreach ($_PIEFORM_WYSIWYGS as $name => $editor) {
            $result .= "    if (!arguments[0] || arguments[0]=='{$name}') {\n";
            $result .= "        tinyMCE.execCommand('mceRemoveEditor', false, '$editor');\n";
            $result .= "    };\n";
        }
        $result .= "});</script>";
        safe_require('artefact', 'file');
        $strings = PluginArtefactFile::jsstrings('filebrowser');
        $jsstrings = '';
        foreach ($strings as $section => $sectionstrings) {
            foreach ($sectionstrings as $s) {
                $jsstrings .= "strings.$s=" . json_encode(get_raw_string($s, $section)) . ';';
            }
        }
        $headdata = '<script type="application/javascript">' . $jsstrings . '</script>';
        return array('tinymce', $result, $headdata);
    }
    return array();
}

function pieform_element_wysiwyg_get_value(Pieform $form, $element) {
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if (isset($element['value'])) {
        return clean_html($element['value']);
    }
    else if ($form->is_submitted() && isset($global[$element['name']])) {
        $value = $global[$element['name']];
        if (!is_html_editor_enabled()) {
            $value = format_whitespace($value);
        }
        return $value;
    }
    else if (isset($element['defaultvalue'])) {
        return clean_html($element['defaultvalue']);
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
            . "\ntinyMCE.execCommand('mceAddEditor', false, $editor);"
            . "\nPieformManager.connect('onsubmit', $formname, tinyMCE.triggerSave);"
            . "\nPieformManager.connect('onreply', $formname, function () {"
            . "\n  tinyMCE.execCommand('mceRemoveEditor', false, $editor);"
            . "});";
    }
    return '';
}
