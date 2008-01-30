<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage form
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Provides an element to manage a view ACL
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_element_viewacl(Pieform $form, $element) {
    $smarty = smarty_core();
    $smarty->left_delimiter  = '{{';
    $smarty->right_delimiter = '}}';

    $value = $form->get_value($element);

    // Look for the presets and split them into two groups
    $presets = array();
    if (get_config('allowpublicviews') == '1') {
         $presets = array('public', 'loggedin', 'friends');
    }
    else {
        $presets = array('loggedin', 'friends');
    }
    if ($value) {
        foreach ($value as $key => &$item) {
            if (is_array($item)) {
                if (in_array($item['type'], $presets)) {
                    $item['name'] = get_string($item['type'], 'view');
                    $item['preset'] = true;
                }
                else {
                    $item['name'] = pieform_render_viewacl_getvaluebytype($item['type'], $item['id']);
                }
            }
            else {
                unset($value[$key]);
            }
        }
    }
    
    $potentialpresets = $presets;
    foreach ($potentialpresets as &$preset) {
        $preset = array(
            'type' => $preset,
            'id'   => $preset,
            'start' => null,
            'end'   => null,
            'name' => get_string($preset, 'view'),
            'preset' => true
        );
    }
    
    $smarty->assign('potentialpresets', json_encode($potentialpresets));
    $smarty->assign('accesslist', json_encode($value));
    return $smarty->fetch('form/viewacl.tpl');
}

function pieform_render_viewacl_getvaluebytype($type, $id) {
    switch ($type) {
        case 'user':
            $user = get_record('usr', 'id', $id);
            return display_name($user);
            break;
        case 'group':
            return get_field('group', 'name', 'id', $id);
            break;
    }
    return "$type: $id";
}

function pieform_element_viewacl_get_value(Pieform $form, $element) {
    global $USER;
    $values = null;
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if (isset($element['value'])) {
        $values = $element['value'];
    }
    else if (isset($global[$element['name']])) {
        $value = $global[$element['name']];
        $values = $value;
    }
    else if (isset($element['defaultvalue'])) {
        $values = $element['defaultvalue'];
    }
    if (get_config('allowpublicviews') != '1' && $values) {
        foreach ($values as $key => $value) {
            if ($value['type'] == 'public') {
                unset($values[$key]);
            }
        }
    }
    return $values;
}

?>
