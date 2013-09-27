<?php
/**
 *
 * @package    mahara
 * @subpackage form-element
 * @author     Richard Mansfield
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Multiple text elements
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_element_multitext(Pieform $form, $element) {
    $smarty = smarty_core();

    $smarty->left_delimiter = '{{';
    $smarty->right_delimiter = '}}';

    $values = array();
    foreach ($form->get_value($element) as $v) {
        if ($v) {
            $values[] = hsc($v);
        }
    }

    $smarty->assign('value', $values);
    $smarty->assign('next', count($values));
    $smarty->assign('name', $form->get_name() . '_' . $element['name']);

    return $smarty->fetch('form/multitext.tpl');
}

function pieform_element_multitext_get_value(Pieform $form, $element) {
    if (isset($element['value'])) {
        return $element['value'];
    }

    $global = $form->get_property('method') == 'get' ? $_GET : $_POST;
    $name = $form->get_name() . '_' . $element['name'];

    if ($form->is_submitted() && isset($global[$name]) && is_array($global[$name])) {
        return array_values($global[$name]);
    }

    if (isset($element['defaultvalue'])) {
        return $element['defaultvalue'];
    }

    return null;
}/*}}}*/
