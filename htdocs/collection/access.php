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
define('SECTION_PAGE', 'views');

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
if ($collection->has_views()) {

    $views = $collection->views();
    $options[0] = get_string('nooverride','collection');
    foreach ($views['views'] as $v) {
        $options[$v->view] = $v->title;
    }

    $elements['view'] = array(
        'type'         => 'select',
        'title'        => get_string('masterview','collection'),
        'options'      => $options,
        'rules'        => array('required' => true),
        'defaultvalue' => $master ? $master->view : 0,
    );
    if ($new) {
        $elements['submit'] = array(
            'type' => 'submit',
            'value' => get_string('save'),
            'goto' => get_config('wwwroot') . 'collection/views.php?id='.$id.'&new='.$new,
        );
    }
    else {
        $elements['submit'] = array(
            'type' => 'submit',
            'value' => get_string('save'),
            'goto' => get_config('wwwroot') . 'collection/views.php?id='.$id,
        );
    }

    $form = pieform(array(
        'name' => 'access',
        'plugintype'    => 'core',
        'pluginname'    => 'collection',
        'autofocus'     => false,
        'method'        => 'post',
        'renderer'      => 'div',
        'elements'      => $elements,
    ));

}

$smarty = smarty();
if ($new) {
    $newform = pieform(array(
        'name'          =>  'new',
        'plugintype'    => 'core',
        'pluginname'    => 'collection',
        'autofocus'     => false,
        'method'        => 'post',
        'elements'      => array(
            'submit' => array(
                'type'  => 'cancelbackcreate',
                'value' => array(get_string('cancel'), get_string('back','collection'), get_string('save')),
                'confirm' => array(get_string('confirmcancelcreatingcollection', 'collection'), null, null),
            ),
        ),
    ));
    $smarty->assign('newform', $newform);
}

$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('master', $master);
$smarty->assign_by_ref('form', $form);
$smarty->display('collection/access.tpl');

function access_submit(Pieform $form, $values) {
    global $SESSION, $collection, $new;

    $new = $new ? '&new=1' : '';
    $success = $collection->set_master($values['view']);

    if (!$success) {
        $SESSION->add_ok_msg(get_string('nooverridesaved', 'collection'));
        redirect('/collection/access.php?id=' . $collection->get('id') . $new);
    }

    if ($success['secreturl'] == false) {
        $SESSION->add_ok_msg(get_string('accesssaved', 'collection'));
        redirect('/collection/access.php?id=' . $collection->get('id') . $new);
    }
    else {
        if (!empty($success['valid'])) {
            $SESSION->add_ok_msg(get_string('accesssaved', 'collection'));
            $SESSION->add_info_msg(get_string('accessignored', 'collection'));
            redirect('/collection/access.php?id=' . $collection->get('id') . $new);
        }
        else {
            $SESSION->add_error_msg(get_string('accesscantbeused', 'collection'));
            redirect('/collection/access.php?id=' . $collection->get('id') . $new);
        }
    }
}

function new_cancel_submit() {
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

?>
