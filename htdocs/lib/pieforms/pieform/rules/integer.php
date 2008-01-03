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
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Returns whether the given field is an integer
 *
 * @param Pieform $form      The form the rule is being applied to
 * @param string  $value     The value to check
 * @param array   $element   The element to check
 * @return string            The error message, if there is something wrong with
 *                           the address.
 */
function pieform_rule_integer(Pieform $form, $value, $element) {
    if (!is_numeric($value) || $value != (int)$value) {
        return $form->i18n('rule', 'integer', 'integer', $element);
    }
}

function pieform_rule_integer_i18n() {
    return array(
        'en.utf8' => array(
            'integer'   => 'The field must be an integer'
        ),
         'de.utf8' => array(
            'integer'   => 'Das Feld muss eine Zahl sein'
        ),
         'fr.utf8' => array(
            'integer'   => 'Ce champ doit être un nombre entier'
        ),
    );
}

?>
