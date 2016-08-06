<?php
/**
 * Pieforms: Advanced web forms made easy
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @package    pieforms
 * @subpackage element
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

global $_PIEFORM_FIELDSETS;
$_PIEFORM_FIELDSETS = array();

/**
 * Renders a fieldset. Fieldsets contain other elements, and do not count as a
 * "true" element, in that they do not have a value and cannot be validated.
 *
 * @param Pieform $form    The form to render the element for
 * @param array   $element The element to render
 * @return string          The HTML for the element
 */
function pieform_element_fieldset(Pieform $form, $element) {
    global $_PIEFORM_FIELDSETS;

    $openparam = false;
    $formname = $form->get_name();

    $legendcontent = isset($element['legend']) ? Pieform::hsc($element['legend']) : '';

    $iscollapsible = pieform_is_collapsible($element);
    $iscollapsed = pieform_is_collapsed($form, $element);

    $classes = array('pieform-fieldset');

    if (!empty($element['class'])) {
        $classes[] = Pieform::hsc($element['class']);
    }

    // if fieldset is collapsible, we need to adjust the legend html and add a class to the fieldset
    if ($iscollapsible) {

         $classes[] = 'collapsible';

        if (!isset($_PIEFORM_FIELDSETS['forms'][$formname])) {
            $_PIEFORM_FIELDSETS['forms'][$formname] = array('formname' => $formname);
        }

        if (isset($element['name'])) {
            $openparam = $formname . '_' . $element['name'] . '_open';
        }

        $triggerclass = $iscollapsed ? 'collapsed': '';
        $ariaexpanded = $iscollapsed ? 'false' : 'true';

        $legendcontent = '<a href="#' . $openparam . '" data-toggle="collapse" aria-expanded="'.$ariaexpanded.'" aria-controls="' . $openparam . '" class="'.$triggerclass.'">';

        if (!empty($element['iconclass'])){
            $legendcontent .= '<span class="icon-fieldset icon icon-'.$element['iconclass'].'" role="presentation" aria-hidden="true"> </span>';
        }
        $legendcontent .= Pieform::hsc($element['legend']);
        $legendcontent .= '<span class="icon icon-chevron-down collapse-indicator right pull-right" role="presentation" aria-hidden="true"> </span> ';

        $legendcontent .= '</a>';
    }


    $fieldset = '<fieldset class="' . implode(' ', $classes) . '">';

    // Render legend and associated objects
    if (isset($element['legend'])) {
        $fieldset .= '<legend><h4>' . $legendcontent;

        // Help icon
        if (!empty($element['help'])) {
            $function = $form->get_property('helpcallback');
            if (function_exists($function)) {
                $fieldset .= $function($form, $element);
            }
            else {
                $fieldset .= '<span class="help"><a href="" title="' . Pieform::hsc($element['help']) . '" onclick="return false;">?</a></span>';
            }
        }
        $fieldset .= "</h4></legend>\n";
    }

    // Render the body of the fieldset
    $stateClass = $iscollapsed ? '':'in';

    $fieldset.= $iscollapsible ? '<div class="fieldset-body collapse '.$stateClass.'" id="'.$openparam.'">' : '';

    if (!empty($element['renderer']) && $element['renderer'] == 'multicolumnfieldsettable') {
        $fieldset .= _render_elements_as_multicolumn($form, $element);
    }
    else {
        foreach ($element['elements'] as $subname => $subelement) {

            if ($subelement['type'] == 'hidden') {
                throw new PieformException("You cannot put hidden elements in fieldsets");
            }

            $fieldset .= "\t" . pieform_render_element($form, $subelement);
        }
    }

    $fieldset .= $iscollapsible ? '</div>' : '';

    $fieldset .= "</fieldset>\n";

    return $fieldset;
}


