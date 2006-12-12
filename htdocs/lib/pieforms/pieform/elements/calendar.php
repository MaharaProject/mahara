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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Provides a javascript calendar for inputting a date.
 *
 * General documentation about the calendar is available at
 * http://www.dynarch.com/demos/jscalendar/doc/html/reference.html
 *
 * @param array   $element The element to render
 * @param Pieform $form    The form to render the element for
 * @return string          The HTML for the element
 */
function pieform_render_calendar($element, Pieform $form) {
    $id = $form->get_name() . '_' . $element['name'];
    $result = '<input type="text"'
        . $form->element_attributes($element)
        . ' value="' . Pieform::hsc($form->get_value($element)) . '">';
    if (isset($element['imagefile'])) {
        $result .= '<a href="" id="'. $id . '_btn" onclick="return false;" class="pieform-calendar-toggle">'
            . '<img src="' . $element['imagefile'] . '" alt=""></a>';
    }
    else {
        $result .= '<input type="button" id="' . $id . '_btn" onclick="return false;" class="pieform-calendar-toggle" value="...">';
    }

    $options = array_merge($element['caloptions'], array('inputField' => $id, 'button' => $id . '_btn'));

    $encodedoptions = json_encode($options);
    // Some options are callbacks and need their quoting removed
    foreach (array('dateStatusFunc', 'flatCallback', 'onSelect', 'onClose', 'onUpdate') as $function) {
        $encodedoptions = preg_replace('/("' . $function . '"):"([a-zA-Z0-9$]+)"/', '\1:\2', $encodedoptions);
    }
    $result .= '<script type="text/javascript">Calendar.setup(' . $encodedoptions . ');</script>';
    return $result;
}

function pieform_render_calendar_set_attributes($element) {
    $element['jsroot']   = isset($element['jsroot']) ? $element['jsroot'] : '';
    $element['language'] = isset($element['language']) ? $element['language'] : 'en';
    $element['theme']    = isset($element['theme']) ? $element['theme'] : 'calendar-win2k-2';
    $element['caloptions']['ifFormat'] = isset($element['caloptions']['ifFormat']) ? $element['caloptions']['ifFormat'] : '%Y/%m/%d';
    $element['caloptions']['daFormat'] = isset($element['caloptions']['daFormat']) ? $element['caloptions']['daFormat'] : '%Y/%m/%d';
    $element['rules']['regex'] = isset($element['rules']['regex']) ? $element['rules']['regex'] : '#^((\d{4}/\d{2}/\d{2})( \d{2}:\d{2})?)?$#';
    return $element;
}

/** Returns code to go in <head> for all instances of calendar */
function pieform_get_headdata_calendar($element) {
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
}


// TODO: use the get_value function to do strtotime? (possibly, also might need the javascript version for ajax forms)

?>
