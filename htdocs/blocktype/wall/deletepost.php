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
 * @author     Maxime Rigo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2009 Maxime Rigo
 * @copyright  (C) 2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('view.php');
safe_require('blocktype', 'wall');

$postid = param_integer('postid');
$return = param_variable('return');

$wallpost = get_record('blocktype_wall_post', 'id', $postid);
$instance = new BlockInstance($wallpost->instance);
$view = new View($instance->get('view'));
if (!PluginBlocktypeWall::can_delete_wallpost($wallpost->from, $view->get('owner'))) {
    throw new AccessDeniedException();
}

$goto = get_config('wwwroot');
$goto .= ($return == 'wall')
    ? '/blocktype/wall/wall.php?id=' . $instance->get('id')
    : '/user/view.php?id=' . $view->get('owner');

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
    $SESSION->add_ok_msg(get_string('deletepostsuccess', 'blocktype.wall'));
    redirect($goto);
}


$smarty = smarty();
$smarty->assign('deleteform', $form);
$smarty->assign('PAGEHEADING', get_string('deletepost', 'blocktype.wall'));
$smarty->display('blocktype:wall:deletepost.tpl');
