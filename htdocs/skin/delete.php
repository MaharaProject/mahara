<?php
/**
 *
 * @package    mahara
 * @subpackage skin
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2010-2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('skin.php');
require_once('pieforms/pieform.php');

if (!can_use_skins()) {
    throw new FeatureNotEnabledException();
}

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
