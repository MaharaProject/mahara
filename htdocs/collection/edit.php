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

// check that My Collections is enabled in the config
// if not as the user is trying to access this illegally
if (!get_config('allowcollections')) {
    die();
}

$new = param_boolean('new', 0);
$id = param_integer('id', 0);

$data = null;
if ($data = get_record_select('collection', 'id = ?', array($id))) {
    $collection = new Collection($id, (array)$data);
    if (!$USER->can_edit_collection($collection)) {
        $SESSION->add_error_msg(get_string('canteditdontown'));
        redirect('/collection/');
    }
}

// if not a new collection
if (!$new) {
    define('COLLECTION', $id);
    define('TITLE', $collection->get('name').': '.get_string('edittitleanddesc', 'collection'));
}
else {
    define('TITLE', get_string('edittitleanddesc', 'collection'));
}

$elements = Collection::get_collectionform_elements($data);
if ($new) {
    $elements['submit'] = array(
        'type' => 'submitcancel',
        'value' => array(get_string('next') . ': ' . get_string('editviews', 'collection'), get_string('cancel')),
    );
}
else {
    $elements['id'] = array(
        'type' => 'hidden',
        'value' => $id,
    );
    $elements['submit'] = array(
        'type' => 'submitcancel',
        'value' => array(get_string('save'), get_string('cancel')),
    );
}

$form = pieform(array(
    'name' => 'edit',
    'plugintype' => 'core',
    'pluginname' => 'collection',
    'successcallback' => 'submit',
    'elements' => $elements,
));

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign_by_ref('form', $form);
$smarty->display('collection/edit.tpl');

function edit_cancel_submit() {
    global $new;
    if ($new) {
        redirect('/collection/');
    }
    else {
        global $id;
        redirect('/collection/about.php?id='.$id);
    }
}

function submit(Pieform $form, $values) {
    global $SESSION, $new;
    $collection = Collection::save($values);
    if (!$new) {
        $SESSION->add_ok_msg(get_string('collectionsaved', 'collection'));
    }
    $collection->post_edit_redirect($new);
}

?>
