<?php
/**
 * Pieforms: Advanced web forms made easy
 * @package    pieform
 * @subpackage element
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Provides a way to pass in html that gets rendered
 * by the render (as opposed to the markup element)
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_element_html(Pieform $form, $element) {/*{{{*/
    return $element['value'];
}/*}}}*/

function pieform_element_html_set_attributes($element) {/*{{{*/
    $element['nolabel'] = true;
    $element['nofocus'] = true;
    return $element;
}/*}}}*/
