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
 * @package    pieforms
 * @subpackage element
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Provides a date picker, in the form of three dropdowns.
 *
 * @param Pieform  $form    The form to render the element for
 * @param array $element The element to render
 * @return string        The HTML for the element
 */
function pieform_element_date(Pieform $form, $element) {/*{{{*/
    $result = '';
    $name = Pieform::hsc($element['name']);
    $element['minyear'] = (isset($element['minyear'])) ? intval($element['minyear']) : 1950;
    $element['maxyear'] = (isset($element['maxyear'])) ? intval($element['maxyear']) : 2050;
    $required = (!empty($element['rules']['required']));
    if ($required && !isset($element['defaultvalue'])) {
        $element['defaultvalue'] = time();
    }

    // Year
    $value = pieform_element_date_get_timeperiod_value('year', $element['minyear'], $element['maxyear'], $element, $form);
    $year = '<select name="' . $name . '_year" id="' . $name . '_year"'
        . (!$required && !isset($element['defaultvalue']) ? ' disabled="disabled"' : '')
        . ' tabindex="' . Pieform::hsc($element['tabindex']) . "\">\n";
    for ($i = $element['minyear']; $i <= $element['maxyear']; $i++) {
        $year .= "\t<option value=\"$i\"" . (($value == $i) ? ' selected="selected"' : '') . ">$i</option>\n";
    }
    $year .= "</select>\n";

    // Month
    $value = pieform_element_date_get_timeperiod_value('month', 1, 12, $element, $form);
    $month = '<select name="' . $name . '_month" id="' . $name . '_month"'
        . (!$required && !isset($element['defaultvalue']) ? ' disabled="disabled"' : '')
        . ' tabindex="' . Pieform::hsc($element['tabindex']) . "\">\n";
    $monthnames = explode(',', $form->i18n('element', 'date', 'monthnames', $element));
    for ($i = 1; $i <= 12; $i++) {
        $month .= "\t<option value=\"$i\"" . (($value == $i) ? ' selected="selected"' : '') . '>' . $monthnames[$i-1] . "</option>\n";
    }
    $month .= "</select>\n";

    // Day
    $value = pieform_element_date_get_timeperiod_value('day', 1, 31, $element, $form);
    $day = '<select name="' . $name . '_day" id="' . $name . '_day"'
        . (!$required && !isset($element['defaultvalue']) ? ' disabled="disabled"' : '')
        . ' tabindex="' . Pieform::hsc($element['tabindex']) . "\">\n";
    for ($i = 1; $i <= 31; $i++) {
        $day .= "\t<option value=\"$i\"" . (($value == $i) ? ' selected="selected"' : '') . ">$i</option>\n";
    }
    $day .= '</select>';

    $result = $year . $month . $day;

    // Optional control
    if (!$required) {
        $optional = <<<EOF
        <script type="text/javascript">
            function {$name}_toggle(x) {
                if ( x.checked ) {
                    $('{$name}_day').disabled   = true;
                    $('{$name}_month').disabled = true;
                    $('{$name}_year').disabled  = true;
                }
                else {
                    $('{$name}_day').disabled   = false;
                    $('{$name}_month').disabled = false;
                    $('{$name}_year').disabled  = false;
                }
            }
        </script>
EOF;
        // @todo this needs cleaning up, namely:
        //   - get_string is a mahara-ism
        //   - 'optional' => true should be 'required' => false shouldn't it?
        $optional .= ' ' . $form->i18n('element', 'date', 'or', $element) . ' <input type="checkbox" '
            . (isset($element['defaultvalue']) ? '' : 'checked="checked" ')
            . 'name="' . $name . '_optional" id="' . $name . '_optional" onchange="' . $name . '_toggle(this)" '
            . 'tabindex="' . Pieform::hsc($element['tabindex']) . '">';
        $optional .= ' <label for="' . $name . '_optional">' . $form->i18n('element', 'date', 'notspecified', $element);

        $result .= $optional;
    }

    return $result;
}/*}}}*/

/**
 * Gets the value of the date element from the request and converts it into a
 * unix timestamp.
 *
 * @param Pieform $form    The form the element is attached to
 * @param array   $element The element to get the value for
 */
function pieform_element_date_get_value(Pieform $form, $element) {/*{{{*/
    $name = $element['name'];
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if ($form->is_submitted() && isset($global[$name . '_day']) && isset($global[$name . '_month']) && isset($global[$name . '_year'])) {
        $time = mktime(0, 0, 0, $global[$name . '_month'], $global[$name . '_day'], $global[$name . '_year']);
        if (false === $time) {
            return null;
        }
        return $time;
    }

    return null;
}/*}}}*/


/** helper: used when rendering the element, to get the value for it */
function pieform_element_date_get_timeperiod_value($timeperiod, $min, $max, $element, Pieform $form) {/*{{{*/
    static $lookup = array(
        'year' => 0,
        'month' => 1,
        'day' => 2
    );
    $index = $lookup[$timeperiod];

    if (isset($element['value'][$index])) {
        $value = $element['value'][$index];
        if ($value < $min || $value > $max) {
            $value = $min;
        }
        return $value;
    }

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if (isset($global[$element['name'] . '_' . $timeperiod])) {
        $value = $global[$element['name'] . '_' . $timeperiod];
        if ($value < $min || $value > $max) {
            $value = $min;
        }
        return $value;
    }

    $value = time();

    if (isset($element['defaultvalue'])) {
        $value = $element['defaultvalue'];
    }

    switch ($timeperiod) {
        case 'day':
            $value = date('j', $value);
            break;
        case 'month':
            $value = date('m', $value);
            break;
        case 'year':
            $value = date('Y', $value);
            break;
    }

    return $value;
}/*}}}*/
