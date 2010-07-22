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
define('TITLE', get_string('collectionaccess','collection'));

// check that My Collections is enabled in the config
// if not as the user is trying to access this illegally
if (!get_config('allowcollections')) {
    die();
}

$collectionid = param_integer('id');
define('COLLECTION', $collectionid);

$collection = Collection::current_collection();
if (!$USER->can_edit_collection($collection)) {
    $SESSION->add_error_msg(get_string('canteditdontown'));
    redirect('/collection/');
}
$master = $collection->master();

$form = null;
if ($collection->has_views()) {

    $views = $collection->views();
    $options[0] = get_string('nooverride','collection');
    foreach ($views as $v) {
        $options[$v->view] = $v->title;
    }

    $elements['view'] = array(
        'type'         => 'select',
        'title'        => get_string('masterview','collection'),
        'options'      => $options,
        'rules'        => array('required' => true),
        'defaultvalue' => $master ? $master->view : 0,
    );
    $elements['submit'] = array(
        'type' => 'submit',
        'value' => get_string('save'),
        'goto' => get_config('wwwroot') . 'collection/views.php?id='.COLLECTION,
    );

    $form = pieform(array(
        'name' => 'access',
        'plugintype' => 'core',
        'pluginname' => 'collection',
        'autofocus' => false,
        'successcallback' => 'submit',
        'renderer' => 'div',
        'elements' => $elements,
    ));

}

$smarty = smarty();
$smarty->assign('master',$master);
$smarty->assign('form', $form);
$smarty->assign('accessdesc',get_string('accessdesc','collection'));
$smarty->display('collection/access.tpl');

function submit(Pieform $form, $values) {
    global $SESSION, $collection;

    $success = $collection->set_master($values['view']);

    if (!$success) {
        $SESSION->add_ok_msg(get_string('nooverridesaved', 'collection'));
        redirect('/collection/access.php?id=' . $collection->get('id'));
    }

    if ($success['secreturl'] == false) {
        $SESSION->add_ok_msg(get_string('accesssaved', 'collection'));
        redirect('/collection/access.php?id=' . $collection->get('id'));
    }
    else {
        if (!empty($success['valid'])) {
            $SESSION->add_ok_msg(get_string('accesssaved', 'collection'));
            $SESSION->add_info_msg(get_string('accessignored', 'collection'));
            redirect('/collection/access.php?id=' . $collection->get('id'));
        }
        else {
            $SESSION->add_error_msg(get_string('accesscantbeused', 'collection'));
            redirect('/collection/access.php?id=' . $collection->get('id'));
        }
    }
}

?>
