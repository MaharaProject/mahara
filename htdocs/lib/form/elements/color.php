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
 * @author     Gregor Anzelj <gregor.anzelj@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
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
function pieform_element_color(Pieform $form, $element) {/*{{{*/
    $result = '';
    $name = Pieform::hsc($element['name']);
    $value = Pieform::hsc($element['defaultvalue']);
    $transparent = (!empty($element['options']['transparent']) and $element['options']['transparent'] == true);

    // Color Picker (Chooser)
    $result = '<input type="text" name="' . $name . '_color" id="' . $name . '_color"'
        . ($transparent && !isset($element['defaultvalue']) ? ' disabled="disabled"' : '')
        . ($transparent ? 'class="color {hash:true,required:false}"' : 'class="color {hash:true}"')
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
}/*}}}*/

/**
 * Returns the color value of the color selector element from the request or transparent
 *
 * @param Pieform $form    The form the element is attached to
 * @param array   $element The element to get the value for
 */
function pieform_element_color_get_value(Pieform $form, $element) {/*{{{*/
    $name = $element['name'];
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if ($form->is_submitted() && isset($global[$name . '_color']) && !isset($global[$name . '_optional'])) {
        return $global[$name . '_color'];
    }

    return 'transparent';
}/*}}}*/

/**
 * Returns code to go in <head> for the given color selector instance
 *
 * @param array $element The element to get <head> code for
 * @return array         An array of HTML elements to go in the <head>
 */
function pieform_element_color_get_headdata($element) {/*{{{*/
    $libfile   = get_config('wwwroot')  . 'js/jscolor/jscolor.js';
    $result = array(
        '<script type="text/javascript" src="' . $libfile . '"></script>'
    );
    return $result;
}/*}}}*/
