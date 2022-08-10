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
 * Renders a set of radio buttons for a form
 *
 * @param Pieform $form  The form to render the element for
 * @param array $element The element to render. In addition to the standard Pieform
 *                       element attributes, it can also take the following optional
 *                       attributes:
 *                         buttons (defaults to \n, always has \n appended to it)
 *                       - rowsize: How many radio buttons to print per row (defaults to 1)
 *                       - nolabels: Don't print the labels next to the individual radio buttons.
 * @return string           The HTML for the element
 */
function pieform_element_radio(Pieform $form, $element) {
    if (!isset($element['options']) || !is_array($element['options']) || count($element['options']) < 1) {
        throw new PieformException('Radio elements should have at least one option');
    }

    $result = '';
    $form_value = $form->get_value($element);
    $id = $element['id'];

    $rowsize = isset($element['rowsize']) ? (int) $element['rowsize'] : 1;
    $nolabels = isset($element['nolabels']) ? $element['nolabels'] : false;
    $classname = '';
    if (!empty($element['hiddenlabels'])) {
        $classname = ' class="visually-hidden"';
    }

    $titletext = '';
    if (!empty($element['title'])) {
        $titletext = '<span class="accessible-hidden visually-hidden">' . Pieform::hsc($element['title']) . ': </span>';
    }

    $i = 0;

    $result .= '<div class="radio-wrapper">';
    foreach ($element['options'] as $value => $data) {
        $idsuffix = substr(md5(microtime()), 0, 4);
        $baseid = $element['id'];
        $element['id'] = $uid = $id . $idsuffix;
        if (is_array($data)) {
            $text = $data['text'];
            $description = (isset($data['description'])) ? $data['description'] : '';
        }
        else {
            $text = $data;
            $description = '';
        }
        $attributes = $form->element_attributes($element);
        $attributes = preg_replace("/aria-describedby=\"[^\"]*{$baseid}{$idsuffix}_description\s*[^\"]*\"/", 'aria-describedby="$1_description"', $attributes);

        $result .= '<div class="radio">';
        $result .= '<input type="radio"'
            . $attributes
            . ' value="' . Pieform::hsc($value) . '"'
            . (($form_value == $value) ? ' checked="checked"' : '')
            . '>';
        if (!$nolabels) {
            $result .= ' <label for="' . $form->get_name() . '_' . $uid . '"' . $classname . '>' . $titletext . Pieform::hsc($text) . "</label>"
            . ($description != '' ? '<div class="description">' . $description . '</div>' : '');
        }
        $result .= '</div>';

        $i++;
    }
    $result .= '</div>';

    return $result;
}

function pieform_element_radio_set_attributes($element) {
    $element['rules']['validateoptions'] = true;
    return $element;
}
