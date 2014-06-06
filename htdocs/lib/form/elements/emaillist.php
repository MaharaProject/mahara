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

defined('INTERNAL') || die();

/**
 * Provides an email list, with verification to enable addresses
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_element_emaillist(Pieform $form, $element) {
    $smarty = smarty_core();

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

    if (!isset($value['unsent'])) {
        $value['unsent'] = array();
    }

    if (!isset($value['default'])) {
        $value['default'] = '';
    }

    if (is_array($value) && count($value)) {
        $smarty->assign('validated', $value['validated']);
        $smarty->assign('unvalidated', $value['unvalidated']);
        $smarty->assign('unsent', $value['unsent']);
        $smarty->assign('default', $value['default']);
    }

    $smarty->assign('form', $form->get_name());
    $smarty->assign('name', $element['name']);
    $smarty->assign('title', $element['title']);
    $smarty->assign('addbuttonstr', get_string('addbutton', 'artefact.internal'));
    $smarty->assign('validationemailstr', json_encode(get_string('validationemailwillbesent', 'artefact.internal')));
    $smarty->assign('disabled', !empty($element['disabled']));

    if (isset($element['description'])) {
        $smarty->assign('describedby', $form->element_descriptors($element));
    }

    return $smarty->fetch('form/emaillist.tpl');
}

function pieform_element_emaillist_get_value(Pieform $form, $element) {
    $name = $element['name'];

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    if (!isset($global[$name . '_valid']) || !is_array($global[$name . '_valid'])) {
        return null;
    }

    $value = array();

    $value['default'] = $global[$name . '_selected'];
    $value['validated'] = $global[$name . '_valid'];

    if (isset($global[$name . '_invalid']) && is_array($global[$name . '_invalid'])) {
        $value['unvalidated'] = $global[$name . '_invalid'];
    }

    if (isset($global[$name . '_unsent']) && is_array($global[$name . '_unsent'])) {
        $value['unsent'] = $global[$name . '_unsent'];
    }

    return $value;
}
