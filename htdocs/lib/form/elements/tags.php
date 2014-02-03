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

/**
 * Provides a tag input field
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_tags(Pieform $form, $element) {
    $smarty = smarty_core();

    $value = array();

    if (isset($element['defaultvalue']) && is_array($element['defaultvalue'])) {
        $value = $element['defaultvalue'];
    }

    if ($tempvalue = $form->get_value($element)) {
        $value = $tempvalue;
    }

    if (isset($element['value']) && is_array($element['value'])) {
        $value = $element['value'];
    }

    if (!is_array($value)) {
        $value = array();
    }

    if (!isset($element['size'])) {
        $element['size'] = 60;
    }

    $smarty->assign('name', $element['name']);
    $smarty->assign('size', $element['size']);
    $smarty->assign('id', $form->get_name() . '_' . $element['id']);
    $smarty->assign('value', join(', ', $value));
    if (isset($element['description'])) {
        $smarty->assign('describedby', $form->element_descriptors($element));
    }

    $smarty->left_delimiter = '{{';
    $smarty->right_delimiter = '}}';

    return $smarty->fetch('form/tags.tpl');
}

function pieform_element_tags_get_value(Pieform $form, $element) {
    $name = $element['name'];

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    if (!isset($global[$name])) {
        return null;
    }

    $value = preg_split("/\s*,\s*/", trim($global[$name]));
    $value = array_unique(array_filter($value, create_function('$v', 'return !empty($v);')));

    return $value;
}
