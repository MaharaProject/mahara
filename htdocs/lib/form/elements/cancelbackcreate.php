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

/**
 * Renders a submit and cancel button
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_element_cancelbackcreate(Pieform $form, $element) {
    $form->include_plugin('element', 'submit');
    $form->include_plugin('element', 'cancel');
    $cancelelement = $element;
    $cancelelement['class'] = (isset($cancelelement['class'])) ? $cancelelement['class'] . ' cancel' : 'cancel';
    $cancelelement['value'] = $element['value'][0];
    $backelement = $element;
    $backelement['name'] = 'back';
    $backelement['id'] = 'back';
    $backelement['class'] = (isset($backelement['class'])) ? $backelement['class'] . ' cancel' : 'cancel';
    $backelement['value'] = $element['value'][1];
    $submitelement = $element;
    $submitelement['class'] = (isset($submitelement['class'])) ? $submitelement['class'] . ' submit' : 'submit';
    $submitelement['value'] = $element['value'][2];

    if (isset($element['confirm']) && isset($element['confirm'][0])) {
        $cancelelement['confirm'] = $element['confirm'][0];
    }
    else {
        unset($cancelelement['confirm']);
    }
    if (isset($element['confirm']) && isset($element['confirm'][1])) {
        $backelement['confirm'] = $element['confirm'][1];
    }
    else {
        unset($backelement['confirm']);
    }
    if (isset($element['confirm']) && isset($element['confirm'][2])) {
        $submitelement['confirm'] = $element['confirm'][2];
    }
    else {
        unset($submitelement['confirm']);
    }

    return  pieform_element_cancel($form, $cancelelement) . ' ' . pieform_element_submit($form, $backelement)
        . ' ' . pieform_element_submit($form, $submitelement);
}

function pieform_element_cancelbacksubmit_set_attributes($element) {
    $element['submitelement'] = true;
    return $element;
}
