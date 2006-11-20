<?php
/**
 * This program is part of Pieforms
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    pieforms
 * @subpackage element
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Provides a date picker, in the form of three dropdowns.
 *
 * @param array $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string        The HTML for the element
 */
function pieform_render_date($element, Pieform $form) {
    $result = '';
    $name = $element['name'];
    $element['minyear'] = (isset($element['minyear'])) ? intval($element['minyear']) : 1950;
    $element['maxyear'] = (isset($element['maxyear'])) ? intval($element['maxyear']) : 2050;
    if (!isset($element['defaultvalue'])) {
        $element['defaultvalue'] = array(date('Y'), date('m'), date('d'));
    }

    // Year
    $value = pieform_render_select_get_value('year', $element['minyear'], $element['maxyear'],  $element, $form);
    $year = '<select name="' . $name . "_year\">\n";
    for ($i = $element['minyear']; $i <= $element['maxyear']; $i++) {
        $year .= "\t<option value=\"$i\"" . (($value == $i) ? ' selected="selected"' : '') . ">$i</option>\n";
    }
    $year .= "</select>\n";

    $value = pieform_render_select_get_value('month', 1, 12, $element, $form);
    $month = '<select name="' . $name . "_month\">\n";
    for ($i = 1; $i <= 12; $i++) {
        $month .= "\t<option value=\"$i\"" . (($value == $i) ? ' selected="selected"' : '') . '>' . date('M', strtotime("2000-$i-01")) . "</option>\n";
    }
    $month .= "</select>\n";

    $value = pieform_render_select_get_value('day', 1, 31, $element, $form);
    $day = '<select name="' . $name . "_day\">\n";
    for ($i = 1; $i <= 31; $i++) {
        $day .= "\t<option value=\"$i\"" . (($value == $i) ? ' selected="selected"' : '') . ">$i</option>\n";
    }
    $day .= '</select>';

    $result = $year . $month . $day;
    return $result;
}

/** gets the value explicitly from the request */
function pieform_get_value_date($element, Pieform $form) {
    $name = $element['name'];
    $global = ($form->get_method() == 'get') ? $_GET : $_POST;
    $time = mktime(0, 0, 0, $global[$name . '_month'], $global[$name . '_day'], $global[$name . '_year']);
    if (false === $time) {
        return null;
    }
    return $time;
}

function pieform_get_value_js_date($element, Pieform $form) {
    $formname = $form->get_name();
    $name = $element['name'];
    return <<<EOF
    data['{$name}_year']  = document.forms['$formname'].elements['{$name}_year'].value;
    data['{$name}_month'] = document.forms['$formname'].elements['{$name}_month'].value;
    data['{$name}_day']   = document.forms['$formname'].elements['{$name}_day'].value;

EOF;
}

/** helper: used when rendering the element, to get the value for it */
function pieform_render_select_get_value($timeperiod, $min, $max, $element, Pieform $form) {
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

    $global = ($form->get_method() == 'get') ? $_GET : $_POST;
    if (isset($global[$element['name'] . '_' . $timeperiod])) {
        $value = $global[$element['name'] . '_' . $timeperiod];
        if ($value < $min || $value > $max) {
            $value = $min;
        }
        return $value;
    }

    if (isset($element['defaultvalue'][$index])) {
        $value = $element['defaultvalue'][$index];
        if ($value < $min || $value > $max) {
            $value = $min;
        }
        return $value;
    }

    return null;
}

?>
