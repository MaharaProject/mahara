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
 * @subpackage artefact-blog
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
safe_require('artefact', 'blog');
require_once(get_config('libroot') . 'pieforms/pieform.php');

$id = param_integer('id');
$limit = param_integer('limit', 5);
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
);
ArtefactTypeBlogPost::render_posts($posts, $template, array(), $pagination);

json_reply(false, array('data' => $posts));
