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
 * @subpackage form-element
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
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
function pieform_element_authlist(Pieform $form, $element) {
    $smarty = smarty();

    $smarty->left_delimiter = '{{';
    $smarty->right_delimiter = '}}';

    $value = $form->get_value($element);

    if (!is_array($value) && isset($element['defaultvalue']) && is_array($element['defaultvalue'])) {
        $value = $element['defaultvalue'];
    }

    if (!isset($value['default'])) {
        $value['default'] = '';
    }
//var_dump($value['instancelist']);exit;
    if (is_array($value) && count($value)) {
        $smarty->assign('instancelist', $value['instancelist']);
        $smarty->assign('default', $value['default']);
    }

    $smarty->assign('name', $element['name']);

    return $smarty->fetch('form/authlist.tpl');
}

function pieform_element_authlist_get_value(Pieform $form, $element) {
    $name = $element['name'];

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    //if (!isset($global[$name . '_valid']) || !is_array($global[$name . '_valid'])) {
    //    return null;
    //}

    $value = array();

    $value['instancelist'] = $element['options'];

    //$value['default'] = $global[$name . '_selected'];
    //$value['validated'] = $global[$name . '_valid'];

    //if (isset($global[$name . '_invalid']) && is_array($global[$name . '_invalid'])) {
    //    $value['unvalidated'] = $global[$name . '_invalid'];
    //}
    //$value['options'] = array();
    return $value;
}

?>
