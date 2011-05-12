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
define('SECTION_PAGE', 'index');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('collection.php');
define('TITLE', get_string('Collections', 'collection'));

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 5);

$data = Collection::get_mycollections_data($offset, $limit);
foreach ($data->data as $value) {
    $collection = new Collection($value->id);
    $views = $collection->get('views');
    if (!empty($views)) {
        $value->views = $views['views'];
    }
}

$pagination = build_pagination(array(
    'id' => 'collectionslist_pagination',
    'class' => 'center',
    'url' => get_config('wwwroot') . 'collection/index.php',
    'count' => $data->count,
    'limit' => $data->limit,
    'offset' => $data->offset,
    'firsttext' => '',
    'previoustext' => '',
    'nexttext' => '',
    'lasttext' => '',
    'numbersincludefirstlast' => false,
    'resultcounttextsingular' => get_string('collection', 'collection'),
    'resultcounttextplural' => get_string('collections', 'collection'),
));

$smarty = smarty(array('paginator'));
$smarty->assign('collections', $data->data);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('strnocollectionsaddone',
    get_string('nocollectionsaddone','collection','<a href="' . get_config('wwwroot') . 'collection/edit.php?new=1">', '</a>'));
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->display('collection/index.tpl');
