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
 * @subpackage element
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * Renders a submit and cancel button
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_render_cancelbackcreate($element, Pieform $form) {
    $form->include_plugin('element', 'submit');
    $form->include_plugin('element', 'cancel');
    $cancelelement = $element;
    $cancelelement['value'] = $element['value'][0];
    $backelement = $element;
    $backelement['name'] = 'back';
    $backelement['id'] = 'back';
    $backelement['value'] = $element['value'][1];
    $submitelement = $element;
    $submitelement['value'] = $element['value'][2];
    return  pieform_render_cancel($cancelelement, $form) . ' ' . pieform_render_submit($backelement, $form)
        . ' ' . pieform_render_submit($submitelement, $form);
}

function pieform_render_cancelbacksubmit_set_attributes($element) {
    $element['ajaxmessages'] = true;
    return $element;
}

?>
