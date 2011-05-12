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
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'content/blogs');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'blog');
define('SECTION_PAGE', 'settings');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
define('TITLE', get_string('blogsettings','artefact.blog'));
require_once('pieforms/pieform.php');
safe_require('artefact', 'blog');

$id = param_integer('id');
$blog = new ArtefactTypeBlog($id);
$blog->check_permission();
if ($blog->get('locked')) {
    throw new AccessDeniedException(get_string('submittedforassessment', 'view'));
}


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
                'maxlength'   => 65536,
                'required'    => false
            ),
            'defaultvalue'  => $blog->get('description')
        ),
        'tags'       => array(
            'defaultvalue' => $blog->get('tags'),
            'type'         => 'tags',
            'title'        => get_string('tags'),
            'description'  => get_string('tagsdescprofile'),
            'help' => true,
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

if (!$USER->get_account_preference('multipleblogs')
    && count_records('artefact', 'artefacttype', 'blog', 'owner', $USER->get('id')) == 1) {
    $smarty->assign('enablemultipleblogstext', 1);
}

$smarty->assign_by_ref('editform', $form);
$smarty->assign_by_ref('blog', $blog);
$smarty->assign('PAGEHEADING', TITLE);
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
