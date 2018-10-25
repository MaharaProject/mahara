<?php
require_once(get_config('docroot') . 'lib/pieforms/pieform/elements/checkbox.php');

/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Provides a checkbox styled as a switch.
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 *
 * The element can contain these variables (all are optional):
 *     switchtext        text        Text to be displayed on button - chosen by style
 *                                   valid options are 'onoff', 'yesno', 'truefalse' - used for headdata
 *     wrapperclass      text        Class to use on the div wrapper
 *
 * @return string           The HTML for the element
 */
function pieform_element_switchbox(Pieform $form, $element) {
    $smarty = smarty_core();
    $smarty->left_delimiter = '{{';
    $smarty->right_delimiter = '}}';

    $checkbox = pieform_element_checkbox($form, array_merge($element, array('arialabel' => true)));

    $wrapper = (!empty($element['wrapperclass']) ? $element['wrapperclass'] : '');
    $smarty->assign('wrapper', $wrapper);
    $smarty->assign('elementid', $form->make_id($element, $form->get_name()));
    $smarty->assign('libfile', get_config('wwwroot') . 'js/switchbox.js');
    $smarty->assign('checkbox', $checkbox);

    // Dealing with the label text
    $labels = pieform_element_switchbox_labeltext($element);
    $smarty->assign('type', $labels['type']);
    $smarty->assign('onlabel', $labels['on']);
    $smarty->assign('offlabel', $labels['off']);

    return $smarty->fetch('form/switchbox.tpl');
}

function pieform_element_switchbox_labeltext($element){
    // Dealing with the label text
    $type = isset($element['switchtext']) ? $element['switchtext'] : '';

    switch ($type) {
        case 'truefalse':
            $on = 'switchbox.true';
            $off = 'switchbox.false';
            break;
        case 'onoff':
            $on = 'switchbox.on';
            $off = 'switchbox.off';
            break;
        default:
            $on = 'switchbox.yes';
            $off = 'switchbox.no';
            break;
    }

    return array(
        'type' => $type,
        'on' => get_string($on, 'pieforms'),
        'off' => get_string($off, 'pieforms')
    );
}

/**
 * Returns code to go in <head> for the given switchbox instance
 *
 * @param array $element The element to get <head> code for
 * @return array         An array of HTML elements to go in the <head>
 */
function pieform_element_switchbox_get_headdata($element) {
    $libfile = get_config('wwwroot') . 'js/switchbox.js';
    $scripts = array();
    $scripts[] = '<script src="' . $libfile . '"></script>';
    return $scripts;
}

function pieform_element_switchbox_get_value(Pieform $form, $element) {
    return pieform_element_checkbox_get_value($form, $element);
}
