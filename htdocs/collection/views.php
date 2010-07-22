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
define('SECTION_PAGE', 'views');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('collection.php');
define('TITLE', get_string('editviews', 'collection'));

// check that My Collections is enabled in the config
// if not as the user is trying to access this illegally
if (!get_config('allowcollections')) {
    die();
}

$view = param_integer('view',0);
$direction = param_variable('direction','');
$collectionid = param_integer('id');
define('COLLECTION', $collectionid);

$data = get_record_select('collection', 'id = ?', array(COLLECTION), '*, ' . db_format_tsfield('ctime'));
$collection = new Collection(COLLECTION, (array)$data);
if (!$USER->can_edit_collection($collection)) {
    $SESSION->add_error_msg(get_string('canteditdontown'));
    redirect('/collection/');
}

if ($view AND !empty($direction)) {
    $collection->set_viewdisplayorder($view,$direction);
    redirect('/collection/views.php?id='.COLLECTION);
}

$views = $collection->views();

$elements = array();
if ($available = Collection::available_views()) {
    foreach ($available as $a) {
        $elements['view_'.$a->id] = array(
            'type'      => 'checkbox', 
            'title'     => $a->title,
        );
    }
    $elements['submit'] = array(
        'type' => 'submit',
        'value' => get_string('add','collection'),
        'goto' => get_config('wwwroot') . 'collection/views.php?id='.COLLECTION,
    );

    $form = pieform(array(
        'name' => 'addviews',
        'plugintype' => 'core',
        'pluginname' => 'collection',
        'autofocus' => false,
        'successcallback' => 'submit',
        'elements' => $elements,
    ));
}
else {
    $form = get_string('noviewstochoose','collection');
}

$smarty = smarty();
$smarty->assign_by_ref('views', $views);
$smarty->assign('form', $form);
$smarty->assign('addviews', get_string('addviews', 'collection'));
$smarty->display('collection/views.tpl');

function submit(Pieform $form, $values) {
    global $SESSION, $collection;
    $count = $collection->add_views($values);
    if ($count > 1) {
        $SESSION->add_ok_msg(get_string('viewsaddedtocollection', 'collection'));
    }
    else {
        $SESSION->add_ok_msg(get_string('viewaddedtocollection', 'collection'));
    }

    redirect('/collection/views.php?id=' . $collection->get('id'));

}

?>
