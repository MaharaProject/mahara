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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/collection');
require(dirname(dirname(__FILE__)) . '/init.php');
define('SECTION_PAGE', 'mycollections');
require_once('collection.php');
define('TITLE', get_string('deleteview', 'collection'));

// check that My Collections is enabled in the config
// if not as the user is trying to access this illegally
if (!get_config('allowcollections')) {
    die();
}

$id = param_integer('id');
define('VIEW', $id);

$collection = get_column('collection_view','collection','view',VIEW);
if (!$USER->can_edit_collection($collection[0])) {
    $SESSION->add_error_msg(get_string('canteditdontown'));
    redirect('/collection/');
}
$cv = get_record_select('collection_view', 'collection = ? AND view = ?', array($collection[0], VIEW));

$form = pieform(array(
    'name' => 'removeview',
    'renderer' => 'div',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'collection/views.php?id='.$collection[0],
        ),
        'view' => array(
            'type' => 'hidden',
            'value' => VIEW,
        ),
        'collection' => array(
            'type' => 'hidden',
            'value' => $collection[0],
        ),
    ),
));

$smarty = smarty();
$smarty->assign('subheading', hsc(TITLE));
$smarty->assign('message', get_string('viewconfirmdelete', 'collection'));
$smarty->assign('form', $form);
$smarty->display('collection/delete.tpl');

function removeview_submit(Pieform $form, $values) {
    collection_view_delete($values['view'], $values['collection']);
}

?>
