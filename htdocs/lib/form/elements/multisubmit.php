<?php
/**
 * Pieforms: Advanced web forms made easy
 * @package    pieform
 * @subpackage element
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Renders a set of submit buttons. Useful if you have multiple choices for a decision that go
 * to different form update places.
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_multisubmit(Pieform $form, $element) {
    if (!isset($element['value']) || !is_array($element['value']) || count($element['value']) < 2) {
        throw new PieformException('The multisubmit element "' . $element['name']
            . '" must have at least two element array for its value otherwise use "submit" element');
    }
    $form->include_plugin('element', 'button');
    $form->include_plugin('element', 'submit');
    $form->include_plugin('element', 'cancel');

    // first try for string indices
    $plugins = array('button', 'cancel');
    $elems = '';
    foreach ($element['options'] as $key => $value) {
        if (!is_numeric($key) && in_array($key, $plugins)) {
            $function = 'pieform_element_' . $key;
        }
        else {
            // default to 'submit' element
            $function = 'pieform_element_submit';
        }
        if (function_exists($function)) {
            $item = $element;
            $itemclass = '';
            if (isset($element['classes']) && isset($element['classes'][$key])) {
                $itemclass = $element['classes'][$key];
            }
            else if (isset($element['class'])) {
                $itemclass = $element['class'];
            }
            if (!is_numeric($key)) {
                $itemclass .= ' ' . $key;
            }
            $item['class'] = $itemclass;
            // A primary choice will give the submit option the btn-primary class
            if (isset($element['primarychoice']) && $element['primarychoice'] === $value) {
                $item['class'] .= ' btn-primary';
            }
            $item['usebuttontag'] = ($key == 'button') ? true : false;
            $item['value'] = $element['value'][$key];
            $item['name'] = $element['name'] . '[' . $key .']';
            $item['option'] = $element['options'][$key];
            if (isset($element['confirm']) && isset($element['confirm'][$key])) {
                $item['confirm'] = $element['confirm'][$key];
            }
            else {
                unset($item['confirm']);
            }
            $elems .= $function($form, $item);
            $elems .= ' ';
        }
    }

    if (!empty($elems)) {
        return $elems;
    }
    return '';
}

function pieform_element_multisubmit_set_attributes($element) {
    $element['submitelement'] = true;
    return $element;
}

function pieform_element_multisubmit_get_value(Pieform $form, $element) {
    if (!empty($_POST[$element['name']])) {
        return $element['options'][key($_POST[$element['name']])];

    }
    else if (is_array($element['value'])) {
        return $element['value'][0];
    }
    else {
        return $element['value'];
    }
}
