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
 * @package    pieform
 * @subpackage element
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Provides a duration chooser, with a text box for a number and a
 * select box to choose the units, in days, weeks, months, years, or 'no end date'.
 *
 * @param array $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string        The HTML for the element
 */
function pieform_render_expiry($element, Pieform $form) {
    $result = '';
    $name = $element['name'];
    if (!isset($element['defaultvalue'])) {
        $element['defaultvalue'] = null;
    }

    $global = ($form->get_method() == 'get') ? $_GET : $_POST;

    // Get the value of the element for rendering.  The values of the
    // two inputs are rendered, and the total time in seconds is
    // stored in a hidden input.

    if (isset($element['value'])) {
        $seconds = $element['value'];
        $values = get_expiry_from_seconds($element['value']);
    }
    else if (isset($global[$element['name'] . '_number'])
             && isset($global[$element['name'] . '_units'])) {
        $values = array('number' => $global[$element['name'] . '_number'],
                        'units'  => $global[$element['name'] . '_units']);
        $seconds = $values['number'] * seconds_in($values['units']);
    }
    else if (isset($element['defaultvalue'])) {
        $seconds = $element['defaultvalue'];
        $values = get_expiry_from_seconds($seconds);
    }
    else {
        $values = array('number' => '', 'units' => 'noenddate');
        $seconds = null;
    }

    // @todo probably create with an actual input element, as tabindex doesn't work here for one thing
    $numberinput = '<input ';
    if ($form->get_ajaxpost()) {
        $numberinput .= 'onchange="' . $name . '_change()"';
    }
    $numberinput .= $values['units'] == 'noenddate' ? ' disabled="disabled"' : '';
    $numberinput .= 'type="text" size="4" ' . 'name="' . $name . '_number" ';
    $numberinput .= 'id="' . $name . '_number" value="' . $values['number'] . "\">\n";

    $allunits = get_expiry_units();

    $uselect = '<select onchange="' . $name . '_change()" ';
    $uselect .= 'name="' . $name . '_units" id="' . $name . '_units"' .  ">\n";
    foreach ($allunits as $u) {
        $uselect .= "\t<option value=\"$u\"" . (($values['units'] == $u) ? ' selected="selected"' : '') . '>' . $form->i18n($u) . "</option>\n";
    }
    $uselect .= "</select>\n";

    // The hidden input contains the value of the expiry in seconds
    $hidden = '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $seconds . "\">\n";

    // Every time one of the two inputs is changed, update the number
    // of seconds in the hidden input.
    $script = <<<EOJS
<script type="text/javascript" language="javascript">
function {$name}_change() {

EOJS;
    /*
    if ($form->get_ajaxpost()) {
        $script .= <<<EOJS
    var seconds = null;
    if ($('{$name}_number').value > 0) {
        var mult = $('{$name}_number').value * 60 * 60 * 24;
        if ($('{$name}_units').value == 'days') {
            seconds = mult;
        } else if ($('{$name}_units').value == 'weeks') {
            seconds = mult * 7;
        } else if ($('{$name}_units').value == 'months') {
            seconds = mult * 30;
        } else if ($('{$name}_units').value == 'years') {
            seconds = mult * 365;
        }
    }
    else {
        seconds = 0;
    }
    $('{$name}').value = seconds;

EOJS;
    }
    */

    $script .= <<<EOJS
    if ($('{$name}_units').value == 'noenddate') {
        $('{$name}_number').disabled = true;
    }
    else {
        $('{$name}_number').disabled = false;
    }
}
</script>
EOJS;

    return $numberinput . $uselect . $hidden . $script;
}

function get_expiry_units() {
    return array('days','weeks','months','years','noenddate');
}

function seconds_in($unit) {
    $dayseconds = 60 * 60 * 24;
    switch ($unit) {
    case 'days'   : return $dayseconds;
    case 'weeks'  : return $dayseconds * 7;
    case 'months' : return $dayseconds * 30;
    case 'years'  : return $dayseconds * 365;
    default       : return null;
    }
}

function get_expiry_from_seconds($seconds) {
    if ($seconds == null) {
        return array('number' => '', 'units' => 'noenddate');
    }
    // This needs work to produce sensible values; at the moment it will convert
    // 60 days into 2 months; 70 days into 7 weeks, etc.
    $yearseconds = seconds_in('years');
    if ($seconds % $yearseconds == 0 && $seconds > 0) {
        return array('number' => (int) ($seconds / $yearseconds), 'units' => 'years');
    }
    $monthseconds = seconds_in('months');
    if ($seconds % $monthseconds == 0 && $seconds > 0) {
        return array('number' => (int) ($seconds / $monthseconds), 'units' => 'months');
    }
    $weekseconds = seconds_in('weeks');
    if ($seconds % $weekseconds == 0 && $seconds > 0) {
        return array('number' => (int) ($seconds / $weekseconds), 'units' => 'weeks');
    }
    $dayseconds = seconds_in('days');
    if ($seconds % $dayseconds == 0) {
        return array('number' => (int) ($seconds / $dayseconds), 'units' => 'days');
    }
    return null;
}

// /** gets the value explicitly from the request */
function pieform_get_value_expiry($element, Pieform $form) {
    $name = $element['name'];
    $global = ($form->get_method() == 'get') ? $_GET : $_POST;
    //return $global[$name];
    $unit = $global[$name . '_units'];
    if ($unit == 'noenddate') {
        return null;
    }
    $allunits = get_expiry_units();
    $number = $global[$name . '_number'];
    if (!in_array($unit,$allunits) || $number < 0) {
        return null;
    }
    return $number * seconds_in($unit);
}

function pieform_get_value_js_expiry($element, Pieform $form) {
    $formname = $form->get_name();
    $name = $element['name'];
    return <<<EOF
    var seconds = null;
    //if ($('{$name}_units').value == 'noenddate') {
    //    seconds = null;
    //}
    //else {
    //    if ($('{$name}_number').value > 0) {
    //        var mult = $('{$name}_number').value * 60 * 60 * 24;
    //        if ($('{$name}_units').value == 'days') {
    //            seconds = mult;
    //        } else if ($('{$name}_units').value == 'weeks') {
    //            seconds = mult * 7;
    //        } else if ($('{$name}_units').value == 'months') {
    //            seconds = mult * 30;
    //        } else if ($('{$name}_units').value == 'years') {
    //            seconds = mult * 365;
    //        }
    //    }
    //    else {
    //        seconds = 0;
    //    }
    //}
    //data['{$name}'] = seconds;
    data['{$name}_number'] = $('{$name}_number').value;
    data['{$name}_units']  = $('{$name}_units').value;

EOF;
}

?>
