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
    // Dealing with the label text
    $switchtext = isset($element['switchtext']) ? $element['switchtext'] : 'onoff';

    $html = '<div class="form-switch ' . $wrapper . '">';
    $html .= '    <div class="switch ' . $switchtext . '">' . pieform_element_checkbox($form, $element);
    $elementid = $form->make_id($element, $form->get_name());
    $html .= '        <label class="switch-label" for="' . $elementid . '">';
    $html .= '            <span class="switch-inner"></span>';
    $html .= '            <span class="switch-switch"></span>';
    $html .= '        </label>';
    $html .= '    </div>';
    $html .= '</div>';
    return $html;
}

/**
 * Getting the bits of css that need to be dynamic
 * Includes the text labels and width of button to accommodate the text
 */
function pieform_element_switchbox_get_css($element) {
    // Dealing with the label text
    $switchtext = isset($element['switchtext']) ? $element['switchtext'] : 'onoff';
    switch ($switchtext) {
        case 'truefalse':
            $on = 'true';
            $off = 'false';
            break;
        case 'yesno':
            $on = 'yes';
            $off = 'no';
            break;
        default:
            $on = 'on';
            $off = 'off';
            break;
    }

    $onlabel = get_string($on, 'mahara');
    $offlabel = get_string($off, 'mahara');
    $strlength = max(strlen($onlabel), strlen($offlabel));
    $width = (57 + (($strlength - 2) * 3.5) + pow(1.4, ($strlength - 2))) . 'px';
    $right = (35 + (($strlength - 2) * 3.5) + pow(1.4, ($strlength - 2))) . 'px';
    $cssdynamic = <<<CSS
<style id="$switchtext" type="text/css">
    .form-switch .$switchtext {
        width: $width;
    }
    .form-switch .$switchtext .switch-inner:before {
        content: "$onlabel";
    }
    .form-switch .$switchtext .switch-inner:after {
        content: "$offlabel";
    }
    .form-switch .$switchtext .switch-switch {
        right: $right;
    }
</style>
CSS;
    return $cssdynamic;
}

function pieform_element_switchbox_views_css(Pieform $form, $element) {
    return pieform_element_switchbox_get_css($element);
}

/**
 * Returns code to go in <head> for the given switchbox instance
 *
 * @param array $element The element to get <head> code for
 * @return array         An array of HTML elements to go in the <head>
 */
function pieform_element_switchbox_get_headdata($element) {
    global $THEME;

    $cssfile = get_config('wwwroot') . 'theme/raw/static/style/switchbox.css';

    $cssdynamic = pieform_element_switchbox_get_css($element);
    $r = <<<JS
<link rel="stylesheet" href="{$cssfile}" />
JS;
    $s = <<<JS2
{$cssdynamic}
JS2;
    return array($r, $s);
}

function pieform_element_switchbox_get_value(Pieform $form, $element) {
    return pieform_element_checkbox_get_value($form, $element);
}
