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

/**
 * Provides a tag input field
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_tags(Pieform $form, $element) {
    $smarty = smarty();

    $value = array();

    if (isset($element['defaultvalue']) && is_array($element['defaultvalue'])) {
        $value = $element['defaultvalue'];
    }

    if ($form->get_value($element)) {
        $value = $form->get_value($element);
    }

    if (isset($element['value']) && is_array($element['value'])) {
        $value = $element['value'];
    }

    if (!is_array($value)) {
        $value = array();
    }

    if (!isset($element['size'])) {
        $element['size'] = 60;
    }

    $smarty->assign('name', $element['name']);
    $smarty->assign('size', $element['size']);
    $smarty->assign('id', $form->get_name() . '_' . $element['id']);
    $smarty->assign('value', join(', ', $value));

    $smarty->left_delimiter = '{{';
    $smarty->right_delimiter = '}}';

    return $smarty->fetch('form/tags.tpl');
}

function pieform_element_tags_get_value(Pieform $form, $element) {
    $name = $element['name'];

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    if (!isset($global[$name])) {
        return null;
    }

    $value = preg_split("/\s*,\s*/", trim($global[$name]));
    $value = array_unique(array_filter($value, create_function('$v', 'return !empty($v);')));

    return $value;
}
