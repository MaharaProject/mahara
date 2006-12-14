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
define('MENUITEM', 'myblogs');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'blog');

$id = param_integer('id');
$blogpost = new ArtefactTypeBlogPost($id);
if ($blogpost->get('owner') != $USER->get('id')) {
    return;
}

$form = pieform(array(
    'name' => 'editpost',
    'method' => 'post',
    'action' => '',
    'elements' => array(
        'id' => array(
            'type' => 'hidden',
            'value' => $id,
            'rules' => array(
                'required' => true
            )
        ),
        'title' => array(
            'type' => 'text',
            'title' => get_string('posttitle', 'artefact.blog'),
            'description' => get_string('posttitledesc', 'artefact.blog'),
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => $blogpost->get('title')
        ),
        'description' => array(
            'type' => 'wysiwyg',
            'rows' => 10,
            'cols' => 80,
            'title' => get_string('postbody', 'artefact.blog'),
            'description' => get_string('postbodydesc', 'artefact.blog'),
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => $blogpost->get('description')
        ),
        'thisisdraft' => array(
            'type' => 'checkbox',
            'title' => get_string('thisisdraft', 'artefact.blog'),
            'description' => get_string('thisisdraftdesc', 'artefact.blog'),
            'checked' => !$blogpost->get('published')
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(
                get_string('save', 'artefact.blog'),
                get_string('cancel', 'artefact.blog')
            )
        )
    )
));

$smarty = smarty();
$smarty->assign_by_ref('editpostform', $form);
$smarty->display('artefact:blog:editpost.tpl');
exit;

/**
 * This function gets called to create a new blog post, and publish it
 * simultaneously.
 *
 * @param array
 */
function editpost_submit(array $values) {
    global $USER;

    $values['published'] = !$values['thisisdraft'];
    if (ArtefactTypeBlogPost::edit_post($USER, $values)) {
        // Redirect to the blog page.
        $id = get_field('artefact', 'parent', 'id', $values['id']);
        redirect(get_config('wwwroot') . 'artefact/blog/view/?id=' . $id);
    }

    redirect(get_config('wwwroot') . 'artefact/blog/list/');
}

/** 
 * This function get called to cancel the form submission. It returns to the
 * blog list.
 */
function editpost_cancel_submit() {
    $id = param_integer('id');
    redirect(get_config('wwwroot') . 'artefact/blog/view/?id=' . $id);
}
 
?>
