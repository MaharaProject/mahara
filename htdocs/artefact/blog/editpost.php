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

// The createid is used to upload and attach files
$createid = $SESSION->get('createid');
if (empty($createid)) {
    $createid = 0;
}
$SESSION->set('createid', $createid + 1);

// For a new post, the 'blog' parameter will be set to the blog's artefact id.
// For an existing post, the 'post' parameter will be set to the blogpost's artefact id.

$blogpost = param_integer('blogpost', 0);
if (!$blogpost) {
    $blog = param_integer('blog');
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


// This form just has the main text inputs and no submit button.  The
// submit and cancel buttons are in their own form at the bottom of
// the page.

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
    )
));



// Strings used in the javascript

$getstring = quotestrings(array(
    'mahara' => array(
    ),
    'artefact.file' => array(
        'myfiles',
    ),
    'artefact.blog' => array(
        'attach',
        'blogpost',
        'browsemyfiles',
        'nofilesattachedtothispost',
        'remove',
    )));

// Insert this automatically sometime.
$copyright = get_field('site_content', 'content', 'name', 'uploadcopyright');
$wwwroot = get_config('wwwroot');


$javascript = <<< EOF


// The file uploader uploads files to the list of blog post attachments
var copyrightnotice = '{$copyright}';
var uploader = new FileUploader('uploader', 'upload.php', {$getstring['blogpost']}, false, 
                                attachtopost, fileexists);
uploader.createid = {$createid};




// File browser instance allows users to attach files from the my files area
var browser = null;
var browsebutton = INPUT({'id':'browsebutton', 'type':'button', 'value':{$getstring['browsemyfiles']},
                          'onclick':browsemyfiles});
function browsemyfiles() {
    hideElement('browsebutton');
    insertSiblingNodesAfter('browsebutton', H3(null, {$getstring['myfiles']}));
    browser = new FileBrowser('filebrowser', '{$wwwroot}artefact/file/myfiles.json.php', 
                              function () {}, {$getstring['attach']}, attachtopost);
    browser.init();
}
addLoadEvent(function () {insertSiblingNodesBefore('filebrowser', browsebutton);});




// List of attachments to the blog post
var attached = new TableRenderer(
    'attachedfiles',
    'attachedfiles.json.php',
    [
     'title',
     'description',
     function (r) { 
         return TD(null, INPUT({'type':'button', 'value':{$getstring['remove']},
                                'onclick':"removefrompost('attached_old:"+r.id+"')"}));
     }
    ]
);
attached.emptycontent = {$getstring['nofilesattachedtothispost']};
attached.paginate = false;
attached.blogpost = {$blogpost};
attached.statevars.push('blogpost');
attached.rowfunction = function (r) { return TR({'id':'attached_old:' + r.id}); };
attached.updateOnLoad();


// Show/hide the 'no attachments' message if there are no/some attachments
function checknoattachments() {
    if (attached.tbody.hasChildNodes()) {
        hideElement(attached.table.previousSibling);
        showElement(attached.table);
    }
    else {
        showElement(attached.table.previousSibling);
        hideElement(attached.table);
    }
}


// Add a newly uploaded file to the attached files list.
function attachtopost(data) {
    var rowid = data.uploadnumber ? 'uploaded:' + data.uploadnumber : 'existing:' + data.id;
    appendChildNodes(attached.tbody,
                     TR({'id':rowid},
                        map(partial(TD,null), 
                            [data.title, data.description,
                             INPUT({'type':'button', 'value':{$getstring['remove']},
                                'onclick':"removefrompost('"+rowid+"')"})])));
    checknoattachments();
}


// Remove a row from the attached files list.
function removefrompost(rowid) {
    removeElement(rowid);
    checknoattachments();
}



// Check if there's already a file attached to the post with the given name
function fileexists(name) {
    return false;
}



EOF;

$smarty = smarty(array('tablerenderer', 'artefact/file/js/uploader.js', 'artefact/file/js/filebrowser.js'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign_by_ref('textinputform', $form);
$smarty->assign('pagetitle', $pagetitle);
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
