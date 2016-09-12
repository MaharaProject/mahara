<?php
/**
 *
 * @package    mahara
 * @subpackage form
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
    global $USER, $SESSION, $LANGDIRECTION;

    $strlen = function_exists('mb_strlen') ? 'mb_strlen' : 'strlen';

    $smarty = smarty_core();
    $smarty->left_delimiter  = '{{';
    $smarty->right_delimiter = '}}';

    $value = $form->get_value($element);

    // Look for the presets and split them into two groups
    require_once(get_config('libroot') . 'antispam.php');
    $public = false;
    if (is_probationary_user()) {
        $public = false;
    }
    else if (get_config('allowpublicviews') && $USER->institution_allows_public_views()) {
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
    $lockedpreset = null;
    if ($value) {
        foreach ($value as $item) {
            if (is_array($item)) {
                if ($item['type'] == 'public') {
                    $item['publicallowed'] = (int)$public;
                }
                if (in_array($item['type'], $allpresets)) {
                    $item['name'] = get_string(($item['type'] == 'loggedin' ? 'registeredusers' : $item['type']), 'view');
                    $item['preset'] = true;
                }
                else {
                    $item['name'] = pieform_render_viewacl_getvaluebytype($item['type'], $item['id']);
                }
                if ($strlen($item['name']) > 30) {
                    $item['shortname'] = str_shorten_text($item['name'], 30, true);
                }

                $datetimeformat = get_string('pieform_calendar_dateformat', 'langconfig') . ' ' . get_string('pieform_calendar_timeformat', 'langconfig');
                $rawstopdate = (array_key_exists('stopdate', $item) ? $item['stopdate'] : null);
                foreach (array('startdate', 'stopdate') as $datetype) {
                    if (empty($item[$datetype])) {
                        unset($item[$datetype]);
                    }
                    else {
                        $item[$datetype] = Pieform::hsc(strftime($datetimeformat, $item[$datetype]));
                    }
                }

                // only show access that is still current. Expired access will be deleted if the form is saved
                if ($form->is_submitted() || !$rawstopdate || (time() <= $rawstopdate)) {
                    $accesslist[] = $item;
                }
                if (!empty($item['locked'])) {
                    $lockedpreset = $item['type'];
                }
            }
        }
    }

    $defaultaccesslist = ($accesslist) ? 0 : 1;
    $myinstitutions = array();
    if ($USER->get('admin')) {
        $institutions = array();
        // Allow site admins to choose to share with the institution
        // that the first selected view/collection belongs to
        $viewid = $form->get_property('viewid');
        $view = new View($viewid);
        $institution = $view->get('institution');
        if ($institution) {
            $institutions = array(
                $institution => (object) array(
                    'institution' => $institution,
                    'displayname' => institution_display_name($institution),
                )
            );
        }
    }
    else {
        $institutions = $USER->get('institutions');
    }

    foreach ($institutions as $i) {
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
            'name' => get_string(($preset == 'loggedin' ? 'registeredusers' : $preset), 'view'),
            'locked' => ($preset === $lockedpreset),
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
    require_once(get_config('libroot') . 'pieforms/pieform/elements/calendar.php');
    $options = array('stepHour' => 1,
                     'stepMinute' => 5,
                     );
    $options = pieform_element_calendar_get_lang_strings($options, $LANGDIRECTION);
    $datepickeroptionstr = '';
    foreach ($options as $key => $option) {
        if (is_numeric($option)) {
            $datepickeroptionstr .= $key . ': ' . $option . ',';
        }
        else if (is_array($option)) {
            foreach ($option as $k => $v) {
                if (!is_numeric($v)) {
                    if (preg_match('/^\'(.*)\'$/', $v, $match)) {
                        $v = $match[1];
                    }
                    $option[$k] = json_encode($v);
                }
            }
            $option = '[' . implode(',', $option) . ']';
            $datepickeroptionstr .= $key . ': ' . $option . ',';
        }
        else {
            $datepickeroptionstr .= $key . ': ' . json_encode($option) . ',';
        }
    }

    $smarty->assign('datepickeroptions', $datepickeroptionstr);
    $smarty->assign('viewtype', $element['viewtype']);
    $smarty->assign('potentialpresets', json_encode($allowedpresets));
    $smarty->assign('loggedinindex', $loggedinindex);
    $smarty->assign('accesslist', json_encode($accesslist));
    $smarty->assign('defaultaccesslist', $defaultaccesslist);
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
            return get_string('registeredusers', 'view');
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
    // Convert dates to epochs
    if ($form->is_submitted() && $values) {
        foreach ($values as &$value) {
            if (!empty($value['startdate'])) {
                $value['startdate'] = pieform_element_calendar_convert_to_epoch($value['startdate']);
            }
            if (!empty($value['stopdate'])) {
                $value['stopdate'] = pieform_element_calendar_convert_to_epoch($value['stopdate']);
            }
        }
    }
    return $values;
}
