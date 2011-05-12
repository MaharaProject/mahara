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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
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
function pieform_element_fieldset(Pieform $form, $element) {/*{{{*/
    global $_PIEFORM_FIELDSETS;
    $result = "\n<fieldset";
    $classes = array('pieform-fieldset');
    if (!empty($element['class'])) {
        $classes[] = Pieform::hsc($element['class']);
    }
    if (!empty($element['collapsible'])) {
        if (!isset($element['legend']) || $element['legend'] === '') {
            Pieform::info('Collapsible fieldsets should have a legend so they can be toggled');
        }
        $classes[] = 'collapsible';
        $formname = $form->get_name();
        if (!isset($_PIEFORM_FIELDSETS['forms'][$formname])) {
            $_PIEFORM_FIELDSETS['forms'][$formname] = array('formname' => $formname);
        }
        // Work out whether any of the children have errors on them
        $error = false;
        foreach ($element['elements'] as $subelement) {
            if (isset($subelement['error'])) {
                $error = true;
                break;
            }
        }
        if (!empty($element['collapsed']) && !$error) {
            $classes[] = 'collapsed';
        }
    }
    $result .= ' class="' . implode(' ', $classes) . '"';
    $result .= ">\n";
    if (isset($element['legend'])) {
        $result .= '<legend>';
        if (!empty($element['collapsible'])) {
            $result .= '<a href="">' . Pieform::hsc($element['legend']) . '</a>';
        }
        else {
            $result .= Pieform::hsc($element['legend']);
        }
        // Help icon
        if (!empty($element['help'])) {
            $function = $form->get_property('helpcallback');
            if (function_exists($function)) {
                $result .= $function($form, $element);
            }
            else {
                $result .= '<span class="help"><a href="" title="' . Pieform::hsc($element['help']) . '" onclick="return false;">?</a></span>';
            }
        }
        $result .= "</legend>\n";
    }

    foreach ($element['elements'] as $subname => $subelement) {
        if ($subelement['type'] == 'hidden') {
            throw new PieformException("You cannot put hidden elements in fieldsets");
        }
        $result .= "\t" . pieform_render_element($form, $subelement);
    }

    $result .= "</fieldset>\n";
    return $result;
}/*}}}*/


function pieform_element_fieldset_js() {/*{{{*/
    return <<<EOF
function pieform_update_legends(element) {
    if (!element) {
        element = getFirstElementByTagAndClassName('body');
    }
    forEach(getElementsByTagAndClassName('fieldset', 'collapsible', element), function(fieldset) {
        if (!hasElementClass(fieldset, 'pieform-fieldset')) {
            return;
        }
        var legend = getFirstElementByTagAndClassName('legend', null, fieldset);
        if (legend.firstChild.tagName == 'A') {
            connect(legend.firstChild, 'onclick', function(e) {
                toggleElementClass('collapsed', fieldset);
                e.stop();
            });
        }
    });
}
EOF;
}/*}}}*/

function pieform_element_fieldset_get_headdata() {/*{{{*/
    global $_PIEFORM_FIELDSETS;

    $result = '<script type="text/javascript">';
    $result .= pieform_element_fieldset_js();
    foreach ($_PIEFORM_FIELDSETS['forms'] as $fieldsetform) {
        $result .= "\nPieformManager.connect('onload', '{$fieldsetform['formname']}', partial(pieform_update_legends, '{$fieldsetform['formname']}'));";
    }
    $result .= "\n</script>";

    // Used below to try to work out whether pieform_update_legends is defined
    $_PIEFORM_FIELDSETS['head'] = true;

    return array($result);
}/*}}}*/


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
    if (!isset($_PIEFORM_FIELDSETS['head'])) {
        $result .= pieform_element_fieldset_js();
    }
    $result .= "pieform_update_legends('instconf');";

    return $result;
}
