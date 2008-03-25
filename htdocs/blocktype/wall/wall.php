<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage blocktype-wall
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('view.php');
safe_require('blocktype', 'wall');

define('TITLE', get_string('viewwall', 'blocktype.wall'));

$instanceid = param_integer('id');
$block = new BlockInstance($instanceid);
$view = $block->get_view();
$owner = $view->get_owner_object();
$owner->displayname = display_name($owner);

$smarty = smarty();
$smarty->assign('owner', $owner);
$smarty->assign('wholewall', true);
if ($posts = PluginBlocktypeWall::fetch_posts($block)) {
    $smarty->assign('wallposts', $posts);
}
else {
    $smarty->assign('wallmessage', get_string('noposts', 'blocktype.wall'));
}

$smarty->display('blocktype:wall:wallposts.tpl');

?>

