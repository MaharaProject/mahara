<?php
/**
 * Pieforms: Advanced web forms made easy
 * @package    pieform
 * @subpackage element
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
    if (isset($element['toggledisplay']) && $element['toggledisplay']) {
        $result .= '<span class="icon icon-eye-slash togglePassword"></span>';
        $result .= <<<EOJS
<script>
jQuery('.togglePassword').off('click');
jQuery('.togglePassword').on('click', function() {
    const type = $(this).prev().prop("type") === "password" ? "text" : "password";
    $(this).prev().prop("type", type);
    $(this).toggleClass("icon-eye");
    $(this).toggleClass("icon-eye-slash");
});
</script>
EOJS;
    }
    if (isset($element['toggledisplay']) && $element['toggledisplay'] && isset($element['showstrength']) && $element['showstrength']) {
        // We need to add a clearing div so things line up
        $result .= '<div class="clear"></div>';
    }
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
