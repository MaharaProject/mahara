<?php
/**
 *
 * @package    mahara
 * @subpackage form-element
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
        $smarty->assign('authtypes', auth_get_available_auth_types($value['institution']));
        $smarty->assign('instancelist', $value['instancelist']);
        $smarty->assign('instancestring', implode(',',$value['instancearray']));
        $smarty->assign('default', $value['default']);
        $smarty->assign('institution', $value['institution']);
    }

    $smarty->assign('name', $element['name']);

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
    $value['authtypes'] = auth_get_available_auth_types($element['institution']);
    $value['instancePriority'] = $element['instancestring'];
    $value['institution'] = $element['institution'];

    return $value;
}

/**
 * Javascript to load libraries used by all authlist elements on the page
 */
function pieform_element_authlist_js() {
    $return = <<<EOF
// Load strings used by the authlist Pieforms element
if (strings !== undefined) {
EOF;

    $jsstrings = array(
        array('cannotremove', 'auth'),
        array('cannotremoveinuse', 'auth'),
        array('saveinstitutiondetailsfirst', 'auth'),
        array('noauthpluginconfigoptions', 'auth'),
    );
    foreach ($jsstrings as $stringdata) {
        list($tag, $section) = $stringdata;
        $return .= '    strings["' . $tag . '"] = ' . json_encode(get_raw_string($tag, $section)) . ";\n";
    }

    $return .= <<<EOF
}

// Load methods used by the authlist and its modal
PieformManager.loadPlugin('element', 'authlist');

// Since this menu is just a dummy selector, don't let it trigger the form change checker.
var formid = jQuery('select#authlistDummySelect').closest('form').attr('id');
PieformManager.connect('onload', formid, function(pformid) {
    var selector = 'select#authlistDummySelect';
    if (pformid) {
        selector = 'form#' + pformid + ' ' + selector;
    }
    jQuery(selector).off('change.changechecker');
});
EOF;

    return $return;
}

function pieform_element_authlist_get_headdata($element) {
    $result = '<script type="application/javascript">' . pieform_element_authlist_js() . "</script>";
    return array($result);
}