function _render_elements_as_multicolumn($form, $element) {
        // we want to render the elements as div within each table cell
        // so we record the old renderer and switch to div and switch back afterwards
        $oldrenderer = $form->get_property('renderer');
        $form->set_property('renderer', 'div');
        $form->include_plugin('renderer', 'div');

        // We have a list of which elements are going to be the column headings
        $columns = $element['columns'];
        $footer = $element['footer'];

        $count = 0;
        $result = '';
        // If we want a description above the table we can add it as 'comment' to the fieldset element
        if (!empty($element['comment'])) {
            $result .= '<p';
            if (isset($element['class'])) {
                $result .= ' class="' . Pieform::hsc($element['class']) . '"';
            }
            $result .= '>' . Pieform::hsc($element['comment']);
            $result .= "</p>\n";
        }

        $result .= '<table class="fullwidth table">';
        // Now we loop through the elements chuncking them into rows based on the columns count
        // but we include the labelhtml as the first column to describe what the row is about.
        if(count($element['columns']) > 0){
            $result .= '<thead>';

            foreach ($columns as $name) {

                $data = $element['elements'][$name];

                 if (empty($count)) {
                    $result .= "\t<tr";
                    // Set the class of the enclosing <tr> to match that of the element
                    if (isset($data['class'])) {
                        $result .= ' class="' . Pieform::hsc($data['class']) . '"';
                    }
                    $result .= ">\n\t\t";
                }

                if (empty($count)) {
                    $result .= "<th></th>\n\t";
                }
                $result .= '<th>';
                $result .= Pieform::hsc($data['value']);
                if ($form->get_property('requiredmarker') && !empty($data['rules']['required'])) {
                    $result .= ' <span class="requiredmarker">' . $form->get_property('requiredmarker') . '</span>';
                }
                $result .= "</th>\n\t";
                $count ++;
            }

            $result .= '</thead>';

            $count = 0;
        }

        $result .= '<tbody>';

        foreach ($element['elements'] as $name => $data) {

            if (empty($count)) {
                $result .= "\t<tr>";
            }
            // ignore heading columns - we've already rendered them, and footer
            if (array_search($name, $columns) === false && array_search($name, $footer) === false) {

                if (empty($count)) {
                    $result .= '<th>';
                    if (isset($data['labelhtml'])) {
                        $result .= $data['labelhtml'];
                    }
                    $result .= "</th>\n\t";
                }
                unset($data['labelhtml']);
                $result .= "\t<td";
                if (isset($data['name'])) {
                    $result .= " id=\"" . $form->get_name() . '_' . Pieform::hsc($data['name']) . '_container"';
                }
                $result .= '>';

                $result .= pieform_render_element($form, $data);

                // Contextual help
                if (isset($data['helphtml'])) {
                    $result .= ' ' . $data['helphtml'];
                }
                $result .= "</td>\n\t";
            }
            $count++;
            if ($count == count($columns)) {
                $result .= "</tr>\n";
                $count = 0;
            }
        }
        $result .= '</tbody></table>';

        if(count($element['footer']) > 0){
            foreach ($footer as $name) {
                $data = $element['elements'][$name];
                $result .= pieform_render_element($form, $data);
            }
        }


        $form->set_property('renderer', $oldrenderer);
        return $result;
}

/**
 * Check if the form is supposed to be collapsed
 * @param array      $element The element to render
 * @return boolean   if the fieldset should be collapsed
 */
function pieform_is_collapsed(Pieform $form, $element) {
    $formname = $form->get_name();
    $iscollapsed = !empty($element['collapsed']);

    // if name element is not set, element should not be collapsed
    if(!isset($element['name'])){
        return false;
    }

    $valid = param_alphanumext('fs', null) !== $element['name'];

    // Work out whether any of the children have errors on them
    foreach ($element['elements'] as $subelement) {
        if (isset($subelement['error'])) {
           return false; // collapsible element should be open
        }
    }

    if (isset($element['name'])) {
        $openparam = $formname . '_' . $element['name'] . '_open';
    }

    if ($iscollapsed && $valid && !param_boolean($openparam, false)) {
        return true;
    }

    return false;
}

/**
 * Check if the fieldset is supposed to be collapsible
 * @param array   $element The element to render
 * @return boolean          If the fieldset is collapsible
 */
function pieform_is_collapsible($element) {

    if (empty($element['collapsible']) || !$element['collapsible']) {
        return false;
    }

    if (!isset($element['legend']) || $element['legend'] === '') {
        Pieform::info('Collapsible fieldsets should have a legend so they can be toggled');
    }

    return true;
}


/**
 * Extension by Mahara. This api function returns the javascript required to
 * set up the element, assuming the element has been placed in the page using
 * javascript. This feature is used in the views interface.
 *
 * In theory, this could go upstream to pieforms itself
 *
 * @param Pieform $form     The form
 * @param array   $element  The element
 */
function pieform_element_fieldset_views_js(Pieform $form, $element) {
    global $_PIEFORM_FIELDSETS;

    $result = '';

    foreach ($element['elements'] as $subelement) {
        $function = 'pieform_element_' . $subelement['type'] . '_views_js';
        if (is_callable($function)) {
            $result .= "\n" . call_user_func_array($function, array($form, $subelement));
        }
    }

    return $result;
}
