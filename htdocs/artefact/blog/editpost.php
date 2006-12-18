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

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'blog');

// For a new post, the 'blog' parameter will be set to the blog's artefact id.
// For an existing post, the 'post' parameter will be set to the blogpost's artefact id.

$blogpost = param_integer('blogpost', 0);
if (!$blogpost) {
    // This is a new post; get a create id so we can attach files to it.
    $createid = $SESSION->get('createid');
    if (empty($createid)) {
        $createid = 0;
    }
    $SESSION->set('createid', $createid + 1);
    $blog = param_integer('blog');
    $blogpost = 0;
    $title = '';
    $description = '';
    $checked = '';
    $pagetitle = 'newblogpost';
}
else {
    $blogpostobj = new ArtefactTypeBlogPost($blogpost);
    if ($blogpostobj->get('owner') != $USER->get('id')) {
        return;
    }
    $blog = $blogpostobj->get('parent');
    $title = $blogpostobj->get('title');
    $description = $blogpostobj->get('description');
    $checked = !$blogpostobj->get('published');
    $pagetitle = 'editblogpost';
}

$getstring = quotestrings(array(
    'mahara' => array(
    ),
    'artefact.blog' => array(
        'nofilesattachedtothispost',
    )));


$attachedhtml = '<h3>' . get_string('attachedfiles', 'artefact.blog') . "</h3>\n";
$attachedhtml .= "<table id='attachedfiles'><tbody><tr><td></td></tr></tbody></table>\n";

//$blogpostjs = $blogpost ? $blogpost : 'null';

$javascript = <<< EOF

var attached = new TableRenderer(
    'attachedfiles',
    'attachedfiles.json.php',
    [
     'title',
     'description',
     function () { return TD(null); }
    ]
);
attached.emptycontent = {$getstring['nofilesattachedtothispost']};
attached.paginate = false;
attached.blogpost = {$blogpost};
attached.statevars.push('blogpost');
attached.rowfunction = function (r) { return TR({'id':'attached_old_' + r.id}); };
attached.updateOnLoad();

EOF;

$form = pieform(array(
    'name' => 'editpost',
    'method' => 'post',
    'action' => '',
    'elements' => array(
        'parent' => array(
            'type' => 'hidden',
            'value' => $blog,
        ),
        'id' => array(
            'type' => 'hidden',
            'value' => $blogpost,
        ),
        'title' => array(
            'type' => 'text',
            'title' => get_string('posttitle', 'artefact.blog'),
            'description' => get_string('posttitledesc', 'artefact.blog'),
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => $title
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
            'defaultvalue' => $description
        ),
        'thisisdraft' => array(
            'type' => 'checkbox',
            'title' => get_string('thisisdraft', 'artefact.blog'),
            'description' => get_string('thisisdraftdesc', 'artefact.blog'),
            'checked' => $checked
        ),
        'attachedfiles' => array(
            'type' => 'html',
            'value' => $attachedhtml,
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

$smarty = smarty(array('tablerenderer'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('pagetitle', $pagetitle);
$smarty->assign_by_ref('editpostform', $form);
$smarty->display('artefact:blog:editpost.tpl');

/**
 * This function gets called to create a new blog post, and publish it
 * simultaneously.
 *
 * @param array
 */
function editpost_submit(array $values) {
    global $USER;

    $values['published'] = !$values['thisisdraft'];
    if ((!empty($values['id']) && ArtefactTypeBlogPost::edit_post($USER, $values))
        || (empty($values['id']) && ArtefactTypeBlogPost::new_post($USER, $values))) {
        // Redirect to the blog page.
        redirect(get_config('wwwroot') . 'artefact/blog/view/?id=' . $values['parent']);
    }

    redirect(get_config('wwwroot') . 'artefact/blog/list/');
}

/** 
 * This function get called to cancel the form submission. It returns to the
 * blog list.
 */
function editpost_cancel_submit() {
    $blog = param_integer('parent');
    redirect(get_config('wwwroot') . 'artefact/blog/view/?id=' . $blog);
}
 
?>
