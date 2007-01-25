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
 * @subpackage artefact-internal
 * @author     Alastair Pharo <alastair@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myblogs');
require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
define('TITLE', get_string('blogsettings','artefact.blog'));
require_once('pieforms/pieform.php');
safe_require('artefact', 'blog');

$id = param_integer('id');
$blog = new ArtefactTypeBlog($id);

$form = pieform(array(
    'name' => 'editblog',
    'method' => 'post',
    'action' => '',
    'plugintype' => 'artefact',
    'pluginname' => 'blog',
    'elements' => array(
        'id' => array(
            'type'          => 'hidden',
            'value'         => $id
        ),
        'title' => array(
            'type'          => 'text',
            'title'         => get_string('blogtitle', 'artefact.blog'),
            'description'   => get_string('blogtitledesc', 'artefact.blog'),
            'rules' => array(
                'required'    => true
            ),
            'defaultvalue'  => $blog->get('title')
        ),
        'description' => array(
            'type'          => 'wysiwyg',
            'rows'          => 10,
            'cols'          => 70,
            'title'         => get_string('blogdesc', 'artefact.blog'),
            'description'   => get_string('blogdescdesc', 'artefact.blog'),
            'rules' => array(
                'required'    => false
            ),
            'defaultvalue'  => $blog->get('description')
        ),
        'commentsallowed' => array(
            'type'          => 'radio',
            'title'         => get_string('commentsallowed', 'artefact.blog'),
            'description'   => get_string('commentsalloweddesc', 'artefact.blog'),
            'options'       => array(
                0 => get_string('commentsallowedno', 'artefact.blog'),
                1 => get_string('commentsallowedyes', 'artefact.blog')
            ),
            'defaultvalue'  => ($blog->get('commentsallowed') ? 1 : 0)
        ),
        'commentsnotify' => array(
            'type'          => 'radio',
            'title'         => get_string('commentsnotify', 'artefact.blog'),
            'description'   => get_string('commentsnotifydesc', 'artefact.blog'),
            'options'       => array(
                0 => get_string('commentsnotifyno', 'artefact.blog'),
                1 => get_string('commentsnotifyyes', 'artefact.blog')
            ),
            'defaultvalue'  => ($blog->get('commentsnotify') ? 1 : 0)
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'value' => array(
                get_string('savesettings', 'artefact.blog'),
                get_string('cancel', 'artefact.blog')
            )
        )
    )
));

$smarty = smarty();
$smarty->assign_by_ref('editform', $form);
$smarty->assign_by_ref('blog', $blog);
$smarty->display('artefact:blog:settings.tpl');
exit;

/**
 * This function is called to update the blog details.
 */
function editblog_submit(Pieform $form, $values) {
    global $USER;
    
    ArtefactTypeBlog::edit_blog($USER, $values);

    redirect('/artefact/blog/view/?id=' . $values['id']);
}

/**
 * This function is called to cancel the form submission. It redirects the user
 * back to the blog.
 */
function editblog_cancel_submit() {
    $id = param_integer('id');
    redirect('/artefact/blog/view/?id=' . $id);
}

?>
