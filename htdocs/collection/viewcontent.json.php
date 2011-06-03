<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2010 Catalyst IT Ltd and others; see:
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
 * @copyright  (C) 2006-2010 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');

$id = param_integer('id');
if (!can_view_view($id)) {
    json_reply('local', get_string('accessdenied', 'error'));
}
$firstview = new View($id);
$collection = $firstview->get('collection');

$smarty = smarty_core();
$smarty->assign('viewid', $id);
$smarty->assign('collectiontitle', $collection->get('name'));
$smarty->assign('ownername', $firstview->formatted_owner());
$smarty->assign('collectiondescription', $collection->get('description'));
$smarty->assign('viewcontent', $firstview->build_columns());
$smarty->assign('tags', $firstview->get('tags'));

$shownav = $collection->get('navigation');
if ($shownav) {
    if ($views = $collection->get('views')) {
        if (count($views['views']) > 1) {
            $smarty->assign_by_ref('collection', array_chunk($views['views'], 5));
        }
    }
}

ob_start();
$smarty->display('collection/viewcontent.tpl');
$html = ob_get_contents();
ob_end_clean();

json_reply(false, array(
    'message' => null,
    'html' => $html,
));
