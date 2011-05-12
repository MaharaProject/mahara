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
define('PUBLIC', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'blog');
require_once(get_config('docroot') . 'blocktype/lib.php');
require_once(get_config('docroot') . 'artefact/blog/blocktype/blog/lib.php');

$offset = param_integer('offset', 0);

if ($blockid = param_integer('block', null)) {
    $bi = new BlockInstance($blockid);
    if (!can_view_view($bi->get('view'))) {
        json_reply(true, get_string('accessdenied', 'error'));
    }
    $configdata = $bi->get('configdata');
    $limit  = isset($configdata['count']) ? $configdata['count'] : 5;
    $configdata['countcomments'] = true;
    $posts = ArtefactTypeBlogpost::get_posts($configdata['artefactid'], $limit, $offset, $configdata);
    $template = 'artefact:blog:viewposts.tpl';
    $pagination = array(
        'baseurl' => $bi->get_view()->get_url() . '&block=' . $blockid,
        'id' => 'blogpost_pagination_' . $blockid,
        'datatable' => 'postlist_' . $blockid,
        'jsonscript' => 'artefact/blog/posts.json.php',
    );
    $configdata['viewid'] = $bi->get('view');
    ArtefactTypeBlogpost::render_posts($posts, $template, $configdata, $pagination);
}
else {
    // No block, we're just rendering the blog by itself on view/artefact.php
    $limit  = param_integer('limit', ArtefactTypeBlog::pagination);
    $blogid = param_integer('artefact');
    $viewid = param_integer('view');
    if (!can_view_view($viewid)) {
        json_reply(true, get_string('accessdenied', 'error'));
    }
    $options = array('viewid' => $viewid);
    $posts = ArtefactTypeBlogpost::get_posts($blogid, $limit, $offset, $options);

    $template = 'artefact:blog:viewposts.tpl';
    $baseurl = get_config('wwwroot') . 'view/artefact.php?artefact=' . $blogid . '&view=' . $viewid;
    $pagination = array(
        'baseurl' => $baseurl,
        'id' => 'blogpost_pagination',
        'datatable' => 'postlist',
        'jsonscript' => 'artefact/blog/posts.json.php',
    );

    ArtefactTypeBlogpost::render_posts($posts, $template, $options, $pagination);
}


json_reply(false, array('data' => $posts));
