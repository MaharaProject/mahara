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
 * Provides a javascript calendar for inputting a date/time.
 *
 * General documentation about the calendar is available at
 * http://api.jqueryui.com/datepicker/
 * General documentation about the timepicker addon is available at
 * http://trentrichardson.com/examples/timepicker/
 *
 * @param Pieform $form    The form to render the element for
 * @param array   $element The element to render
 * @return string          The HTML for the element
 */
function pieform_element_calendar(Pieform $form, $element) {/*{{{*/
    global $LANGDIRECTION;

    $id = $form->get_name() . '_' . $element['name'];
    $value = $form->get_value($element);
    if ($value) {
        $value = Pieform::hsc(strftime($element['caloptions']['ifFormat'], $value));
    }

    // Build the configuring javascript
    $options = array_merge($element['caloptions'], array('inputField' => $id));
    if (empty($options['dateFormat'])) {
        $options['dateFormat'] = get_string('calendar_dateFormat', 'langconfig');
    }
    // Set up default timeFormat if needed
    if (!empty($options['showsTime']) && empty($options['timeFormat'])) {
        $options['timeFormat'] = get_string('calendar_timeFormat', 'langconfig');
    }
    $options = pieform_element_calendar_get_lang_strings($options, $LANGDIRECTION);
    // Build the HTML
    $result = '<input type="text"'
        . $form->element_attributes($element)
        . ' value="' . $value . '">';
    $result .= '<script type="text/javascript">
        var input = jQuery("input#' . $id . '");';
    if (!empty($options['showsTime'])) {
        $result .= 'input.datetimepicker({';
    }
    else {
        $result .= 'input.datepicker({';
    }
    $result .= ' onSelect: function(date) {
                     if (typeof formchangemanager !== \'undefined\') {
                         var form = input.closest(\'form\')[0];
                         formchangemanager.setFormState(form, FORM_CHANGED);
                     }
                 },';
    foreach ($options as $key => $option) {
        if (is_numeric($option)) {
            $result .= $key . ': ' . $option . ',';
        }
        else if (is_array($option)) {
            foreach ($option as $k => $v) {
                if (!is_numeric($v)) {
                    if (preg_match('/^\'(.*)\'$/', $v, $match)) {
                        $v = $match[1];
                    }
                    $option[$k] = json_encode($v);
                }
            }
            $option = '[' . implode(',', $option) . ']';
            $result .= $key . ': ' . $option . ',';
        }
        else {
            $result .= $key . ': ' . json_encode($option) . ',';
        }
    }
    // Adding prev / next year buttons
    $result .= '
    beforeShow: function(input, inst) {
        setTimeout(function() {
            add_prev_next_year(inst);
        }, 1);
    },
    onChangeMonthYear: function(y, m, inst) {
        setTimeout(function() {
            add_prev_next_year(inst);
        }, 1);

    },
';
    if (isset($element['imagefile'])) {
        $result .= 'showOn: "button",
                    buttonImage: "' . $element['imagefile'] . '",
                    buttonText: "' . get_string('element.calendar.opendatepicker', 'pieforms') . '",';
    }
    $result .= '
        });
    </script>';

    return $result;
}/*}}}*/

/**
 * Sets default attributes of the calendar element.
 *
 * @param array $element The element to configure
 * @return array         The configured element
 */
function pieform_element_calendar_set_attributes($element) {/*{{{*/
    $element['jsroot']   = isset($element['jsroot']) ? $element['jsroot'] : '';
    $element['language'] = isset($element['language']) ? $element['language'] : 'en';
    $element['theme']    = isset($element['theme']) ? $element['theme'] : 'raw';
    $element['caloptions']['ifFormat'] = isset($element['caloptions']['ifFormat']) ? $element['caloptions']['ifFormat'] : '%Y/%m/%d';
    $element['caloptions']['dateFormat'] = isset($element['caloptions']['dateFormat']) ? $element['caloptions']['dateFormat'] : get_string('calendar_dateFormat', 'langconfig');
    $element['caloptions']['timeFormat'] = isset($element['caloptions']['timeFormat']) ? $element['caloptions']['timeFormat'] : get_string('calendar_timeFormat', 'langconfig');

    return $element;
}/*}}}*/

/**
 * Returns code to go in <head> for the given calendar instance
 *
 * @param array $element The element to get <head> code for
 * @return array         An array of HTML elements to go in the <head>
 */
