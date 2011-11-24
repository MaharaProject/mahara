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
    global $USER, $SESSION;

    $strlen = function_exists('mb_strlen') ? 'mb_strlen' : 'strlen';

    $smarty = smarty_core();
    $smarty->left_delimiter  = '{{';
    $smarty->right_delimiter = '}}';

    $value = $form->get_value($element);

    // Look for the presets and split them into two groups
    $public = false;
    if (get_config('allowpublicviews') && $USER->institution_allows_public_views()) {
        $public = true;
    }
    else if (get_config('allowpublicprofiles') && $element['viewtype'] == 'profile') {
        $public = true;
    }
    $allpresets = array('public', 'loggedin', 'friends');
    $allowedpresets = array();
    $loggedinindex = 0;
    if ($public) {
        $allowedpresets[] = 'public';
        $loggedinindex = 1;
    }
    $allowedpresets[] = 'loggedin';
    if ($form->get_property('userview')) {
        $allowedpresets[] = 'friends';
    }

    $accesslist = array();
    if ($value) {
        foreach ($value as $item) {
            if (is_array($item)) {
                if ($item['type'] == 'public') {
                    $item['publicallowed'] = (int)$public;
                }
                if (in_array($item['type'], $allpresets)) {
                    $item['name'] = get_string($item['type'], 'view');
                    $item['preset'] = true;
                }
                else {
                    $item['name'] = pieform_render_viewacl_getvaluebytype($item['type'], $item['id']);
                }
                if ($strlen($item['name']) > 30) {
                    $item['shortname'] = str_shorten_text($item['name'], 30, true);
                }
                // only show access that is still current. Expired access will be deleted if the form is saved
                if ($form->is_submitted() || empty($item['stopdate']) || (time() <= strtotime($item['stopdate']))) {
                    $accesslist[] = $item;
                }
            }
        }
    }
    
    $myinstitutions = array();
    foreach ($USER->get('institutions') as $i) {
        $myinstitutions[] = array(
            'type' => 'institution',
            'id'   => $i->institution,
            'start' => null,
            'end'   => null,
            'name' => hsc($i->displayname),
            'preset' => false
        );
    }

    foreach ($allowedpresets as &$preset) {
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
        $group = array(
            'type' => 'group',
            'id'   => $g->id,
            'start' => null,
            'end'   => null,
            'name' => $g->name,
            'preset' => false
        );
        if ($strlen($g->name) > 30) {
            $group['shortname'] = str_shorten_text($g->name, 30, true);
        }
        $mygroups[] = $group;
    }
    
    $faves = array();
    foreach (get_user_favorites($USER->get('id')) as $u) {
        $fave = array(
            'type'   => 'user',
            'id'     => $u->id,
            'start'  => null,
            'end'    => null,
            'name'   => $u->name,
            'preset' => false
        );
        if ($strlen($u->name) > 30) {
            $fave['shortname'] = str_shorten_text($u->name, 30, true);
        }
        $faves[] = $fave;
    }

    $smarty->assign('viewtype', $element['viewtype']);
    $smarty->assign('potentialpresets', json_encode($allowedpresets));
    $smarty->assign('loggedinindex', $loggedinindex);
    $smarty->assign('accesslist', json_encode($accesslist));
    $smarty->assign('viewid', $form->get_property('viewid'));
    $smarty->assign('formname', $form->get_property('name'));
    $smarty->assign('myinstitutions', json_encode($myinstitutions));
    $smarty->assign('allowcomments', $element['allowcomments']);
    $smarty->assign('allgroups', json_encode($allgroups));
    $smarty->assign('mygroups', json_encode($mygroups));
    $smarty->assign('faves', json_encode($faves));
    return $smarty->fetch('form/viewacl.tpl');
}

function pieform_render_viewacl_getvaluebytype($type, $id) {
    switch ($type) {
        case 'loggedin':
            return get_string('loggedin', 'view');
            break;
        case 'user':
            $user = get_record('usr', 'id', $id);
            return display_name($user);
            break;
        case 'group':
            return get_field('group', 'name', 'id', $id);
            break;
        case 'institution':
            return get_string('institution', 'admin') . ': ' . get_field('institution', 'displayname', 'name', $id);
            break;
    }
    return sprintf("%s: %s", ucfirst($type), $id);
}

function pieform_element_viewacl_get_value(Pieform $form, $element) {
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
    return $values;
}
