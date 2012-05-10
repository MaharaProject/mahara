<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2012 Catalyst IT Ltd and others; see:
 *                    http://wiki.mahara.org/Contributors
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
 * @author     Richard Mansfield
 *
 */

defined('INTERNAL') || die();

/**
 * Multiple text elements
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_element_multitext(Pieform $form, $element) {
    $smarty = smarty_core();

    $smarty->left_delimiter = '{{';
    $smarty->right_delimiter = '}}';

    $values = array();
    foreach ($form->get_value($element) as $v) {
        if ($v) {
            $values[] = hsc($v);
        }
    }

    $smarty->assign('value', $values);
    $smarty->assign('next', count($values));
    $smarty->assign('name', $form->get_name() . '_' . $element['name']);

    return $smarty->fetch('form/multitext.tpl');
}

function pieform_element_multitext_get_value(Pieform $form, $element) {
    if (isset($element['value'])) {
        return $element['value'];
    }

    $global = $form->get_property('method') == 'get' ? $_GET : $_POST;
    $name = $form->get_name() . '_' . $element['name'];

    if ($form->is_submitted() && isset($global[$name]) && is_array($global[$name])) {
        return array_values($global[$name]);
    }

    if (isset($element['defaultvalue'])) {
        return $element['defaultvalue'];
    }

    return null;
}/*}}}*/
