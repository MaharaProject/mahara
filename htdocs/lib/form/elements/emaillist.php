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

defined('INTERNAL') || die();

/**
 * Provides an email list, with verification to enable addresses
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_element_emaillist(Pieform $form, $element) {
    $smarty = smarty_core();

    $smarty->left_delimiter = '{{';
    $smarty->right_delimiter = '}}';

    $value = $form->get_value($element);

    if (!is_array($value) && isset($element['defaultvalue']) && is_array($element['defaultvalue'])) {
        $value = $element['defaultvalue'];
    }
    if (!isset($value['validated'])) {
        $value['validated'] = array();
    }

    if (!isset($value['unvalidated'])) {
        $value['unvalidated'] = array();
    }

    if (!isset($value['unsent'])) {
        $value['unsent'] = array();
    }

    if (!isset($value['default'])) {
        $value['default'] = '';
    }
    
    if (is_array($value) && count($value)) {
        $smarty->assign('validated', $value['validated']);
        $smarty->assign('unvalidated', $value['unvalidated']);
        $smarty->assign('unsent', $value['unsent']);
        $smarty->assign('default', $value['default']);
    }

    $smarty->assign('name', $element['name']);
    $smarty->assign('addbuttonstr', get_string('addbutton', 'artefact.internal'));
    $smarty->assign('validationemailstr', json_encode(get_string('validationemailwillbesent', 'artefact.internal')));

    return $smarty->fetch('form/emaillist.tpl');
}

function pieform_element_emaillist_get_value(Pieform $form, $element) {
    $name = $element['name'];

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    if (!isset($global[$name . '_valid']) || !is_array($global[$name . '_valid'])) {
        return null;
    }

    $value = array();

    $value['default'] = $global[$name . '_selected'];
    $value['validated'] = $global[$name . '_valid'];

    if (isset($global[$name . '_invalid']) && is_array($global[$name . '_invalid'])) {
        $value['unvalidated'] = $global[$name . '_invalid'];
    }

    if (isset($global[$name . '_unsent']) && is_array($global[$name . '_unsent'])) {
        $value['unsent'] = $global[$name . '_unsent'];
    }

    return $value;
}
