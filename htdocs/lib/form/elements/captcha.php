<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @package    mahara
 * @subpackage form
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

function pieform_element_captcha(Pieform $form, $element) {
    $id = $form->get_name() . '_' . $element['name'];
    $image = '<img src="' . get_config('wwwroot') . 'captcha.php?name=' . $id . '" alt="' . get_string('captchaimage') . '" style="padding: 2px 0;"><br>';
    $input = '<input type="text" class="text required" id="' . $id . '" name="' . $element['name'] . '" style="width: 137px;" tabindex="' . $form->get_property('tabindex') . '">';
    return $image . ' ' . $input;
}

function pieform_element_captcha_get_value(Pieform $form, $element) {
    global $SESSION;
    $name = $element['name'];
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    return isset($global[$name]) && strtolower($global[$name]) == strtolower($SESSION->get($form->get_name() . '_' . $name));
}

?>
