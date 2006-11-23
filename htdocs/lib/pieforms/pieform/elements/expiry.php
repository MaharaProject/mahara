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

    // Get the value of the element for rendering.
    if (isset($element['value'])) {
        $seconds = $element['value'];
        $values = pieform_render_expiry_get_expiry_from_seconds($element['value']);
    }
    else if (isset($global[$element['name'] . '_number'])
             && isset($global[$element['name'] . '_units'])) {
        $values = array('number' => $global[$element['name'] . '_number'],
                        'units'  => $global[$element['name'] . '_units']);
        $seconds = $values['number'] * pieform_render_expiry_seconds_in($values['units']);
    }
    else if (isset($element['defaultvalue'])) {
        $seconds = $element['defaultvalue'];
        $values = pieform_render_expiry_get_expiry_from_seconds($seconds);
    }
    else {
        $values = array('number' => '', 'units' => 'noenddate');
        $seconds = null;
    }

    // @todo probably create with an actual input element, as tabindex doesn't work here for one thing
    $numberinput = '<input';
    $numberinput .= $values['units'] == 'noenddate' ? ' disabled="disabled"' : '';
    $numberinput .= ' type="text" size="4" name="' . $name . '_number"';
    $numberinput .= ' id="' . $name . '_number" value="' . $values['number'] . "\">\n";

    $uselect = '<select onchange="' . $name . '_change()" ';
    $uselect .= 'name="' . $name . '_units" id="' . $name . '_units"' .  ">\n";
    foreach (pieform_render_expire_get_expiry_units() as $u) {
        $uselect .= "\t<option value=\"$u\"" . (($values['units'] == $u) ? ' selected="selected"' : '') . '>' . $form->i18n($u) . "</option>\n";
    }
    $uselect .= "</select>\n";

    // Make sure the input is disabled if "no end date" is selected
    $script = <<<EOJS
<script type="text/javascript" language="javascript">
function {$name}_change() {
    if ($('{$name}_units').value == 'noenddate') {
        $('{$name}_number').disabled = true;
    }
    else {
        $('{$name}_number').disabled = false;
    }
}
</script>
EOJS;

    return $numberinput . $uselect . $script;
}

function pieform_render_expire_get_expiry_units() {
    return array('days', 'weeks', 'months', 'years', 'noenddate');
}

function pieform_render_expiry_seconds_in($unit) {
    $dayseconds = 60 * 60 * 24;
    switch ($unit) {
    case 'days'   : return $dayseconds;
    case 'weeks'  : return $dayseconds * 7;
    case 'months' : return $dayseconds * 30;
    case 'years'  : return $dayseconds * 365;
    default       : return null;
    }
}

function pieform_render_expiry_get_expiry_from_seconds($seconds) {
    if ($seconds == null) {
        return array('number' => '', 'units' => 'noenddate');
    }
    // This needs work to produce sensible values; at the moment it will convert
    // 60 days into 2 months; 70 days into 7 weeks, etc.
    $yearseconds = pieform_render_expiry_seconds_in('years');
    if ($seconds % $yearseconds == 0 && $seconds > 0) {
        return array('number' => (int) ($seconds / $yearseconds), 'units' => 'years');
    }
    $monthseconds = pieform_render_expiry_seconds_in('months');
    if ($seconds % $monthseconds == 0 && $seconds > 0) {
        return array('number' => (int) ($seconds / $monthseconds), 'units' => 'months');
    }
    $weekseconds = pieform_render_expiry_seconds_in('weeks');
    if ($seconds % $weekseconds == 0 && $seconds > 0) {
        return array('number' => (int) ($seconds / $weekseconds), 'units' => 'weeks');
    }
    $dayseconds = pieform_render_expiry_seconds_in('days');
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
    $allunits = pieform_render_expire_get_expiry_units();
    $number = $global[$name . '_number'];
    if (!in_array($unit,$allunits) || $number < 0) {
        return null;
    }
    return $number * pieform_render_expiry_seconds_in($unit);
}

function pieform_get_value_js_expiry($element, Pieform $form) {
    $formname = $form->get_name();
    $name = $element['name'];
    return <<<EOF
    data['{$name}_number'] = $('{$name}_number').value;
    data['{$name}_units']  = $('{$name}_units').value;

EOF;
}

?>
