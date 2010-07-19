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
require(dirname(dirname(__FILE__)) . '/init.php');
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
$smarty = smarty();

if (!$USER->can_edit_collection(COLLECTION)) {
    $SESSION->add_error_msg(get_string('canteditdontown'));
    redirect('/collection/');
}

$views = collection_get_views(COLLECTION);
$master = collection_get_master(COLLECTION);

// we only need to have a select list if there is more than one view
if (count($views) > 1) {

    $options[0] = get_string('nooverride','collection');
    foreach ($views as $value) {
        $options[$value->view] = $value->title; 
    }

    $elements['collection'] = array(
        'type' => 'hidden',
        'value' => COLLECTION,
    );
    $elements['view'] = array(
        'type'         => 'select',
        'title'        => get_string('masterview','collection'),
        'options'      => $options,
        'rules'        => array('required' => true),
        'defaultvalue' => $master ? $master->id : 0,
    );
    $elements['submit'] = array(
        'type' => 'submit',
        'value' => get_string('save'),
        'goto' => get_config('wwwroot') . 'collection/views.php?id='.COLLECTION,
    );

    $form = pieform(array(
        'name' => 'access',
        'renderer' => 'div',
        'autofocus' => false,
        'method' => 'post',
        'elements' => $elements,
    ));

    $smarty->assign('form', $form);
}

if ($master) {
    $smarty->assign('master',$master->title);
    $smarty->assign('masterid',$master->id);
}
$smarty->assign('accessdesc',get_string('accessdesc','collection'));
$smarty->assign('viewcount', count($views));
$smarty->display('collection/access.tpl');

function access_submit(Pieform $form, $values) {
   collection_set_access($values['collection'],$values['view']);
}

?>
