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
define('MENUITEM', 'myportfolio/collection/access');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'collection');
define('SECTION_PAGE', 'access');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('collection.php');
require_once('view.php');

// check that My Collections is enabled in the config
// if not as the user is trying to access this illegally
if (!get_config('allowcollections')) {
    die();
}

$new = param_integer('new', 0);
$id = param_integer('id');
$newurl = $new ? '&new=1' : '';

$data = get_record_select('collection', 'id = ?', array($id), '*');
$collection = new Collection($id, (array)$data);
if (!$USER->can_edit_collection($collection)) {
    $SESSION->add_error_msg(get_string('canteditdontown'));
    redirect('/collection/');
}

if (!$new) {
    define('COLLECTION', $id);
    define('TITLE', $collection->get('name').': '.get_string('editaccess','collection'));
}
else {
    define('TITLE', get_string('editaccess','collection'));
}

$master = $collection->master();

$form = null;
$newform = null;
if ($collection->has_views()) {

    $views = $collection->views();
    $options[0] = get_string('nooverride','collection');
    foreach ($views['views'] as $v) {
        $options[$v->view] = $v->title;
    }

    $elements['view'] = array(
        'type'         => 'radio',
        'title'        => get_string('masterview','collection'),
        'separator'      => '<br />',
        'options'      => $options,
        'rules'        => array('required' => true),
        'defaultvalue' => $master ? $master->view : 0,
    );

    if (!$new) {
        $elements['submit'] = array(
            'type' => 'submit',
            'value' => get_string('saveapply','collection'),
            'goto' => get_config('wwwroot') . 'collection/access.php?id='.$id,
        );
    }
    else {
        $elements['submit'] = array(
            'type'      => 'cancelbackcreate',
            'value'     => array(get_string('cancel'), get_string('back'), get_string('saveapply','collection')),
            'confirm'   => array(get_string('confirmcancelcreatingcollection', 'collection'), null, null),
        );
    }
    $form = pieform(array(
        'name' => 'updateaccess',
        'plugintype'    => 'core',
        'pluginname'    => 'collection',
        'autofocus'     => false,
        'method'        => 'post',
        'elements'      => $elements,
    ));
}
else if ($new) {
    $newform = pieform(array(
        'name' => 'new',
        'plugintype'    => 'core',
        'pluginname'    => 'collection',
        'autofocus'     => false,
        'method'        => 'post',
        'elements'      => array(
            'submit'  => array(
                'type'      => 'cancelbackcreate',
                'value'     => array(get_string('cancel'), get_string('back'),get_string('save')),
                'confirm'   => array(get_string('confirmcancelcreatingcollection', 'collection'), null, null),
            ),
        ),
    ));
}

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('strnoviews', get_string('noviewsaddsome','collection','<a href='.get_config('wwwroot').'/collection/views.php?id='.$id.$newurl.'>','</a>'));
$smarty->assign_by_ref('form', $form);
$smarty->assign_by_ref('newform', $newform);
$smarty->display('collection/access.tpl');

function updateaccess_submit(Pieform $form, $values) {
    global $SESSION, $collection, $new;

    // new collection back case
    if (param_boolean('back')) {
        redirect('/collection/views.php?id='.$collection->get('id').'&new=1');
    }
    $success = $collection->set_master($values['view']);
    $collection->post_access_redirect($success, $new);
}

function updateaccess_cancel_submit() {
    global $collection;
    $collection->delete();
    redirect('/collection/');
}

function new_submit(Pieform $form, $values) {
    global $collection;

    if (param_boolean('back')) {
        redirect('/collection/views.php?id='.$collection->get('id').'&new=1');
    }
    else {
        redirect('/collection/about.php?id='.$collection->get('id'));
    }
}

function new_cancel_submit() {
    global $collection;
    $collection->delete();
    redirect('/collection/');
}

?>
