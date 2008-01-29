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

/**
 * Renders a fieldset. Fieldsets contain other elements, and do not count as a
 * "true" element, in that they do not have a value and cannot be validated.
 *
 * @param Pieform $form    The form to render the element for
 * @param array   $element The element to render
 * @return string          The HTML for the element
 */
function pieform_element_fieldset(Pieform $form, $element) {/*{{{*/
    $result = "\n<fieldset";
    if (!empty($element['collapsible'])) {
        if (!isset($element['legend']) || $element['legend'] === '') {
            Pieform::info('Collapsible fieldsets should have a legend so they can be toggled');
        }
        $classes = array('collapsible');
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
        $result .= ' class="' . implode(' ', $classes) . '"';
    }
    $result .= ">\n";
    if (isset($element['legend'])) {
        $result .= '<legend';
        if (!empty($element['collapsible'])) {
            $id = substr(md5(microtime()), 0, 4);
            $result .= ' id="' . $id . '">';
            $result .= '<script type="text/javascript">';
            $result .= "var a = A({'href':'', 'tabindex':{$form->get_property('tabindex')}}, " . json_encode($element['legend']) . "); ";
            $result .= "connect(a, 'onclick', function(e) { toggleElementClass('collapsed', $('{$id}').parentNode); e.stop(); });";
            $result .= "replaceChildNodes('{$id}', a);</script>";
        }
        else {
            $result .= '>' . Pieform::hsc($element['legend']);
        }
        $result .= "</legend>\n";
    }

    foreach ($element['elements'] as $subname => $subelement) {
        if ($subelement['type'] == 'hidden') {
            throw new PieformError("You cannot put hidden elements in fieldsets");
        }
        $result .= "\t" . pieform_render_element($form, $subelement);
    }

    $result .= "</fieldset>\n";
    return $result;
}/*}}}*/

?>
