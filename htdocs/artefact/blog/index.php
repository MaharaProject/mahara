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
 * @subpackage artefact-blog
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/blogs');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'blog');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'blog');

if ($delete = param_integer('delete', 0)) {
    $blog = artefact_instance_from_id($delete);
    if ($blog instanceof ArtefactTypeBlog) {
        $blog->check_permission();
        $blog->delete();
        $SESSION->add_ok_msg(get_string('blogdeleted', 'artefact.blog'));
    }
}

$blogs = (object) array(
    'offset' => param_integer('offset', 0),
    'limit'  => param_integer('limit', 10),
);

list($blogs->count, $blogs->data) = ArtefactTypeBlog::get_blog_list($blogs->limit, $blogs->offset);

// If the user has exactly one blog, skip the blog listing and display it
if (!$delete && $blogs->offset == 0 && !empty($blogs->data) && count($blogs->data) == 1) {
    define('TITLE', get_string('viewblog','artefact.blog'));
    define('SECTION_PAGE', 'view');

    $record = end($blogs->data);
    $id = $record->id;
    $blog = new ArtefactTypeBlog($id, $record);
    // This javascript is used to generate a list of blog posts.
    $js = '';
    if ($blog->count_children()) {
        $js = require(get_config('docroot') . 'artefact/blog/view/index.js.php');
    }

    $smarty = smarty(array('tablerenderer'));
    $smarty->assign_by_ref('blog', $blog);
    $smarty->assign_by_ref('INLINEJAVASCRIPT', $js);
    $smarty->assign('PAGEHEADING', hsc($blog->get('title')));
    $smarty->assign('strnopostsaddone',
                    get_string('nopostsaddone', 'artefact.blog',
                               '<a href="' . get_config('wwwroot') . 'artefact/blog/post.php?blog=' . $blog->get('id') . '">', '</a>'));
    $smarty->display('artefact:blog:view.tpl');
    exit;
}

define('TITLE', get_string('myblogs','artefact.blog'));
define('SECTION_PAGE', 'index');

ArtefactTypeBlog::build_blog_list_html($blogs);

$smarty = smarty(array('paginator'));
$smarty->assign_by_ref('blogs', $blogs);
$smarty->assign('PAGEHEADING', hsc(get_string("myblogs", "artefact.blog")));
$smarty->assign('INLINEJAVASCRIPT', 'addLoadEvent(function() {' . $blogs->pagination_js . '});');
$smarty->display('artefact:blog:index.tpl');

?>
