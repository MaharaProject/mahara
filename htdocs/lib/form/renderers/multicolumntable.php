<?php
/**
 * This program is part of Mahara
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
 * @package    mahara
 * @subpackage form-renderer
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Renders form elements inside a <table>.
 *
 * @param string $builtelement The element, already built
 * @param array  $rawelement   The element in raw form, for looking up
 *                             information about it.
 * @return string              The element rendered inside an appropriate
 *                             container.
 */

require_once(dirname(__FILE__) . '/table.php');
static $formrenderermct;

function form_renderer_multicolumntable_messages_js($id, $submitid) {
    // @todo this isn't that pretty here :( 
    return form_renderer_table_messages_js($id, $submitid);
}

function form_renderer_multicolumntable($builtelement, $rawelement) {
    global $formrenderermct;
    $formrenderermct->add_element($builtelement, $rawelement);
}

function form_renderer_multicolumntable_header() {
    global $formrenderermct;
    $formrenderermct = new FormRendererMultiColumnTable();
}

function form_renderer_multicolumntable_footer() {
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
                $result .= hsc($data['settings']['title']);
            }
            $result .= "</th>\n\t";
            foreach ($data['builtelements'] as $k => $builtelement) {
                $rawelement = $data['rawelements'][$k];
                $result .= "\t<td id=\"" . $rawelement['name'] . '_container"';
                if ($rawelement['class']) {
                    $result .= ' class="' . $rawelement['class'] . '"';
                }
                $result .= '>';
                if (!empty($rawelement['prefix'])) {
                    $result .= hsc($rawelement['prefix']) . '&nbsp';
                }
                $result .= $builtelement;
                if (!empty($rawelement['suffix'])) {
                    $result .= '&nbsp;' . hsc($rawelement['suffix']);
                }
                // Contextual help
                if (!empty($rawelement['help'])) {
                    $result .= ' <span class="help"><a href="#" title="' 
                        . hsc($rawelement['help']) . '">?</a></span>';
                }
                $result .= "</td>\n\t";
            }
            $result .= "</tr>\n";
        }
        $result .= "</tbody></table>\n";
        return $result;
    }

}

?>