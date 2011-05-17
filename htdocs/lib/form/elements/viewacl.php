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
 * @subpackage form
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
    global $USER;
    $smarty = smarty_core();
    $smarty->left_delimiter  = '{{';
    $smarty->right_delimiter = '}}';

    $value = $form->get_value($element);

    // Look for the presets and split them into two groups
    $public = get_config('allowpublicviews') == '1';
    $presets = array();
    $loggedinindex = 0;
    if ($public) {
        $presets[] = 'public';
        $loggedinindex = 1;
    }
    $presets[] = 'loggedin';
    if ($form->get_property('userview')) {
        $presets[] = 'friends';
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
                if (mb_strlen($item['name']) > 30) {
                    $item['shortname'] = str_shorten_text($item['name'], 30, true);
                }
                // only show access that is still current. Expired access will be deleted if the form is saved
                if($item['stopdate'] && (time() > strtotime($item['stopdate']))) {
                    unset($value[$key]);
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

    $allgroups = array(
        'type'   => 'allgroups',
        'id'     => 'allgroups',
        'start'  => null,
        'end'    => null,
        'name'   => get_string('allmygroups', 'group'),
        'preset' => true
    );
    $mygroups = array();
    foreach (group_get_user_groups($USER->get('id')) as $g) {
        $mygroups[] = array(
            'type' => 'group',
            'id'   => $g->id,
            'start' => null,
            'end'   => null,
            'name' => $g->name,
            'preset' => false
        );
        if (mb_strlen($g->name) > 30) {
            $mygroups[key($mygroups)]['shortname'] = str_shorten_text($g->name, 30, true);
        }
    }
    
    $smarty->assign('potentialpresets', json_encode($potentialpresets));
    $smarty->assign('loggedinindex', $loggedinindex);
    $smarty->assign('accesslist', json_encode($value));
    $smarty->assign('viewid', $form->get_property('viewid'));
    $smarty->assign('formname', $form->get_property('name'));
    $smarty->assign('allowcomments', $element['allowcomments']);
    $smarty->assign('allgroups', json_encode($allgroups));
    $smarty->assign('mygroups', json_encode($mygroups));
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
            if ($value['type'] == 'public' || $value['type'] == 'token') {
                unset($values[$key]);
            }
        }
    }

    /*
        If the above foreach() loop removes any items, json_encode() converts
        it into an object, which can't be iterated over - array_merge() with
        only one argument effects a renumber of the array, which json_encode()
        then handles with expected results.
    */
    if (is_array($values)) {
        return array_values($values);
    }
    else {
        return $values;
    }
}
