<?php
/**
 * Pieforms: Advanced web forms made easy
 * @package    pieform
 * @subpackage renderer
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

function pieform_renderer_oneline_header() {/*{{{*/
    return '<div>';
}/*}}}*/

function pieform_renderer_oneline_footer() {/*{{{*/
    return '</div>';
}/*}}}*/

/**
 * Renders form elements all on one line.
 *
 * @param Pieform $form    The form the element is being rendered for
 * @param array   $element The element that is being rendered
 * @return string          The element rendered inside an appropriate container
 */
function pieform_renderer_oneline(Pieform $form, $element) {/*{{{*/
    $formname = $form->get_name();
    // Set the class of the enclosing <div> to match that of the element
    $result = '<span';
    if (isset($element['name'])) {
        $result .= ' id="' . $formname . '_' . Pieform::hsc($element['name']) . '_container"';
    }
    if (!empty($element['class'])) {
                // add form-group classes to all real form fields
        if (strpos($element['class'],'html') === false) {
            // $element['class'] = $element['class'] . ' form-group-inline';
            $element['class'] = 'form-group-inline';
        }

        // add bootstrap has-error class to any error fields
        if (strpos($element['class'],'error') !== false) {
             $element['class'] = $element['class'] . ' has-error';
        }

        $result .= ' class="' . Pieform::hsc($element['class']) . '"';
    }
    $result .= '>';

    if (isset($element['labelhtml'])) {
        $result .= $element['labelhtml'];
    }

    if (isset($element['prehtml'])) {
        $result .= '<span class="prehtml">' . $element['prehtml'] . '</span>';
    }

    $result .= $element['html'];

    if (isset($element['posthtml'])) {
        $result .= '<span class="posthtml">' . $element['posthtml'] . '</span>';
    }

    $result .= "</span>";
    return $result;
}/*}}}*/
