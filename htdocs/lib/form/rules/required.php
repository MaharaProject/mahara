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
 * Checks whether the field has been specified.
 *
 * @param Form $form        The form the rule is being applied to
 * @param string $field     The field to check
 * @param array  $element   The element to check
 * @return string           The error message, if the value is invalid.
 */
function form_rule_required(Form $form, $value, $element) {
    // The array test is for using the "required" rule on file elements
    $function = 'form_is_empty_' . $element['type'];
    if (function_exists($function)) {
        if ($function($value, $element)) {
            return $form->i18n('required');
        }
        return;
    }

    if ($value == '') {
        return $form->i18n('required');
    }
}

?>
