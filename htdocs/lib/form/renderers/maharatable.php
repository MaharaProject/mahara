<?php
/**
 *
 * @package    mahara
 * @subpackage form-renderer
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Renders form elements inside a <table>.
 *
 * @param Pieform $form    The form the element is being rendered for
 * @param string  $element The element to be rendered
 * @return string          The element rendered inside an appropriate container
 */
function pieform_renderer_maharatable(Pieform $form, $element) {
    $formname = $form->get_name();
    if ($element['type'] == 'fieldset') {
        // Add table tags to the build element, to preserve HTML compliance
        $builtelement = $element['html'];
        if (0 === strpos($builtelement, "\n<fieldset")) {
            $closelegendpos = strpos($builtelement, '</legend>');
            if ($closelegendpos !== false) {
                $closelegendpos += 9;
                $builtelement = substr($builtelement, 0, $closelegendpos) . '<table>' . substr($builtelement, $closelegendpos);
            }
            else {
                $pos = strpos($builtelement, '>') + 1;
                $builtelement = substr($builtelement, 0, $pos) . '<table>' . substr($builtelement, $pos);
            }
        }
        else {
            $builtelement = substr($builtelement, 0, 11) . '<table>' . substr($builtelement, 11);
        }
        $builtelement = substr($builtelement, 0, -12) . '</table></fieldset>';

        $result = "\t<tr id=\"{$formname}_{$element['name']}_fieldset\"";
        if (isset($element['class'])) {
            $result .= ' class="' . Pieform::hsc($element['class']) . '"';
        }
        $result .= ">\n\t\t<td colspan=\"2\">";
        $result .= $builtelement;
        $result .= "</td>\n\t</tr>";
        return $result;
    }

    $result = '';
    if (isset($element['labelhtml']) && $element['labelhtml'] !== '') {
        $result .= "\t<tr";
        $result .= ' id="' . $formname . '_' . Pieform::hsc($element['name']) . '_header"';
        // Set the class of the enclosing <tr> to match that of the element
        if (isset($element['class'])) {
            $result .= ' class="' . Pieform::hsc($element['class']) . '"';
        }
        $result .= ">\n\t\t";

        $result .= '<th>';
        $result .= $element['labelhtml'];
        $result .= "</th>\n\t</tr>\n";
    }
    $result .= "\t<tr id=\"{$formname}_{$element['name']}_container\"";
    if ($element['class']) {
        $result .= ' class="' . Pieform::hsc($element['class']) . '"';
    }
    $result .= ">\n\t\t<td>";

    // Wrap WYSIWYG elements in a table with two cells side by side, one for the element and one for the help icon
    if (!empty($element['help']) && $element['type'] == 'wysiwyg') {
        $result .= '<table class="help-wrapper"><tr><td>';
    }

    // Add the element itself
    $result .= $element['html'];

    if (!empty($element['help']) && $element['type'] == 'wysiwyg') {
        $result .= '</td><td>';
    }

    // Contextual help
    if (!empty($element['help'])) {
        $result .= get_help_icon($form->get_property('plugintype'),
                                 $form->get_property('pluginname'),
                                 $form->get_name(), $element['name']);
        if ($element['type'] == 'wysiwyg') {
            $result .= '</td></tr></table>';
        }
    }

    $result .= "</td>\n\t</tr>\n";

    // Description - optional description of the element, or other note that should be visible
    // on the form itself (without the user having to hover over contextual help
    if ((!$form->has_errors() || $form->get_property('showdescriptiononerror')) && !empty($element['descriptionhtml'])) {
        $result .= "\t<tr";
        if (isset($element['class'])) {
            $result .= ' class="' . Pieform::hsc($element['class']) . '"';
        }
        $result .= ">\n\t\t<td class=\"description\">";
        $result .= $element['descriptionhtml'];
        $result .= "</td>\n\t</tr>\n";
    }

    if (!empty($element['errorhtml'])) {
        $result .= "\t<tr>\n\t\t<td class=\"errmsg\">";
        $result .= !empty($element['isescaped']) ? hsc($element['errorhtml']) : $element['errorhtml'];
        $result .= "</td>\n\t</tr>\n";
    }

    return $result;
}

function pieform_renderer_maharatable_header() {
    return "<table cellspacing=\"0\" border=\"0\" class=\"maharatable\"><tbody>\n";
}

function pieform_renderer_maharatable_footer() {
    return "</tbody></table>\n";
}


// @todo table renderer - probably doesn't need the remove_error function for the same reason that
// this one doesn't (all errors are removed on form submit). Also should set classes on elements.
function pieform_renderer_maharatable_get_js($id) {
    $result = <<<EOF
function {$id}_set_error(message, element) {
    element = '{$id}_' + element + '_container';
    var container = jQuery('#' + element).find('td').first();
    container.addClass('error');
    container.children().first().addClass('error');
    jQuery('<tr>', {'id': '{$id}_error_' + element}).append(jQuery('<td>', {'class': 'errmsg', 'text': message} ))
        .insertAfter(jQuery('#' + element));
}
function {$id}_remove_all_errors() {
    jQuery('#$id td.errmsg').each(function() {
        jQuery(this).parent().remove();
    });
    jQuery('#$id td.error').each(function() {
        jQuery(this).removeClass('error');
        jQuery(this).children().first().removeClass('error');
    });
}
EOF;
    return $result;
}
