<?php
/**
 *
 * @package    mahara
 * @subpackage form-element
 * @author     Richard Mansfield
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// Pieform element for editing group artefact permissions

function pieform_element_rolepermissions(Pieform $form, $element) {/*{{{*/

    $value       = $form->get_value($element);
    $roles       = group_get_role_info($element['group']);
    $permissions = array_keys(get_object_vars($value['member']));

    $result = '<table class="editpermissions"><tbody>';
    $result .= '<tr><th>' . get_string('Role', 'group') . '</th>';

    foreach ($permissions as $p) {
        $result .= '<th>' . get_string('filepermission.' . $p, 'artefact.file') . '</th>';
    }

    $result .= '</tr>';

    $prefix = $form->get_name() . '_' . $element['name'] . '_p';

    foreach ($roles as $r) {
        $result .= '<tr>';
        $result .= '<td>' . hsc($r->display) . '</td>';

        foreach ($permissions as $p) {
            $inputname = $prefix . '_' . $r->name . '_' . $p;
            $result .= '<td><input type="checkbox" class="permission" name="' . hsc($inputname) . '"';
            if ($r->name == 'admin') {
                $result .= ' checked disabled';
            }
            else if ($value[$r->name]->$p) {
                $result .= ' checked';
            }
            $result .= '/></td>';
        }

        $result .= '</tr>';
    }

    $result .= '</tbody></table>';

    return $result;
}/*}}}*/

function pieform_element_rolepermissions_get_value(Pieform $form, $element) {/*{{{*/
    if (isset($element['value'])) {
        return $element['value'];
    }

    if (isset($element['defaultvalue'])) {
        $value = $element['defaultvalue'];
    }
    else {
        $value = group_get_default_artefact_permissions($element['group']);
    }

    if ($form->is_submitted()) {
        $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
        $prefix = $form->get_name() . '_' . $element['name'] . '_p';

        foreach ($value as $r => $perms) {
            foreach (array_keys(get_object_vars($perms)) as $p) {
                if ($r != 'admin') {
                    $value[$r]->$p = param_boolean($prefix . '_' . $r . '_' . $p);
                }
            }
        }
    }

    return $value;
}/*}}}*/
