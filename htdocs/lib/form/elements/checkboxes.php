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

function pieform_element_checkboxes(Pieform $form, $element) {/*{{{*/
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    $submitted = $form->is_submitted();
    if ($submitted) {
        $value = isset($global[$element['name']]) ? $global[$element['name']] : array();
    }

    $result = '';

    if (empty($element['hideselectorbuttons']) && count($element['elements']) > 1) {
        $id = hsc($form->get_name() . '_' . $element['name']) . '_container';
        $result .= '<div class="btn-group"><a href="" class="btn btn-secondary btn-sm" onclick="pieform_element_checkboxes_update(\'' . $id . '\', true); return false;">' . get_string('selectall') . '</a>' .
        '<a href="" class="btn btn-secondary btn-sm" onclick="pieform_element_checkboxes_update(\'' . $id . '\', false); return false;">' . get_string('selectnone') . '</a></div>';
    }

    $element['name'] .= '[]';

    // Number of characters in checkbox labels (use 0 or false for no limit).
    $labelwidth = isset($element['labelwidth']) ? (int) $element['labelwidth'] : 17;

    $elementtitle = '';
    if (isset($element['title'])) {
        $elementtitle = '<span class="accessible-hidden sr-only">' . Pieform::hsc($element['title']) . ': </span>';
    }

    foreach ($element['elements'] as $e) {
        $id = $form->get_name() . '_' . $element['id'];
        $idsuffix = substr(md5(microtime()), 0, 4);
        if (!$submitted || !empty($e['disabled'])) {
            $checked = $e['defaultvalue'];
        }
        else {
            $checked = !empty($value[$e['value']]) || in_array($e['value'], $value);
        }
        $attributes = $form->element_attributes($element);
        $attributes = preg_replace("/\bid=\"{$id}\"/", "id=\"{$id}{$idsuffix}\"", $attributes);
        $title = $labelwidth ? str_shorten_text($e['title'], $labelwidth, true) : $e['title'];
        $result .= '<div class="checkboxes-option checkbox"><input type="checkbox" value="' . $e['value'] . '" '
            . $attributes . ($checked ? ' checked="checked"' : '') . (!empty($e['disabled']) ? ' disabled' : '') . '>'
            . ' <label class="checkbox" for="' . $id . $idsuffix . '">' . $elementtitle . Pieform::hsc($title) . '</label></div>';
    }
    $result .= '<div class="cl"></div>';

    return $result;
}/*}}}*/

function pieform_element_checkboxes_get_value(Pieform $form, $element) {/*{{{*/
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if (isset($element['value'])) {
        $values = (array) $element['value'];
    }
    else if ($form->is_submitted() && isset($global[$element['name']])) {
        $values = (array) $global[$element['name']];
    }
    else if (!$form->is_submitted() && isset($element['defaultvalue'])) {
        $values = (array) $element['defaultvalue'];
    }
    else {
        $values = array();
    }

    return $values;
}/*}}}*/

function pieform_element_checkboxes_js() {/*{{{*/
    return <<<EOF
function pieform_element_checkboxes_update(p, v) {
    jQuery('#' + p + ' input.checkboxes').each(function() {
        if (!jQuery(this).prop('disabled')) {
            jQuery(this).prop('checked', v);
        }
    });
    if (typeof formchangemanager !== 'undefined') {
        var form = jQuery('div#' + p).closest('form')[0];
        formchangemanager.setFormState(form, FORM_CHANGED);
    }
}
EOF;
}/*}}}*/

function pieform_element_checkboxes_get_headdata() {/*{{{*/
    $result = '<script>' . pieform_element_checkboxes_js() . "\n</script>";
    return array($result);
}/*}}}*/
