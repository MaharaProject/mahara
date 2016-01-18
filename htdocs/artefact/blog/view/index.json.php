<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-blog
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
safe_require('artefact', 'blog');

$id = param_integer('id');
$limit = param_integer('limit', 5);
$setlimit = param_integer('setlimit', 0);
$offset = param_integer('offset', 0);

if (!$USER->can_edit_artefact(new ArtefactTypeBlog($id))) {
    json_reply(true, get_string('accessdenied', 'error'));
}

$posts = ArtefactTypeBlogPost::get_posts($id, $limit, $offset);
$template = 'artefact:blog:posts.tpl';
$pagination = array(
    'baseurl'    => get_config('wwwroot') . 'artefact/blog/view/index.php?id=' . $id,
    'id'         => 'blogpost_pagination',
    'jsonscript' => 'artefact/blog/view/index.json.php',
    'datatable'  => 'postlist',
    'setlimit'   => $setlimit,
);
ArtefactTypeBlogPost::render_posts($posts, $template, array(), $pagination);

json_reply(false, array('data' => $posts));
