<?php
/**
 *
 * @package    mahara
 * @subpackage form-element
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Provides a basic text field input.
 *
 * @todo this is just lies ...
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_element_userlist(Pieform $form, $element) {
    $smarty = smarty_core();

    $smarty->left_delimiter = '{{';
    $smarty->right_delimiter = '}}';

    $value = $form->get_value($element);

    if (!is_array($value) && isset($element['defaultvalue']) && is_array($element['defaultvalue'])) {
        $value = $element['defaultvalue'];
    }

    if (is_array($value) && count($value)) {
        $orderby = (isset($element['searchparams']['orderby']) && $element['searchparams']['orderby'] == 'lastname') ? 'lastname,firstname,id' : 'firstname,lastname,id';
        $members = get_records_select_assoc('usr','id IN (' . join(',',array_map('intval', $value)) . ')', null, $orderby, 'id,username,firstname,lastname,preferredname,staff');

        foreach($members as &$member) {
            $member = display_name($member);
        }

        $smarty->assign('options',$members);
        $smarty->assign('value', join(',',$value));
    }

    $smarty->assign('name', $element['name']);

    if (!empty($element['lefttitle'])) {
        $smarty->assign('lefttitle', $element['lefttitle']);
    }
    if (!empty($element['righttitle'])) {
        $smarty->assign('righttitle', $element['righttitle']);
    }
    if (!empty($element['leftarrowlabel'])) {
        $smarty->assign('leftarrowlabel', $element['leftarrowlabel']);
    }
    if (!empty($element['rightarrowlabel'])) {
        $smarty->assign('rightarrowlabel', $element['rightarrowlabel']);
    }

    if (!empty($element['group'])) {
        $smarty->assign('group', $element['group']);
        $smarty->assign('includeadmins', !isset($element['includeadmins']) || $element['includeadmins'] ? 1 : 0);
    }
    if (empty($element['searchscript'])) {
        $element['searchscript'] = 'json/usersearch.php';
    }
    $smarty->assign('searchscript', $element['searchscript']);
    if (empty($element['searchparams'])) {
        $element['searchparams'] = array('query' => '', 'limit' => 100);
    }
    $smarty->assign('searchparams', json_encode($element['searchparams']));
    $smarty->assign('onlyshowingfirst', json_encode(get_string('onlyshowingfirst', 'admin')));
    $smarty->assign('resultsof', json_encode(get_string('resultsof', 'admin')));

    return $smarty->fetch('form/userlist.tpl');
}

function pieform_element_userlist_get_value(Pieform $form, $element) {
    $name = $element['name'];

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    if (isset($global[$name])) {
        $value = $global[$name];

        if ($value == '') {
            return array();
        }

        if (preg_match('/^(\d+(,\d+)*)$/',$value)) {
            return array_map('intval', explode(',', $value));
        }

        throw new PieformException("Invalid value for userlist form element '$name' = '$value'");
    }

    return null;
}

function pieform_element_userlist_rule_required(Pieform $form, $value, $element) {
    if (is_array($value) && count($value)) {
        return null;
    }

    return $form->i18n('rule', 'required', 'required', $element);
}
