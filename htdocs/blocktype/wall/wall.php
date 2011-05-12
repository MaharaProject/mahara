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
 * @subpackage blocktype-wall
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('view.php');
safe_require('blocktype', 'wall');

define('TITLE', get_string('viewwall', 'blocktype.wall'));

$instanceid = param_integer('id');
$reply = param_boolean('reply', false); // TODO send this to the form 
$postid = 0;
if ($reply) {
    $postid = param_integer('postid'); // TODO just fetch this thread
}
$postid = param_integer('postid', 0);
$block = new BlockInstance($instanceid);
$view = $block->get_view();
$owner = $view->get_owner_object();
$owner->displayname = display_name($owner);

$smarty = smarty(array(), array(), array(), array('sidebars' => false));
$smarty->assign('instanceid', $instanceid);
$smarty->assign('owner', $owner);
$smarty->assign('wholewall', true);
$smarty->assign('ownwall', (!empty($USER) && $USER->get('id') == $owner->id));
if ($posts = PluginBlocktypeWall::fetch_posts($block)) {
    $smarty->assign('wallposts', $posts);
}
else {
    $smarty->assign('wallmessage', get_string('noposts', 'blocktype.wall'));
}

$smarty->display('blocktype:wall:wallposts.tpl');