function pieform_element_calendar_get_headdata($element) {/*{{{*/
    if (isset($element['themefile'])) {
        $themefile = $element['themefile'];
    }
    else if (isset($element['theme'])) {
        if (file_exists(get_config('docroot') . 'theme/' . $element['theme'] . '/static/style/datepicker.css')) {
            $themefile = get_config('wwwroot') . 'theme/' . $element['theme'] . '/static/style/datepicker.css';
        }
        else {
            throw new PieformException('No theme file for calendar "' . $element['name'] . '": please make sure themefile "' . get_config('docroot') . 'theme/' . $element['theme'] . '/static/style/datepicker.css" exists');
        }
    }
    else {
        throw new PieformException('No theme chosen for calendar "' . $element['name'] . '": please set themefile or theme');
    }

    $libjs = $element['jsroot'] . 'js/jquery-ui-1.10.2.min.js';
    $libcss = $element['jsroot'] . 'css/ui-lightness/jquery-ui-1.10.2.min.css';
    $timeaddonjs  = $element['jsroot'] . 'js/jquery-ui-timepicker-addon.js';
    $prev = get_string('datepicker_prevText');
    $next = get_string('datepicker_nextText');
    $extrajs = <<<EOF
/**
 * Add the prev and next year button to a datepicker
 */
function add_prev_next_year(inst) {
    var widgetHeader = jQuery("#ui-datepicker-div").find(".ui-datepicker-header");
    var prevYrBtn = jQuery('<a class="ui-datepicker-prev-year ui-corner-all" title="$prev"><span class="ui-icon ui-icon-circle-triangle-wy">$prev</span></a>');
    prevYrBtn.unbind("click").bind("click", function() {
                jQuery.datepicker._adjustDate(inst.input, -1, "Y");
    }).hover(function() { \$j(this).addClass('ui-datepicker-prev-year-hover ui-state-hover')},
             function() { \$j(this).removeClass('ui-datepicker-prev-year-hover ui-state-hover')});
    var nextYrBtn = jQuery('<a class="ui-datepicker-next-year ui-corner-all" title="$next"><span class="ui-icon ui-icon-circle-triangle-ey">$next</span></a>');
    nextYrBtn.unbind("click").bind("click", function() {
                jQuery.datepicker._adjustDate(inst.input, +1, "Y");
    }).hover(function() { \$j(this).addClass('ui-datepicker-next-year-hover ui-state-hover')},
             function() { \$j(this).removeClass('ui-datepicker-next-year-hover ui-state-hover')});
    nextYrBtn.prependTo(widgetHeader);
    prevYrBtn.prependTo(widgetHeader);
}
EOF;
    $result = array(
        '<link rel="stylesheet" type="text/css" media="all" href="' . append_version_number($libcss) . '">',
        '<link rel="stylesheet" type="text/css" media="all" href="' . append_version_number($themefile) . '">',
        '<script type="text/javascript" src="' . append_version_number($libjs) . '"></script>',
        '<script type="text/javascript" src="' . append_version_number($timeaddonjs) . '"></script>',
        '<script type="text/javascript">' . $extrajs . '</script>',
    );
    return $result;
}/*}}}*/

/**
 * Retrieves the value of the calendar as a unix timestamp
 *
 * @param Pieform $form    The form the element is attached to
 * @param array   $element The element to get the value for
 * @return int             The unix timestamp represented by the calendar
 */
function pieform_element_calendar_get_value(Pieform $form, $element) {/*{{{*/
    $name = $element['name'];
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    if (isset($element['value'])) {
        return $element['value'];
    }

    if ($form->is_submitted() && isset($global[$name])) {
        if (trim($global[$name]) == '') {
            return null;
        }

        $value = strtotime($global[$name]);

        if ($value === false) {
            $form->set_error($name, $form->i18n('element', 'calendar', 'invalidvalue', $element));
            return null;
        }
        return $value;
    }

    if (isset($element['defaultvalue'])) {
        return $element['defaultvalue'];
    }

    return null;
}/*}}}*/

/**
 * Retrieves the values of the internationalised strings for a calendar
 * The $form is not passed in so that we can fetch this array from outside a pieform
 * on the viewacl.tpl
 *
 * @param array   $options The datepicker options array
 * @return array  $options The datepicker options array with the new lang strings added
 */
function pieform_element_calendar_get_lang_strings($options, $langdirection = 'ltr') {/*{{{*/
    // Set up internationalisation
    $lang_options = array('clearText','closeText','closeStatus','prevText','prevStatus',
                          'nextText','nextStatus','currentText','currentStatus',
                          'monthNames','monthNamesShort','monthStatus',
                          'yearStatus','weekHeader','weekStatus',
                          'dayNames','dayNamesShort','dayNamesMin','dayStatus',
                          'dateStatus','initStatus',
                          'timeOnlyTitle', 'timeText', 'hourText', 'minuteText', 'secondText',
                          'millisecText', 'timezoneText', 'amNames', 'pmNames');
    foreach ($lang_options as $lang_option) {
        $langopt = get_string('datepicker_' . $lang_option);
        if (preg_match('/^\[(.*)\]$/', $langopt, $match)) {
            $langopt = explode(',', $match[1]);
        }
        $options[$lang_option] = $langopt;
    }
    $options['isRTL'] = ($langdirection == 'rtl') ? true : false;
    return $options;
}/*}}}*/