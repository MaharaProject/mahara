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
 * @subpackage skin
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2010-2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('skin.php');
require_once('pieforms/pieform.php');

$skinid = param_integer('id');
$siteskin = param_boolean('site', false);
if ($siteskin) {
    $goto = 'admin/site/skins.php';
    $redirect = '/admin/site/skins.php';
}
else {
    $goto = 'skin/index.php';
    $redirect = '/skin/index.php';
}

$skin = new Skin($skinid, null);

if (!$skin->can_edit()) {
    throw new AccessDeniedException(get_string('cantdeleteskin', 'skin'));
}

define('TITLE', get_string('deletespecifiedskin', 'skin', $skin->get('title')));

$form = pieform(array(
    'name' => 'deleteskin',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'title' => get_string('deleteskinconfirm', 'skin'),
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . $goto,
        )
    ),
));

$smarty = smarty();
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('form', $form);
$smarty->display('form.tpl');

function deleteskin_submit(Pieform $form, $values) {
    global $SESSION, $USER, $skinid, $redirect;
    $skin = new Skin($skinid, null);
    if ($skin->get('owner') == $USER->get('id') || $USER->get('admin')) {
        $skin->delete();
        unlink(get_config('dataroot') . 'skins/' . $skinid . '.png');
        $SESSION->add_ok_msg(get_string('skindeleted', 'skin'));
    }
    else {
        $SESSION->add_error_msg(get_string('cantdeleteskin', 'skin'));
    }
    redirect($redirect);
}
