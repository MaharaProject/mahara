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
define('TITLE', get_string('newblog','artefact.blog'));
require_once('pieforms/pieform.php');
safe_require('artefact', 'blog');

$form = pieform(array(
    'name' => 'newblog',
    'method' => 'post',
    'action' => '',
    'plugintype' => 'artefact',
    'pluginname' => 'blog',
    'elements' => array(
        'title' => array(
            'type'        => 'text',
            'title'       => get_string('blogtitle', 'artefact.blog'),
            'description' => get_string('blogtitledesc', 'artefact.blog'),
            'rules' => array(
                'required'    => true
            ),
            'help'        => true,
        ),
        'description' => array(
            'type'        => 'wysiwyg',
            'rows'        => 10,
            'cols'        => 70,
            'title'       => get_string('blogdesc', 'artefact.blog'),
            'description' => get_string('blogdescdesc', 'artefact.blog'),
            'rules' => array(
                'required'    => false
            ),
            'help'        => true,
        ),
        'tags'        => array(
            'type'        => 'tags',
            'title'       => get_string('tags'),
            'description' => get_string('tagsdesc'),
        ),
        'commentsallowed' => array(
            'type'        => 'radio',
            'title'       => get_string('commentsallowed', 'artefact.blog'),
            'description' => get_string('commentsalloweddesc', 'artefact.blog'),
            'options'     => array(
                0 => get_string('commentsallowedno', 'artefact.blog'),
                1 => get_string('commentsallowedyes', 'artefact.blog')
            ),
            'help'        => true,
        ),
        'commentsnotify' => array(
            'type'        => 'radio',
            'title'       => get_string('commentsnotify', 'artefact.blog'),
            'description' => get_string('commentsnotifydesc', 'artefact.blog'),
            'options'     => array(
                0 => get_string('commentsnotifyno', 'artefact.blog'),
                1 => get_string('commentsnotifyyes', 'artefact.blog')
            ),
            'help'        => true,
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'value' => array(
                get_string('createblog', 'artefact.blog'),
                get_string('cancel', 'artefact.blog')
            )
        )
    )
));

$smarty =& smarty();
$smarty->assign_by_ref('newblogform', $form);
$smarty->display('artefact:blog:new.tpl');
exit;

/**
 * This function gets called to submit the new blog.
 *
 * @param array
 */
function newblog_submit(Pieform $form, $values) {
    global $USER;

    ArtefactTypeBlog::new_blog($USER, $values);
    redirect('/artefact/blog/');
}

/**
 * This function gets called to cancel a submission.
 */
function newblog_cancel_submit() {
    redirect('/artefact/blog/');
}

?>
