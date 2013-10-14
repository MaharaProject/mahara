<?php
/**
 *
 * @package    pieform
 * @subpackage element
 * @author     Gregor Anzelj <gregor.anzelj@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2010 Gregor Anzelj
 *
 */

/**
 * Provides a javascript color selector for inputting a hex color value.
 *
 * General documentation about the JavaScript Color Picker (Chooser) is available at
 * http://jscolor.com/
 *
 * @param Pieform $form    The form to render the element for
 * @param array   $element The element to render
 * @return string          The HTML for the element
 */
function pieform_element_color(Pieform $form, $element) {
    $result = '';
    $name = Pieform::hsc($element['name']);
    $value = Pieform::hsc($element['defaultvalue']);
    $transparent = (!empty($element['options']['transparent']) && $element['options']['transparent'] == true);
    // Color Picker (Chooser)
    $result = '<input type="text" name="' . $name . '_color" id="' . $name . '_color"'
        . ($transparent && !isset($element['defaultvalue']) ? ' disabled="disabled"' : '')
        . ($transparent ? ' class="color {hash:true,required:false}"' : ' class="color {hash:true}"')
        . ' value="' . ($value == 'transparent' ? '' : $value) . '">';

    // Transparency optional control
    if ($transparent) {
        $optional = <<<EOF
        <script type="text/javascript">
            function {$name}_toggle(x) {
                if ( x.checked ) {
                    $('{$name}_color').value   = '';
                    $('{$name}_color').disabled   = true;
                }
                else {
                    $('{$name}_color').value   = '#FFFFFF';
                    $('{$name}_color').disabled   = false;
                }
            }
        </script>
EOF;
        $optional .= ' ' . $form->i18n('element', 'color', 'or', $element) . ' <input type="checkbox" '
            . (isset($element['defaultvalue']) && $element['defaultvalue'] <> 'transparent' ? '' : 'checked="checked" ')
            . 'name="' . $name . '_optional" id="' . $name . '_optional" onchange="' . $name . '_toggle(this)" '
            . 'tabindex="' . Pieform::hsc($element['tabindex']) . '">';
        $optional .= ' <label for="' . $name . '_optional">' . $form->i18n('element', 'color', 'transparent', $element);

        $result .= $optional;
    }
    return $result;
}

/**
 * Returns the color value of the color selector element from the request or transparent
 *
 * @param Pieform $form    The form the element is attached to
 * @param array   $element The element to get the value for
 * @return string A 6-digit hex color value, or the string "transparent"
 */
function pieform_element_color_get_value(Pieform $form, $element) {
    $name = $element['name'];
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if ($form->is_submitted() && isset($global[$name . '_color']) && !isset($global[$name . '_optional'])) {
        $color = $global[$name . '_color'];

        // Whitelist for a 6-digit hex color
        $color = preg_replace('/[^a-f0-9]/i', '', $color);
        if (strlen($color) >= 6) {
            $color = substr($color, 0, 6);
        }
        else if (strlen($color) >= 3) {
            // If they provided a 3-digit color string, convert it into a 6-digit one by doubling each digit
            $color = substr($color, 0, 3);
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }
        else {
            $color = '';
        }
        if ($color === '') {
            return 'transparent';
        }
        $color = "#{$color}";
        return $color;
    }

    return 'transparent';
}

/**
 * Returns code to go in <head> for the given color selector instance
 *
 * @param array $element The element to get <head> code for
 * @return array         An array of HTML elements to go in the <head>
 */
function pieform_element_color_get_headdata($element) {
    $libfile   = get_config('wwwroot')  . 'js/jscolor/jscolor.js';
    $result = array(
        '<script type="text/javascript" src="' . $libfile . '"></script>'
    );
    return $result;
}

