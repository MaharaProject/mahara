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
function pieform_element_calendar(Pieform $form, $element) {
    global $LANGDIRECTION;

    $id = $form->get_name() . '_' . $element['name'];

    // Build the configuring javascript
    $options = array_merge($element['caloptions'], array('inputField' => $id));
    $options['dateFormat'] = pieform_element_calendar_convert_dateformat(get_string('pieform_calendar_dateformat', 'langconfig'));
    $options['timeFormat'] = pieform_element_calendar_convert_timeformat(get_string('pieform_calendar_timeformat', 'langconfig'));
    $value = $form->get_value($element);
    if ($value) {
        if (!empty($options['showsTime'])) {
            $format = get_string('pieform_calendar_dateformat', 'langconfig') . ' ' . get_string('pieform_calendar_timeformat', 'langconfig');
        }
        else {
            $format = get_string('pieform_calendar_dateformat', 'langconfig');
        }
        $value = Pieform::hsc(strftime($format, $value));
    }

    // Build the HTML
    $element['class'] .= " datetimepicker-input";
    $result = '<span class="hasDatepickerwrapper"><input type="text"'
        . $form->element_attributes($element, array('id'))
        . ' id="' . $id . '"'
        . ' value="' . $value . '"'
        . ' autocomplete="off"'
        . ' data-toggle="datetimepicker" data-target="#' . $id . '"'
        . ' aria-label="' . get_string('element.calendar.format.arialabel', 'pieforms') . '"
        ></span>';
    $result .= '
        <script>
        var input_' . $id . ' = jQuery("input#' . $id . '");
        ';
    if (!empty($options['showsTime'])) {
        $result .= 'input_' . $id . '.datetimepicker({
            format: "' . $options['dateFormat'] . ' ' . $options['timeFormat'] . '",';
    }
    else {
        $result .= 'input_' . $id . '.datetimepicker({
            format: "' . $options['dateFormat'] . '",';
    }
    $tooltips = json_encode(pieform_element_calendar_tooltip_lang_strings());
    if ($value) {
        $result .= '
            date: moment("' . $value . '", "' . $options['dateFormat'] . '"),';
    }
    $result .= '
        locale: "' . strstr(current_language(), '.', true) . '",
        useCurrent: false,
        buttons: {
            showClear: true,
            showToday: true,
        },
        tooltips: ' . $tooltips . ',
        icons: {
            time: "icon icon-clock-o",
            date: "icon icon-calendar",
            up: "icon icon-arrow-up",
            down: "icon icon-arrow-down",
            previous: "icon icon-chevron-left",
            next: "icon icon-chevron-right",
            close: "icon icon-times",
            clear: "icon icon-trash",
            today: "icon icon-crosshairs",
        },
    });

    input_' . $id . '.on("hide.datetimepicker", function(selectedDate) {
        if (typeof formchangemanager !== \'undefined\') {
            var form = input_' . $id . '.closest(\'form\')[0];
            formchangemanager.setFormState(form, FORM_CHANGED);
        }
    });
    </script>';

    return $result;
}

/**
 * Returns a (hopefully) human-readable version of the date format. To be used in help strings.
 * @return string
 */
function pieform_element_calendar_human_readable_dateformat() {
    static $formatstring = null;
    if ($formatstring) {
        return $formatstring;
    }

    $replacements = array(
        '%e' => get_string('element.calendar.format.help.dayofmonth1digit', 'pieforms'),
        '%d' => get_string('element.calendar.format.help.dayofmonth2digits', 'pieforms'),
        '%m' => get_string('element.calendar.format.help.month2digit', 'pieforms'),
        '%y' => get_string('element.calendar.format.help.year2digit', 'pieforms'),
        '%Y' => get_string('element.calendar.format.help.year4digit', 'pieforms'),
    );

    $formatstring = str_replace(
        array_keys($replacements),
        array_values($replacements),
        get_string('pieform_calendar_dateformat', 'langconfig')
    );

    return $formatstring;
}

/**
 * Returns a (hopefully) human-readable version of the time format. To be used in help strings.
 * @return string
 */
