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
define('MENUITEM', 'myportfolio/collection/views');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'collection');
define('SECTION_PAGE', 'deleteview');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('collection.php');
define('TITLE', get_string('deleteview', 'collection'));

// check that My Collections is enabled in the config
// if not as the user is trying to access this illegally
if (!get_config('allowcollections')) {
    die();
}

$vid = param_integer('view');
$new = param_integer('new',0);
$id = param_integer('id');

if (!$new) {
    define('COLLECTION', $id);
}

$data = (array)get_record_select('collection', 'id = ?', array($id), '*');
$collection = new Collection($id, $data);
if (!$USER->can_edit_collection($collection)) {
    $SESSION->add_error_msg(get_string('canteditdontown'));
    redirect('/collection/');
}

$newtxt = $new ? 'new=1' : '';
$elements = array(
    'submit' => array(
        'type' => 'submitcancel',
        'value' => array(get_string('yes'), get_string('no')),
        'goto' => get_config('wwwroot') . 'collection/views.php?id='.$id.$newtxt,
    ),
);

$form = pieform(array(
    'name' => 'removeview',
    'renderer' => 'div',
    'plugintype' => 'core',
    'pluginname' => 'collection',
    'autofocus' => false,
    'successcallback' => 'submit',
    'elements' => $elements,
));

$smarty = smarty();
$smarty->assign('subheading', hsc(TITLE));
$smarty->assign('message', get_string('viewconfirmremove', 'collection'));
$smarty->assign('form', $form);
$smarty->display('collection/delete.tpl');

function submit(Pieform $form, $values) {
    global $SESSION, $vid, $collection, $newtxt;
    $collection->remove_view($vid);
    $SESSION->add_ok_msg(get_string('viewremovedsuccessfully','collection'));
    redirect('/collection/views.php?id='.$collection->get('id').$newtxt);
}

?>
