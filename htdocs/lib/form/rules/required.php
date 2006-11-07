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
 * @subpackage form/rule
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Checks whether the field has been specified.
 *
 * @param string $field     The field to check
 * @return string           The error message, if the value is invalid.
 */
function form_rule_required($field) {
    // The array test is for using the "required" rule on file elements
    if ($field == '' || is_array($field) && !empty($field['error'])) {
        return get_string('This field is required');
    }
}

/**
 * Returns a javascript condition to check whether the field has been specified.
 *
 * @param string $id        id of the field to check
 * @return string           js condition to check if the field is empty.
 *         string           The error message, if the value is invalid.
 */
function form_rule_required_js($id) {
    $r->condition = '$(\'' . $id . '\').value != \'\'';
    $r->message = get_string('This field is required');
    return $r;
}



?>
