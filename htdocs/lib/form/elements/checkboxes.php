<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @package    mahara
 * @subpackage form-element
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

function pieform_element_checkboxes(Pieform $form, $element) {/*{{{*/
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    $submitted = $form->is_submitted();
    if ($submitted && isset($global[$element['name']])) {
        $value = $global[$element['name']];
    }

    $result = '';

    if (count($element['elements']) > 1) {
        $id = hsc($form->get_name() . '_' . $element['name']) . '_container';
        $result .= '<a href="" onclick="pieform_element_checkboxes_update(\'' . $id . '\', true); return false;">' . get_string('All') . '</a>'
            . '&nbsp;'
            . ' <a href="" onclick="pieform_element_checkboxes_update(\'' . $id . '\', false); return false;">' . get_string('none') . '</a>';
    }

    $result .= '<div class="cl"></div>';

    $element['name'] .= '[]';

    foreach ($element['elements'] as $e) {
        $checked = ($submitted && (!empty($value[$e['value']]) || in_array($e['value'], $value))) || (!$submitted && !empty($e['defaultvalue']));
        $result .= '<div class="checkboxes-option"><input type="checkbox" value="' . $e['value'] . '" '
        . $form->element_attributes($element)
        . ($checked ? ' checked="checked"' : '')
        . '>' . Pieform::hsc(str_shorten_text($e['title'], 17, true)) . '</div>';
    }
    $result .= '<div class="cl"></div>';

    return $result;
}/*}}}*/

function pieform_element_checkboxes_get_value(Pieform $form, $element) {/*{{{*/
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if (isset($element['value'])) {
        $values = (array) $element['value'];
    }
    else if ($form->is_submitted() && isset($global[$element['name']])) {
        $values = (array) $global[$element['name']];
    }
    else if (!$form->is_submitted() && isset($element['defaultvalue'])) {
        $values = (array) $element['defaultvalue'];
    }
    else {
        $values = array();
    }

    return $values;
}/*}}}*/

function pieform_element_checkboxes_js() {/*{{{*/
    return <<<EOF
function pieform_element_checkboxes_update(p, v) {
    forEach(getElementsByTagAndClassName('input', 'checkboxes', p), function(e) {
        e.checked = v;
    });
}
EOF;
}/*}}}*/

function pieform_element_checkboxes_get_headdata() {/*{{{*/
    $result = '<script type="text/javascript">' . pieform_element_checkboxes_js() . "\n</script>";
    return array($result);
}/*}}}*/
