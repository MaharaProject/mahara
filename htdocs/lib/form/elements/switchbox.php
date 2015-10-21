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
    $wrapper = !empty($element['wrapperclass']) ? $element['wrapperclass'] : '';

    $html = '<div class="' . $wrapper . '">' . pieform_element_checkbox($form, $element) . '</div>';

    $labels = pieform_element_switchbox_labeltext($element);

    // Dealing with the label text
    $type = $labels['type'];
    $onlabel = $labels['on'];
    $offlabel = $labels['off'];

    $strlength = max(strlen($onlabel), strlen($offlabel));
    $width = floor((57 + (($strlength - 2) * 3.5) + pow(1.4, ($strlength - 2)))) . 'px';

    $elementid = $form->make_id($element, $form->get_name());

    $html = '<div class="form-switch ' . $wrapper . '">';
    $html .= '    <div class="switch ' . $type . '" style="width:'.$width.'">';
    $html .= pieform_element_checkbox($form, array_merge($element, array('arialabel' => true)));
    $html .= '        <label class="switch-label" for="' . $elementid . '" aria-hidden="true">';
    $html .= '            <span class="switch-inner"></span>';
    $html .= '            <span class="switch-indicator"></span>';
    $html .= '            <span class="state-label on">'. $onlabel .'</span>';
    $html .= '            <span class="state-label off">'. $offlabel .'</span>';
    $html .= '        </label>';
    $html .= '    </div>';
    $html .= '</div>';
    return $html;
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
    global $THEME;
    return array();
}

function pieform_element_switchbox_get_value(Pieform $form, $element) {
    return pieform_element_checkbox_get_value($form, $element);
}
