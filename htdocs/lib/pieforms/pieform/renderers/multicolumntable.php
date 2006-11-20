<?php
/**
 * This program is part of Pieforms
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    pieform
 * @subpackage renderer
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

// @todo this renderer needs to take into account potentially being called more
// than once in the same PHP script (clobbering of the $formrenderermct variable).
// Also, not sure what 'static $var' does in global scope...
require_once(dirname(__FILE__) . '/table.php');
static $formrenderermct;

/**
 * Renders form elements inside a <table>. If elements have the same title,
 * they will be rendered in the same table row, allowing a grid layout.
 *
 * @param Pieform $form         The form the element is being rendered for
 * @param string $builtelement The element, already built
 * @param array  $rawelement   The element in raw form, for looking up
 *                             information about it.
 * @return string              The element rendered inside an appropriate
 *                             container.
 */
function pieform_renderer_multicolumntable(Pieform $form, $builtelement, $rawelement) {
    global $formrenderermct;
    $formrenderermct->add_element($builtelement, $rawelement);
}

function pieform_renderer_multicolumntable_messages_js($id, $submitid) {
    return pieform_renderer_table_messages_js($id, $submitid);
}

function pieform_renderer_multicolumntable_header() {
    global $formrenderermct;
    $formrenderermct = new FormRendererMultiColumnTable();
}

function pieform_renderer_multicolumntable_footer() {
    global $formrenderermct;
    return $formrenderermct->build();
}

class FormRendererMultiColumnTable {

    private $elements = array();

    function add_element($builtelement, $rawelement) {
        if (!array_key_exists($rawelement['title'], $this->elements)) {
            $this->elements[$rawelement['title']] = array();
            $this->elements[$rawelement['title']]['rawelements'] = array();
            $this->elements[$rawelement['title']]['builtelements'] = array();
            $this->elements[$rawelement['title']]['settings'] = $rawelement;
        }
        $this->elements[$rawelement['title']]['rawelements'][] = $rawelement;
        $this->elements[$rawelement['title']]['builtelements'][] = $builtelement;
    }

    function build() {
        $result = "<table cellspacing=\"0\" border=\"0\"><tbody>\n";
        foreach ($this->elements as $title => $data) {
            $result .= "\t<tr";
            // Set the class of the enclosing <tr> to match that of the element
            if ($data['settings']['class']) {
                $result .= ' class="' . $data['settings']['class'] . '"';
            }
            $result .= ">\n\t\t";

            $result .= '<th>';
            if (isset($data['settings']['title'])) {
                $result .= Pieform::hsc($data['settings']['title']);
            }
            $result .= "</th>\n\t";

            foreach ($data['builtelements'] as $k => $builtelement) {
                $rawelement = $data['rawelements'][$k];
                $result .= "\t<td";
                if (isset($rawelement['name'])) {
                    $result .= " id=\"" . $rawelement['name'] . '_container"';
                }
                if ($rawelement['class']) {
                    $result .= ' class="' . $rawelement['class'] . '"';
                }
                $result .= '>';

                $result .= $builtelement;

                // Contextual help
                if (!empty($rawelement['help'])) {
                    $result .= ' <span class="help"><a href="#" title="' 
                        . Pieform::hsc($rawelement['help']) . '">?</a></span>';
                }
                $result .= "</td>\n\t";

                // @todo description...
            }
            $result .= "</tr>\n";
        }
        $result .= "</tbody></table>\n";
        return $result;
    }

}

?>
