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
define('SECTION_PAGE', 'view');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
define('TITLE', get_string('viewblog','artefact.blog'));
safe_require('artefact', 'blog');

$id = param_integer('id');
$blog = new ArtefactTypeBlog($id);
$blog->check_permission();

// This javascript is used to generate a list of blog posts.
$js = '';
if ($blog->count_children()) {
    $js = require('index.js.php'); 
}

$smarty = smarty(array('tablerenderer'));
$smarty->assign_by_ref('blog', $blog);
$smarty->assign_by_ref('INLINEJAVASCRIPT', $js);
$smarty->assign('PAGEHEADING', hsc($blog->get('title')));
$smarty->assign('strnopostsaddone',
    get_string('nopostsaddone', 'artefact.blog',
    '<a href="' . get_config('wwwroot') . 'artefact/blog/post.php?blog=' . $blog->get('id') . '">', '</a>'));
$smarty->display('artefact:blog:view.tpl');

?>
