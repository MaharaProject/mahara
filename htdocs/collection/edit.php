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
define('MENUITEM', 'myportfolio/collection/info');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'collection');
define('SECTION_PAGE', 'edit');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('collection.php');
define('TITLE', get_string('editcollection', 'collection'));

// check that My Collections is enabled in the config
// if not as the user is trying to access this illegally
if (!get_config('allowcollections')) {
    die();
}

$collectionid = param_integer('id');
define('COLLECTION', $collectionid);

$data = get_record_select('collection', 'id = ?', array(COLLECTION));
$collection = new Collection(COLLECTION, (array)$data);
if (!$USER->can_edit_collection($collection)) {
    $SESSION->add_error_msg(get_string('canteditdontown'));
    redirect('/collection/');
}

$elements = Collection::get_collectionform_elements($data);
$elements['submit'] = array(
    'type' => 'submitcancel',
    'value' => array(get_string('savecollection','collection'), get_string('cancel')),
    'goto' => get_config('wwwroot') . 'collection/about.php?id='.COLLECTION,
);
$form = pieform(array(
    'name' => 'editcollection',
    'plugintype' => 'core',
    'pluginname' => 'collection',
    'successcallback' => 'submit',
    'elements' => $elements,
));

$smarty = smarty();
$smarty->assign_by_ref('form', $form);
$smarty->display('collection/edit.tpl');

function submit(Pieform $form, $values) {
    global $SESSION, $collectionid;
    Collection::save($values);
    $SESSION->add_ok_msg(get_string('collectionsaved', 'collection'));
    redirect('/collection/about.php?id='.$collectionid);
}

?>
