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
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Provides a duration chooser, with a text box for a number and a
 * select box to choose the units, in days, weeks, months, years, or 'no end date'.
 *
 * @param Pieform $form    The form to render the element for
 * @param array   $element The element to render
 * @return string          The HTML for the element
 */
function pieform_element_expiry(Pieform $form, $element) {/*{{{*/
    $formname = $form->get_name();
    $result = '';
    $name = Pieform::hsc($element['name']);
    if (!isset($element['defaultvalue'])) {
        $element['defaultvalue'] = null;
    }

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    // Get the value of the element for rendering.
    if (isset($element['value'])) {
        $seconds = $element['value'];
        $values = pieform_element_expiry_get_expiry_from_seconds($element['value']);
    }
    else if ($form->is_submitted()
             && isset($global[$element['name']])
             && isset($global[$element['name'] . '_units'])) {
        $values = array('number' => $global[$element['name']],
                        'units'  => $global[$element['name'] . '_units']);
        $seconds = $values['number'] * pieform_element_expiry_seconds_in($values['units']);
    }
    else if (isset($element['defaultvalue'])) {
        $seconds = $element['defaultvalue'];
        $values = pieform_element_expiry_get_expiry_from_seconds($seconds);
    }
    else {
        $values = array('number' => '', 'units' => 'noenddate');
        $seconds = null;
    }

    // @todo probably create with an actual input element, as tabindex doesn't work here for one thing
    // Same with the select. And do the events using mochikit signal instead of dom events
    $numberinput = '<input';
    $numberinput .= ($values['units'] == 'noenddate' && empty($element['rules']['required'])) ? ' disabled="disabled"' : '';
    $numberinput .= ' type="text" size="4" name="' . $name . '"';
    $numberinput .= ' id="' . $formname . '_' . $name . '" value="' . Pieform::hsc($values['number']) . '" tabindex="' . Pieform::hsc($element['tabindex']) . '"';
    $numberinput .= (isset($element['error']) ? ' class="error"' : '') . ">\n";

    $uselect = '<select onchange="' . $name . '_change()" ';
    $uselect .= 'name="' . $name . '_units" id="' . $formname . '_' . $name . '_units"' . ' tabindex="' . Pieform::hsc($element['tabindex']) . "\">\n";
    foreach (pieform_element_expire_get_expiry_units() as $u) {
        // Don't allow 'no end date' if the element is required
        if ($u == 'noenddate' && !empty($element['rules']['required'])) {
            continue;
        }

        $uselect .= "\t<option value=\"$u\"" . (($values['units'] == $u) ? ' selected="selected"' : '') . '>'
            . $form->i18n('element', 'expiry', $u, $element) . "</option>\n";
    }
    $uselect .= "</select>\n";

    // Make sure the input is disabled if "no end date" is selected
    $script = <<<EOJS
<script type="text/javascript" language="javascript">
function {$name}_change() {
    if ($('{$formname}_{$name}_units').value == 'noenddate') {
        $('{$formname}_{$name}').disabled = true;
    }
    else {
        $('{$formname}_{$name}').disabled = false;
    }
}
</script>
EOJS;

    return $numberinput . $uselect . $script;
}/*}}}*/

/**
 * Gets the value of the expiry element and converts it to a time in seconds.
 *
 * @param Pieform $form    The form the element is attached to
 * @param array   $element The element to get the value for
 * @return int             The number of seconds until expiry
 */
function pieform_element_expiry_get_value(Pieform $form, $element) {/*{{{*/
    $name = $element['name'];
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    $unit = $global[$name . '_units'];
    if ($unit == 'noenddate') {
        return null;
    }
    $allunits = pieform_element_expire_get_expiry_units();
    $number = $global[$name];
    if (!in_array($unit,$allunits) || $number < 0) {
        return null;
    }
    return $number * pieform_element_expiry_seconds_in($unit);
}/*}}}*/

function pieform_element_expire_get_expiry_units() {/*{{{*/
    return array('days', 'weeks', 'months', 'years', 'noenddate');
}/*}}}*/

function pieform_element_expiry_seconds_in($unit) {/*{{{*/
    $dayseconds = 60 * 60 * 24;
    switch ($unit) {
        case 'days'   : return $dayseconds;
        case 'weeks'  : return $dayseconds * 7;
        case 'months' : return $dayseconds * 30;
        case 'years'  : return $dayseconds * 365;
        default       : return null;
    }
}/*}}}*/

function pieform_element_expiry_get_expiry_from_seconds($seconds) {/*{{{*/
    if ($seconds == null) {
        return array('number' => '', 'units' => 'noenddate');
    }
    // This needs work to produce sensible values; at the moment it will convert
    // 60 days into 2 months; 70 days into 7 weeks, etc.
    $yearseconds = pieform_element_expiry_seconds_in('years');
    if ($seconds % $yearseconds == 0 && $seconds > 0) {
        return array('number' => (int) ($seconds / $yearseconds), 'units' => 'years');
    }
    $monthseconds = pieform_element_expiry_seconds_in('months');
    if ($seconds % $monthseconds == 0 && $seconds > 0) {
        return array('number' => (int) ($seconds / $monthseconds), 'units' => 'months');
    }
    $weekseconds = pieform_element_expiry_seconds_in('weeks');
    if ($seconds % $weekseconds == 0 && $seconds > 0) {
        return array('number' => (int) ($seconds / $weekseconds), 'units' => 'weeks');
    }
    $dayseconds = pieform_element_expiry_seconds_in('days');
    if ($seconds % $dayseconds == 0) {
        return array('number' => (int) ($seconds / $dayseconds), 'units' => 'days');
    }
    return null;
}/*}}}*/
