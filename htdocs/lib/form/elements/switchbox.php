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
 *     show_labels       boolean     Should we show the on_label and/or off_label labels?
 *     labels_placement  text        Position of the labels: "both", "left" or "right"
 *     on_label          text        Text to be displayed when checked
 *     off_label         text        Text to be displayed when unchecked
 *     width             integer     Width of the button in pixels
 *     height            integer     Height of the button in pixels
 *     button_width      integer     Width of the sliding part in pixels
 *     clear             boolean     Should we insert a div with style="clear: both;" after the switch button?
 *     clear_after       text        Override the element after which the clearing div should be
 *                                   inserted. Accepts valid jQuery selector value eg '#foo'. Default is null.
 *     on_callback       function    Callback function that will be executed after going to on state
 *     off_callback      function    Callback function that will be executed after going to off state
 *     wrapperclass      text        Class to use on the div wrapper
 *
 * @return string           The HTML for the element
 */
function pieform_element_switchbox(Pieform $form, $element) {
    $wrapper = !empty($element['wrapperclass']) ? $element['wrapperclass'] : '';
    $html = '<div class="' . $wrapper . '">' . pieform_element_checkbox($form, $element) . '</div>';

    $elementid = $form->make_id($element, $form->get_name());
    $settings = '';
    // Dealing with the showing/placement of the label text
    $settings .= (isset($element['show_labels']) && empty($element['show_labels'])) ? 'show_labels: 0, ' : '';
    $settings .= (isset($element['labels_placement']) && ($element['labels_placement'] == 'left' || $element['labels_placement'] == 'right')) ? 'labels_placement: "' . $element['labels_placement'] . '", ' : '';
    $settings .= 'on_label: "' . (!isset($element['on_label']) ? get_string('on', 'mahara') : $element['on_label']) . '", ';
    $settings .= 'off_label: "' . (!isset($element['off_label']) ? get_string('off', 'mahara') : $element['off_label']) . '", ';
    // Dealing with the sizing/placement of the button
    $settings .= (isset($element['width']) && is_int($element['width'])) ? 'width: ' . $element['width'] . ', ' : '';
    $settings .= (isset($element['height']) && is_int($element['height'])) ? 'height: ' . $element['height'] . ', ' : '';
    $settings .= (isset($element['button_width']) && is_int($element['button_width'])) ? 'button_width: ' . $element['button_width'] . ', ' : '';
    $settings .= (isset($element['clear']) && !empty($element['clear'])) ? 'clear: 1, ' : '';
    $settings .= (isset($element['clear_after'])) ? 'clear_after: "' . $element['clear_after'] . '", ' : '';
    // Dealing with passing callbacks to the button
    $settings .= (isset($element['on_callback'])) ? 'on_callback: ' . (preg_match('/^function/', $element['on_callback']) ? $element['on_callback'] : '"' . $element['on_callback'] . '"') . ', ' : '';
    $settings .= (isset($element['off_callback'])) ? 'off_callback: ' . $element['off_callback'] . ', ' : '';

    $js = <<<JS
<script type="text/javascript">
    jQuery('#{$elementid}').switchButton({
        {$settings}
    });
</script>
JS;
    return $html . $js;
}

/**
 * Returns code to go in <head> for the given switchbox instance
 *
 * @param array $element The element to get <head> code for
 * @return array         An array of HTML elements to go in the <head>
 */
function pieform_element_switchbox_get_headdata($element) {
    global $THEME;

    $cssfile = get_config('wwwroot') . 'js/jquery/jquery-ui/js/switchbutton/jquery.switchButton.css';
    $jqueryjs = get_config('wwwroot') . 'js/jquery/jquery-ui/js/jquery-ui-1.10.2.min.js';
    $jsfile = get_config('wwwroot') . 'js/jquery/jquery-ui/js/switchbutton/jquery.switchButton.js';

    $r = <<<JS
<link rel="stylesheet" href="{$cssfile}" />
<script type="text/javascript" src="{$jqueryjs}"></script>
<script type="text/javascript" src="{$jsfile}"></script>
<script type="text/javascript">
// Basic callback that submits the form the switchbutton is in
function switchbox_submit() {
    if (typeof formchangemanager !== 'undefined') {
        formchangemanager.setFormStateById(this.element[0].form.id, FORM_INIT);
    }
    this.element[0].form.submit();
}
</script>
JS;
    return array($r);
}

function pieform_element_switchbox_get_value(Pieform $form, $element) {
    return pieform_element_checkbox_get_value($form, $element);
}