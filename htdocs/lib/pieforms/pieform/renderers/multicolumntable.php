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
 * @package    pieform
 * @subpackage renderer
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// @todo this renderer needs to take into account potentially being called more
// than once in the same PHP script (clobbering of the $formrenderermct variable).
// Also, not sure what 'static $var' does in global scope...
static $formrenderermct;

/**
 * Renders form elements inside a <table>. If elements have the same title,
 * they will be rendered in the same table row, allowing a grid layout.
 *
 * @param Pieform $form   The form the element is being rendered for
 * @param array  $element The element to be rendered
 * @return string         The element rendered inside an appropriate container
 */
function pieform_renderer_multicolumntable(Pieform $form, $element) {/*{{{*/
    global $formrenderermct;
    $formrenderermct->add_element($element['html'], $element);
    $formrenderermct->set_form($form);
}/*}}}*/

function pieform_renderer_multicolumntable_header() {/*{{{*/
    global $formrenderermct;
    $formrenderermct = new FormRendererMultiColumnTable();
}/*}}}*/

function pieform_renderer_multicolumntable_footer() {/*{{{*/
    global $formrenderermct;
    return $formrenderermct->build();
}/*}}}*/

class FormRendererMultiColumnTable {/*{{{*/

    private $elements = array();
    private $form;

    function add_element($builtelement, $rawelement) {
        if ($rawelement['type'] == 'fieldset' || $rawelement['type'] == 'container') {
            throw new PieformException('The multicolumntable renderer does not support fieldsets or containers');
        }

        if (!isset($rawelement['key'])) {
            $rawelement['key'] = $rawelement['title'];
        }
        if (!array_key_exists($rawelement['key'], $this->elements)) {
            $this->elements[$rawelement['key']] = array();
            $this->elements[$rawelement['key']]['rawelements'] = array();
            $this->elements[$rawelement['key']]['builtelements'] = array();
            $this->elements[$rawelement['key']]['settings'] = $rawelement;
        }
        $this->elements[$rawelement['key']]['rawelements'][] = $rawelement;
        $this->elements[$rawelement['key']]['builtelements'][] = $builtelement;
    }

    function set_form(Pieform $form) {
        $this->form = $form;
    }

    function build() {
        // Find out the maximum number of columns
        $columns = 0;
        foreach ($this->elements as $data) {
            $columns = max($columns, count($data['builtelements']));
        }
        $toggle = 0;
        $datatable = false;

        if(!$this->elements > 0) {
            return false;
        }
        $result = '<table class="fullwidth table"> <thead>';
        foreach ($this->elements as $title => $data) {
            if ($datatable) {
                $toggle = 1 - $toggle;
                $data['settings']['class'] .= ' r' . $toggle;
            }

            $result .= "\t<tr";

            // Set the class of the enclosing <tr> to match that of the element
            if ($data['settings']['class']) {
                $result .= ' class="' . Pieform::hsc($data['settings']['class']) . '"';
            }
            $result .= ">\n\t\t";
            $title = '';
            if (!empty($data['settings']['title'])) {
                $title = trim($data['settings']['title']);
            }
            if (!empty($title)) {
                $result .= '<th>';
                $result .= Pieform::hsc($title);
                if ($this->form->get_property('requiredmarker') && !empty($rawelement['rules']['required'])) {
                    $result .= ' <span class="requiredmarker">*</span>';
                }
                $result .= "</th>\n\t";
            }
            foreach ($data['builtelements'] as $k => $builtelement) {
                $rawelement = $data['rawelements'][$k];
                $dt = (!empty($rawelement['datatable'])) ? true : false;
                if ($dt) {
                    $result .= "\t<th";
                }
                else {
                    $result .= "\t<td";
                }
                if (isset($rawelement['name'])) {
                    $result .= " id=\"" . $this->form->get_name() . '_' . Pieform::hsc($rawelement['name']) . '_container"';
                }
                if ($rawelement['class']) {
                    $result .= ' class="' . Pieform::hsc($rawelement['class']) . '"';
                }
                $result .= '>';

                $result .= $builtelement;

                // Contextual help
                if (isset($rawelement['helphtml'])) {
                    $result .= ' ' . $rawelement['helphtml'];
                }
                if ($dt) {
                    $result .= "</th>\n\t";
                }
                else {
                    $result .= "</td>\n\t";
                }

                // @todo description...
            }
            for ($i = count($data['builtelements']); $i < $columns; $i++) {
                $result .= "\t<td></td>\n\t";
            }
            if (!$datatable) {
                $result .= "</tr></thead><tbody>\n";
             } else {
                $result .= "</tr>\n";
            }
            // We want to add in the row class but not for the heading row so we do the check here
            if (!empty($data['settings']['datatable'])) {
                $datatable = true;
            }
        }
        $result .= "</tbody></table>\n";
        return $result;
    }

}/*}}}*/
