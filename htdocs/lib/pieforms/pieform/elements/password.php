<?php
/**
 * Pieforms: Advanced web forms made easy
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    pieform
 * @subpackage element
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Renders a password field
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_element_password(Pieform $form, $element) {/*{{{*/
    $result = '<input type="password"'
        . $form->element_attributes($element)
        . ' value="' . Pieform::hsc($form->get_value($element)) . '">';

    if (isset($element['showstrength']) && $element['showstrength']) {
        $id = $form->get_name() . '_' . $element['id'];
        $msg1 = get_string('passwordstrength1');
        $msg2 = get_string('passwordstrength2');
        $msg3 = get_string('passwordstrength3');
        $msg4 = get_string('passwordstrength4');
        $result .= '<label for="strengthBar"></label>'
            . '<div class="form-control progress password-progress">'
            . '    <div id="strengthBar" class="progress-bar" role="progressbar" style="width: 0;"></div>'
            . '</div>';
        $result .= <<<EOJS
<script>
jQuery('#{$id}').on("keyup", function() {
    var result = zxcvbn(jQuery('#{$id}').val());
    var score  = result.score;

    var bar = jQuery('#strengthBar');
    switch (score) {
        case 0:
            bar.attr('class', 'progress-bar progress-bar-danger').css('width', '1%');
            bar.text('');
            break;
        case 1:
            bar.attr('class', 'progress-bar progress-bar-danger').css('width', '25%');
            bar.text('{$msg1}');
            break;
        case 2:
            bar.attr('class', 'progress-bar progress-bar-danger').css('width', '50%');
            bar.text('{$msg2}');
            break;
        case 3:
            bar.attr('class', 'progress-bar progress-bar-warning').css('width', '75%');
            bar.text('{$msg3}');
            break;
        case 4:
            bar.attr('class', 'progress-bar progress-bar-success').css('width', '100%');
            bar.text('{$msg4}');
            break;
    }
});
</script>
EOJS;
    }

    return $result;
}/*}}}*/

function pieform_element_password_get_headdata() {
    $libjs = get_config('wwwroot') . 'js/zxcvbn/zxcvbn.js';
    $result = array(
        '<script src="' . append_version_number($libjs) . '"></script>',
    );
    return $result;
}

function pieform_element_password_get_value(Pieform $form, $element) {/*{{{*/
    if (isset($element['value'])) {
        return $element['value'];
    }
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if ($form->is_submitted() && isset($global[$element['name']])) {
        return $global[$element['name']];
    }
    if (isset($element['defaultvalue'])) {
        return $element['defaultvalue'];
    }
    return null;
}/*}}}*/
