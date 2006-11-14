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
 * @subpackage form-rule
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Makes sure that the submitted value is specified in the 'options' index of
 * the element. This prevents malicious people from doing things like
 * submitting values that aren't in a select box.
 *
 * @param string $field     The field to check
 * @param string $element   The element being checked
 * @return string           The error message, if the value is invalid.
 * @todo untested :p
 */
function form_rule_validateoptions($field, $element) {
    // Get the value into an array as a key if it's a scalar, since
    // the actual check involves array keys
    if (!is_array($field)) {
        $field = array($field => '');
    }

    $allowedvalues = array_keys($element['options']);
    foreach (array_keys($field) as $key) {
        if (!in_array($key, $allowedvalues)) {
            return get_string('optionnotavailableforelement', 'error');
        }
    }
}

?>
