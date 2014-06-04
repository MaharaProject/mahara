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

require_once 'form/elements/wysiwyg.php';

/**
 * Renders a textarea, but with extra javascript to turn it into a wysiwyg
 * textarea.
 *
 * This version has far less controls - though that is configured in
 * lib/web.php
 *
 * @param array   $element The element to render
 * @param Pieform $form    The form to render the element for
 * @return string          The HTML for the element
 */
function pieform_element_tinywysiwyg(Pieform $form, $element) {
    return pieform_element_wysiwyg($form, $element);
}

function pieform_element_tinywysiwyg_rule_required(Pieform $form, $value, $element, $check) {
    return pieform_element_wysiwyg_rule_required($form, $value, $element, $check);
}

function pieform_element_tinywysiwyg_get_headdata() {
    if (is_html_editor_enabled()) {
        return array('tinytinymce');
    }
    return array();
}

function pieform_element_tinywysiwyg_get_value(Pieform $form, $element) {
    return pieform_element_wysiwyg_get_value($form, $element);
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
function pieform_element_tinywysiwyg_views_js(Pieform $form, $element) {
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
