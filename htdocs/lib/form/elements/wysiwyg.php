<?php
/**
 * This program is part of Mahara
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
 * @package    mahara
 * @subpackage form
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Renders a textarea, but with extra javascript to turn it into a wysiwyg
 * textarea.
 *
 * @param array   $element The element to render
 * @param Pieform $form    The form to render the element for
 * @return string          The HTML for the element
 */
function pieform_render_wysiwyg($element, Pieform $form) {
    global $USER;
    if ($USER->get_account_preference('wysiwyg')) {
        $element['class'] = isset($element['class']) && $element['class'] !== '' ? $element['class'] . ' wysiwyg' : 'wysiwyg';
    }
    $rows = $cols = $style = '';
    if (isset($element['height'])) {
        $style .= 'height:' . $element['height'] . ';';
        $rows   = (intval($element['height'] > 0)) ? ceil(intval($element['height']) / 10) : 1;
    }
    elseif (isset($element['rows'])) {
        $rows = $element['rows'];
    }
    else {
        log_warn('No value for rows or height specified for textarea ' . $element['name']);
    }

    if (isset($element['width'])) {
        $style .= 'width:' . $element['width'] . ';';
        $cols   = (intval($element['width'] > 0)) ? ceil(intval($element['width']) / 10) : 1;
    }
    elseif (isset($element['cols'])) {
        $cols = $element['cols'];
    }
    else {
        log_warn('No value for cols or width specified for textarea ' . $element['name']);
    }
    $element['style'] = (isset($element['style'])) ? $style . $element['style'] : $style;
    return '<textarea'
        . (($rows) ? ' rows="' . $rows . '"' : '')
        . (($cols) ? ' cols="' . $cols . '"' : '')
        . $form->element_attributes($element, array('maxlength', 'size'))
        . '>' . Pieform::hsc($form->get_value($element)) . '</textarea>';
}

/**
 * @todo document: basically, the required rule now works better, as stripping
 * tags out takes away a whole bunch of hidden stuff
 */
function pieform_is_empty_wysiwyg($value, $element) {
    return strip_tags($value) === '';
}

function pieform_get_headdata_wysiwyg() {
    global $USER;
    if ($USER->get_account_preference('wysiwyg')) {
        return array('tinymce');
    }
    return array();
}

?>
