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
 * Provides an email list, with verification to enable addresses
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_element_authlist(Pieform $form, $element) {
    $smarty = smarty_core();

    $smarty->left_delimiter = '{{';
    $smarty->right_delimiter = '}}';

    $value = $form->get_value($element);

    if (!is_array($value) && isset($element['defaultvalue']) && is_array($element['defaultvalue'])) {
        $value = $element['defaultvalue'];
    }

    if (!isset($value['default'])) {
        $value['default'] = '';
    }

    if (is_array($value) && count($value)) {
        $smarty->assign('authtypes', $value['authtypes']);
        $smarty->assign('instancelist', $value['instancelist']);
        $smarty->assign('instancestring', implode(',',$value['instancearray']));
        $smarty->assign('default', $value['default']);
        $smarty->assign('institution', $value['institution']);
    }

    $smarty->assign('name', $element['name']);
    $smarty->assign('cannotremove', json_encode(get_string('cannotremove', 'auth')));
    $smarty->assign('cannotremoveinuse', json_encode(get_string('cannotremoveinuse', 'auth')));
    $smarty->assign('saveinstitutiondetailsfirst', json_encode(get_string('saveinstitutiondetailsfirst', 'auth')));
    $smarty->assign('noauthpluginconfigoptions', json_encode(get_string('noauthpluginconfigoptions', 'auth')));

    return $smarty->fetch('form/authlist.tpl');
}

function pieform_element_authlist_get_value(Pieform $form, $element) {
    $name = $element['name'];

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    $value = array();

    if (array_key_exists('instancePriority', $global) && !empty($global['instancePriority'])) {
        $value['instancearray'] = explode(',',$global['instancePriority']);
    } else {
        $value['instancearray'] = $element['instancearray'];
    }

    if (array_key_exists('deleteList', $global) && !empty($global['deleteList'])) {
        $value['deletearray'] = explode(',',$global['deleteList']);
    } else {
        $value['deletearray'] = array();
    }

    $value['instancelist'] = $element['options'];
    $value['authtypes'] = $element['authtypes'];
    $value['instancePriority'] = $element['instancestring'];
    $value['institution'] = $element['institution'];

    return $value;
}

function pieform_element_authlist_js() {
    return <<<EOF
// Since this menu is just a dummy selector, don't let it trigger the form change checker.
jQuery(document).on('pieform_postinit', function(event, form) {
    jQuery('form[name=' + form.data.name + ']').find('select#dummySelect').off('change.changechecker');
});

EOF;
}

function pieform_element_authlist_get_headdata() {
    $result = '<script>' . pieform_element_authlist_js() . "</script>";
    return array($result);
}
