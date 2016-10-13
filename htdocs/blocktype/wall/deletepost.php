<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-wall
 * @author     Maxime Rigo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2009 Maxime Rigo
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('view.php');
safe_require('blocktype', 'wall');
define('TITLE', get_string('deletepost', 'blocktype.wall'));
$postid = param_integer('postid');
$return = param_variable('return');

if (!$wallpost = get_record('blocktype_wall_post', 'id', $postid)) {
    throw new NotFoundException();
}
if (!$instance = new BlockInstance($wallpost->instance)) {
    throw new NotFoundException();
}
$owner = $instance->get_view()->get('owner');
if (!PluginBlocktypeWall::can_delete_wallpost($wallpost->from, $owner)) {
    throw new AccessDeniedException();
}

$goto = ($return == 'wall')
    ? '/blocktype/wall/wall.php?id=' . $instance->get('id')
    : profile_url($owner);

$form = pieform(array(
    'name'     => 'deletepost',
    'renderer' => 'div',
    'autofocus' => false,
    'elements' => array(
        'title' => array(
            'value' => get_string('deletepostsure', 'blocktype.wall'),
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto'  => $goto,
        ),
    )
));


function deletepost_submit(Pieform $form, $values) {
    global $SESSION, $postid, $goto;
    delete_records('blocktype_wall_post', 'id', $postid);

    require_once('embeddedimage.php');
    EmbeddedImage::remove_embedded_images('wallpost', $postid);

    $SESSION->add_ok_msg(get_string('deletepostsuccess', 'blocktype.wall'));
    redirect($goto);
}


$smarty = smarty();
$smarty->assign('deleteform', $form);
$smarty->display('blocktype:wall:deletepost.tpl');
