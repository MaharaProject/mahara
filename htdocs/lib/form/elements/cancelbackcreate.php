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
