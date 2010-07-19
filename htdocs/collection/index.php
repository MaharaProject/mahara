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
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('mycollections','collection'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'collection');
define('SECTION_PAGE', 'mycollections');
require_once('collection.php');

// check that My Collections is enabled in the config
// if not as the user is trying to access this illegally
if (!get_config('allowcollections')) {
    die();
}

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

$collections = collection_get_user_collections($offset, $limit);
collection_build_list_html($collections);

$smarty = smarty(array('paginator'));
if ($collections['count'] > 0) {
    $smarty->assign('collections', $collections);
}
$smarty->assign('strnocollectionsaddone',
    get_string('nocollectionsaddone','collection','<a href="' . get_config('wwwroot') . 'collection/create.php">', '</a>'));
$smarty->assign('PAGEHEADING', hsc(get_string('mycollections', 'collection')));
$smarty->display('collection/index.tpl');

?>
