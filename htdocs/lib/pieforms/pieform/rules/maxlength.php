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
 * @subpackage rule
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Checks whether the given value is longer than the allowed length.
 *
 * @param Pieform $form      The form the rule is being applied to
 * @param string  $value     The value to check
 * @param array   $element   The element to check
 * @param int     $maxlength The length to check for
 * @return string            The error message, if the value is invalid.
 */
function pieform_rule_maxlength(Pieform $form, $value, $element, $maxlength) {/*{{{*/
    if (strlen($value) > $maxlength) {
        return sprintf($form->i18n('rule', 'maxlength', 'maxlength', $element), $maxlength);
    }
}/*}}}*/

function pieform_rule_maxlength_i18n() {/*{{{*/
    return array(
        'en.utf8' => array(
            'maxlength' => 'This field must be at most %d characters long'
        ),
        'en_US.utf8' => array(
            'maxlength' => 'This field must be at most %d characters long'
        ),
         'de.utf8' => array(
            'maxlength' => 'Das Feld darf höchstens %d Zeichen lang sein'
        ),
         'fr.utf8' => array(
            'maxlength' => 'Ce champ ne peut pas contenir plus de %d signes'
        ),
        'ja.utf8' => array(
            'maxlength' => 'このフィールドは、最大半角 %d 文字にしてください'
        ),
        'es.utf8' => array(
            'maxlength' => 'Este campo tiene que tener como máximo %d caracteres'
        ),
        'sl.utf8' => array(
            'maxlength' => 'To polje mora biti dolgo največ %d znakov'
        ),
        'nl.utf8' => array(
            'maxlength' => 'Dit veld moet minstens %d tekens lang zijn'
        ),
        'cs.utf8' => array(
            'maxlength' => 'Můžete zadat nejvýše %d znaků'
        ),

    );
}/*}}}*/

?>
