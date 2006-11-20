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
 * @subpackage rule
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Checks whether the field has been specified.
 *
 * @param Pieform $form   The form the rule is being applied to
 * @param string $field   The field to check
 * @param array  $element The element to check
 * @return string         The error message, if the value is invalid.
 */
function pieform_rule_required(Pieform $form, $value, $element) {
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