function pieform_element_calendar_human_readable_timeformat() {
    static $formatstring = null;
    if ($formatstring) {
        return $formatstring;
    }

    $replacements = array(
        '%k' => get_string('element.calendar.format.help.24hour1digit', 'pieforms'),
        '%H' => get_string('element.calendar.format.help.24hour2digits', 'pieforms'),
        '%l' => get_string('element.calendar.format.help.12hour1digit', 'pieforms'),
        '%I' => get_string('element.calendar.format.help.12hour2digits', 'pieforms'),
        '%M' => get_string('element.calendar.format.help.minute2digits', 'pieforms'),
        '%S' => get_string('element.calendar.format.help.second2digits', 'pieforms'),
        '%P' => get_string('element.calendar.format.help.ampmlowercase', 'pieforms'),
        '%p' => get_string('element.calendar.format.help.ampmuppercase', 'pieforms'),
    );

    $formatstring = str_replace(
        array_keys($replacements),
        array_values($replacements),
        get_string('pieform_calendar_timeformat', 'langconfig')
    );

    return $formatstring;
}

/**
 * Returns a (hopefully) human-readable version of the date & time format. To be used in help strings.
 * @return string
 */
function pieform_element_calendar_human_readable_datetimeformat() {
    return pieform_element_calendar_human_readable_dateformat() . ' ' . pieform_element_calendar_human_readable_timeformat();
}

/**
 * Converts a date format string from PHP strftime format to
 * JQuery UI calendar format. (Only covers basic formatting options shared
 * in common between the two formats.)
 *
 * strftime: http://php.net/strftime
 * JQuery UI calendar: http://api.jqueryui.com/datepicker/#utility-formatDate
 *
 * @param string $format A date format in PHP strftime format
 * @return string The equivalent format in JQuery UI calendar format
 */
function pieform_element_calendar_convert_dateformat($format) {
    // We typically use doubled percentage marks in our lang strings because
    // they get passed through printf.
    $format = str_replace('%%', '%', $format);

    $replacements = array(
            '%e' => 'd',  // day of month (no leading zero)
            '%d' => 'DD', // day of month (two digit)
            '%m' => 'MM', // month of year (two digit)
            '%y' => 'y',  // year (two digit)
            '%Y' => 'YYYY', // year (four digit)
    );
    return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $format
    );
}

/**
 * Converts a time format string from PHP strftime format to
 * JQuery UI timepicker format. (Only covers basic formatting options shared
 * in common between the two formats.)
 *
 * strftime: http://php.net/strftime
 * JQuery UI timepicker: http://trentrichardson.com/examples/timepicker/
 *
 * @param string $format A time format in PHP strftime format
 * @return string The equivalent format in JQuery UI timepicker format
 */
function pieform_element_calendar_convert_timeformat($format) {
    // We typically use doubled percentage marks in our lang strings because
    // they get passed through printf.
    $format = str_replace('%%', '%', $format);

    // Replacements as per http://momentjs.com/docs/#/displaying/format/
    $replacements = array(
            '%k' => "H", // Hour (24-hour, no leading 0)
            '%H' => 'HH', // Hour (24-hour, 2 digits)
            '%l' => "h", // Hour (12-hour, no leading 0)
            '%I' => 'hh', // Hour (12-hour, 2 digits)
            '%M' => 'mm', // Minute (2 digits)
            '%S' => 'ss', // Second (2 digits)
            '%P' => 'a', // am or pm for AM/PM
            '%p' => 'A', // AM or PM for AM/PM
    );
    return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $format
    );
}

/**
 * Sets default attributes of the calendar element.
 *
 * @param array $element The element to configure
 * @return array         The configured element
 */
function pieform_element_calendar_set_attributes($element) {
    global $THEME;
    $element['jsroot']   = get_config('wwwroot') . 'js/jquery/jquery-ui/';
    $element['language'] = substr(current_language(), 0, 2);
    if (!isset($element['caloptions']['showsTime'])) {
        $element['caloptions']['showsTime'] = true;
    }
    return $element;
}

/**
 * Returns code to go in <head> for the given calendar instance
 *
 * @param array $element The element to get <head> code for
 * @return array         An array of HTML elements to go in the <head>
 */
