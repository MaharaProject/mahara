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
