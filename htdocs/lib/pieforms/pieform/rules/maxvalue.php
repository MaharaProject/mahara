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
 * Checks whether the given value is at most a certain size.
 *
 * @param Pieform $form      The form the rule is being applied to
 * @param string  $value     The value to check
 * @param array   $element   The element to check
 * @param int     $maxvalue  The value to check for
 * @return string            The error message, if the value is invalid.
 */
function pieform_rule_maxvalue(Pieform $form, $value, $element, $maxvalue) {
    if ($value != '' && intval($value) > $maxvalue) {
        return sprintf($form->i18n('rule', 'maxvalue', 'maxvalue', $element), $maxvalue);
    }
}

function pieform_rule_maxvalue_i18n() {
    return array(
        'en.utf8' => array(
            'maxvalue' => 'This value can not be larger than %d'
        ),
        'de.utf8' => array(
            'maxvalue' => 'Dieser Wert kann nicht gr??er als %d sein'
        ),
    );
}

?>
