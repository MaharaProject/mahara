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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Provides a javascript calendar for inputting a date.
 *
 * General documentation about the calendar is available at
 * http://www.dynarch.com/static/jscalendar-1.0/doc/html/reference.html
 *
 * @param Pieform $form    The form to render the element for
 * @param array   $element The element to render
 * @return string          The HTML for the element
 */
function pieform_element_calendar(Pieform $form, $element) {/*{{{*/
    $id = $form->get_name() . '_' . $element['name'];
    $value = $form->get_value($element);
    if ($value) {
        $value = Pieform::hsc(strftime($element['caloptions']['ifFormat'], $value));
    }

    // Build the HTML
    $result = '<input type="text"'
        . $form->element_attributes($element)
        . ' value="' . $value . '">';
    if (isset($element['imagefile'])) {
        $result .= '<a href="" id="'. $id . '_btn" onclick="return false;" class="pieform-calendar-toggle"'
            . ' tabindex="' . $element['tabindex'] . '">'
            . '<img src="' . $element['imagefile'] . '" alt=""></a>';
    }
    else {
        $result .= '<input type="button" id="' . $id . '_btn" onclick="return false;" class="pieform-calendar-toggle"'
            . ' value="..." tabindex="' . $element['tabindex'] . '">';
    }

    // Build the configuring javascript
    $options = array_merge($element['caloptions'], array('inputField' => $id, 'button' => $id . '_btn'));

    $encodedoptions = json_encode($options);
    // Some options are callbacks and need their quoting removed
    foreach (array('dateStatusFunc', 'flatCallback', 'onSelect', 'onClose', 'onUpdate') as $function) {
        $encodedoptions = preg_replace('/("' . $function . '"):"([a-zA-Z0-9$]+)"/', '\1:\2', $encodedoptions);
    }
    $result .= '<script type="text/javascript">Calendar.setup(' . $encodedoptions . ');</script>';

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
    $element['theme']    = isset($element['theme']) ? $element['theme'] : 'calendar-win2k-2';
    $element['caloptions']['ifFormat'] = isset($element['caloptions']['ifFormat']) ? $element['caloptions']['ifFormat'] : '%Y/%m/%d';
    $element['caloptions']['daFormat'] = isset($element['caloptions']['daFormat']) ? $element['caloptions']['daFormat'] : '%Y/%m/%d';
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
        $themefile = $element['jsroot'] . $element['theme'] . '.css';
    }
    else {
        throw new PieformException('No theme chosen for calendar "' . $element['name'] . '": please set themefile or theme');
    }
    $libfile   = $element['jsroot'] . 'calendar_stripped.js';
    $langfile  = $element['jsroot'] . 'lang/calendar-' . $element['language'] . '.js';
    $setupfile = $element['jsroot'] . 'calendar-setup_stripped.js';
    $result = array(
        '<link rel="stylesheet" type="text/css" media="all" href="' . $themefile . '">',
        '<script type="text/javascript" src="' . $libfile . '"></script>',
        '<script type="text/javascript" src="' . $langfile . '"></script>',
        '<script type="text/javascript" src="' . $setupfile . '"></script>'
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
