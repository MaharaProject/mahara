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

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'collection');
define('SECTION_PAGE', 'views');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('collection.php');

$new = param_integer('new',0);
$id = param_integer('id');
$newurl = $new ? '&new=1' : '';

// view addition/displayorder values
$view = param_integer('view',0);
$direction = param_variable('direction','');

$data = get_record_select('collection', 'id = ?', array($id), '*');
$collection = new Collection($id, (array)$data);
if (!$USER->can_edit_collection($collection)) {
    $SESSION->add_error_msg(get_string('canteditdontown', 'collection'));
    redirect('/collection/');
}

if (!$new) {
    define('COLLECTION', $id);
    define('TITLE', $collection->get('name') . ': ' . get_string('editviews', 'collection'));
}
else {
    define('TITLE', get_string('editviews', 'collection'));
}

if ($view AND !empty($direction)) {
    $collection->set_viewdisplayorder($view,$direction);
    redirect('/collection/views.php?id='.$id.$newurl);
}

$views = $collection->views();

if ($views) {
    foreach ($views['views'] as &$v) {
        $v->remove = pieform(array(
            'name' => 'removeview_' . $v->view,
            'successcallback' => 'removeview_submit',
            'elements' => array(
                'view' => array(
                    'type' => 'hidden',
                    'value' => $v->view,
                ),
                'submit' => array(
                    'type' => 'submit',
                    'confirm' => get_string('viewconfirmremove', 'collection'),
                    'value' => get_string('remove'),
                ),
            ),
        ));
    }
}

$elements = array();
$viewsform = null;
if ($available = Collection::available_views()) {
    foreach ($available as $a) {
        $elements['view_'.$a->id] = array(
            'type'      => 'checkbox', 
            'title'     => $a->title,
        );
    }
    $elements['submit'] = array(
        'type' => 'submit',
        'value' => get_string('addviews','collection'),
        'goto' => get_config('wwwroot') . 'collection/views.php?id='.$id,
    );

    $viewsform = pieform(array(
        'name' => 'addviews',
        'plugintype' => 'core',
        'pluginname' => 'collection',
        'autofocus' => false,
        'method'   => 'post',
        'elements' => $elements,
    ));
}


$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('displayurl',get_config('wwwroot').'collection/views.php?id='.$id.$newurl);
$smarty->assign('removeurl',get_config('wwwroot').'collection/deleteview.php?id='.$id.$newurl);
$smarty->assign_by_ref('views', $views);
$smarty->assign_by_ref('viewsform', $viewsform);
$smarty->display('collection/views.tpl');

function addviews_submit(Pieform $form, $values) {
    global $SESSION, $collection, $newurl;
    $count = $collection->add_views($values);
    if ($count > 1) {
        $SESSION->add_ok_msg(get_string('viewsaddedtocollection', 'collection'));
    }
    else {
        $SESSION->add_ok_msg(get_string('viewaddedtocollection', 'collection'));
    }
    redirect('/collection/views.php?id='.$collection->get('id').$newurl);

}

function manageviews_submit(Pieform $form, $values) {
    global $collection, $new, $SESSION, $views;
    if (param_boolean('back')) {
        redirect('/collection/edit.php?id='.$collection->get('id').'&new=1');
    }
    else {
        $collection->set('navigation',(int)$values['navigation']);
        $collection->commit();
        if ($new) {
            if ($views) {
                $SESSION->add_ok_msg(get_string('collectioncreatedsuccessfullyshare', 'collection'));
            }
            else {
                $SESSION->add_ok_msg(get_string('collectioncreatedsuccessfully', 'collection'));
            }
        }
        redirect('/collection/');
    }
}

function removeview_submit(Pieform $form, $values) {
    global $SESSION, $collection, $newurl;
    $collection->remove_view((int)$values['view']);
    $SESSION->add_ok_msg(get_string('viewremovedsuccessfully','collection'));
    redirect('/collection/views.php?id='.$collection->get('id').$newurl);
}
