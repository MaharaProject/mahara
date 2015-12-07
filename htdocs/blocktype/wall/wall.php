<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-wall
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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

$smarty = smarty(array(), array(), array(), array('sidebars' => false));
$smarty->assign('displayname', display_name($owner));
$smarty->assign('instanceid', $instanceid);
$smarty->assign('owner', $owner);
$smarty->assign('wholewall', true);
$smarty->assign('ownwall', (!empty($USER) && $USER->get('id') == $owner->get('id')));
if ($posts = PluginBlocktypeWall::fetch_posts($block, true)) {
    $smarty->assign('wallposts', $posts);
}
else {
    $smarty->assign('wallmessage', get_string('noposts', 'blocktype.wall'));
}

$smarty->display('blocktype:wall:wallposts.tpl');
