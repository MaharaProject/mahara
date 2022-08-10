<?php
/**
 * Pieforms: Advanced web forms made easy
 * @package    pieforms
 * @subpackage element
 * @author     Hugh Davenport <hugh@catalyst.net.nz>
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Renders a container. Containers contain other elements, and do not count as a
 * "true" element, in that they do not have a value and cannot be validated.
 *
 * Similar to a fieldset, except with no wrapper, apart from the div produced by
 * the pieform
 *
 * @param Pieform $form    The form to render the element for
 * @param array   $element The element to render
 * @return string          The HTML for the element
 */
function pieform_element_container(Pieform $form, $element) {/*{{{*/
    $result = "";
    if (isset($element['title'])) {
      $result .= '<h3>' . $element['title'] . '</h3>';
    }
    foreach ($element['elements'] as $subname => $subelement) {
        $result .= "\t" . pieform_render_element($form, $subelement);
    }
    return $result;
}/*}}}*/