function pieform_element_calendar_get_headdata($element) {
    global $THEME;

    $libjs = $element['jsroot'] . 'js/jquery-ui.min.js';
    $libcss = $element['jsroot'] . 'css/smoothness/jquery-ui.min.css';
    $bootstrapdatetimejs = get_config('wwwroot') . 'js/bootstrap-datetimepicker/tempusdominus-bootstrap-4.js';
    $momentjs = get_config('wwwroot') . 'js/momentjs/moment-with-locales.min.js';
    $prev = get_string('datepicker_prevText');
    $next = get_string('datepicker_nextText');
    $result = array(
        '<link rel="stylesheet" type="text/css" media="all" href="' . append_version_number($libcss) . '">',
        '<script src="' . append_version_number($libjs) . '"></script>',
        '<script src="' . append_version_number($momentjs) . '"></script>',
        '<script src="' . append_version_number($bootstrapdatetimejs) . '"></script>'
    );
    return $result;
}

/**
 * Retrieves the value of the calendar as a unix timestamp
 *
 * @param Pieform $form    The form the element is attached to
 * @param array   $element The element to get the value for
 * @return int             The unix timestamp represented by the calendar
 */
function pieform_element_calendar_get_value(Pieform $form, $element) {
    $name = $element['name'];
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    if (isset($element['value'])) {
        return $element['value'];
    }

    if ($form->is_submitted() && isset($global[$name])) {
        if (trim($global[$name]) == '') {
            return null;
        }

        $value = pieform_element_calendar_convert_to_epoch($global[$name]);
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
}

/**
 * Convert the user-submitted string from the calendar input, into a Unix epoch.
 * If it can't do the conversion, it returns boolean false.
 *
 * @param string $date
 * @return integer|false
 */
function pieform_element_calendar_convert_to_epoch($date) {
    $value = false;

    // If they're using a "dmy" format, replace the separators with dots to tell strtotime() it's not mdy.
    // (See http://php.net/manual/en/function.strtotime.php#refsect1-function.strtotime-notes)
    $dateformat = get_string('pieform_calendar_dateformat', 'langconfig');
    if (preg_match('/%[ed].*%[m].*%[yY]/', $dateformat)) {
        $timesuffix = preg_match('/(am|pm)$/i', $date, $match);
        $fixdate = preg_replace('/[^0-9]/', '.', $date);
        if ($timesuffix) {
            $fixdate = preg_replace('/[^\d](\.+)$/', $match[1], $fixdate);
        }
        $value = strtotime($fixdate);
    }

    // If that didn't work, then just try doing strtotime on the plain value
    if ($value === false) {
        $value = strtotime($date);
    }

    // And if that still didn't work, then maybe langconfig has an mdy format, but the user entered
    // a dmy format. So try it one more time, replacing the separators with dots.
    if ($value === false) {
        $value = strtotime(preg_replace('/[^0-9]/', '.', $date));
    }

    return $value;
}

/**
 * Retrieves the values of the internationalised tooltip strings for a calendar
 * The $form is not passed in so that we can fetch this array from outside a pieform
 * on the viewacl.tpl
 *
 * @return array  $tooltips The datepicker tooltip options array with the new lang strings added
 */
function pieform_element_calendar_tooltip_lang_strings() {
    $tooltips = array();
    $tooltip_options = array(
        'today', 'clear', 'close', 'selectMonth', 'prevMonth', 'nextMonth', 'selectYear', 'prevYear', 'nextYear',
        'selectDecade', 'prevDecade', 'nextDecade', 'prevCentury', 'nextCentury',
        'pickHour', 'incrementHour', 'decrementHour', 'pickMinute', 'incrementMinute', 'decrementMinute',
        'pickSecond', 'incrementSecond', 'decrementSecond', 'togglePeriod', 'selectTime');
    foreach ($tooltip_options as $tooltip) {
        if (string_exists('datepicker_' . $tooltip)) {
            $tooltips[$tooltip] = get_string('datepicker_' . $tooltip);
        }
    }
    return $tooltips;
}
