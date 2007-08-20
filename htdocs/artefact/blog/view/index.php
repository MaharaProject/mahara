<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage artefact-blog
 * @author     Alastair Pharo <alastair@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
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

// This javascript is used to generate a list of blog posts.
$js = require('index.js.php'); 

$images = array('themepaths' => array('images/file.gif', 'images/image.gif'));

$smarty = smarty(array('tablerenderer'), array(), array(), $images);
$smarty->assign_by_ref('blog', $blog);
$smarty->assign_by_ref('editform', $form);
$smarty->assign_by_ref('INLINEJAVASCRIPT', $js);
$smarty->display('artefact:blog:view.tpl');

?>
