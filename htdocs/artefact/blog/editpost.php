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

/*
 * Files uploaded to blog posts will be stored temporarily in the
 * artefact/blog directory under the dataroot until the blog post is
 * saved.  This createid is used to ensure that all of these newly
 * uploaded files get unique filenames.
 */
$createid = $SESSION->get('createid');
if (empty($createid)) {
    $createid = 0;
}
$SESSION->set('createid', $createid + 1);


/* 
 * For a new post, the 'blog' parameter will be set to the blog's
 * artefact id.  For an existing post, the 'blogpost' parameter will
 * be set to the blogpost's artefact id.
 */
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



/*
 * The main form has the text inputs and no submit button.  The submit
 * and cancel buttons are in their own form at the bottom of the page,
 * with the file upload form appearing in between.
 */
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
            'cols' => 70,
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
            'defaultvalue' => $checked
        ),
    )
));



/*
 * Strings used in the inline javascript for this page.
 */
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



// These variables are needed by file.js.  They should really be set
// automatically when file.js is included.
$copyright = get_field('site_content', 'content', 'name', 'uploadcopyright');
$wwwroot = get_config('wwwroot');



/*
 * Javascript specific to this page.  Creates the list of files
 * attached to the blog post.
 */
$javascript = <<<EOF



// The file uploader uploads files to the list of blog post attachments
var copyrightnotice = '{$copyright}';
var uploader = new FileUploader('uploader', 'upload.php', null, {$getstring['blogpost']}, false, 
                                attachtopost, fileattached);
uploader.createid = {$createid};



// File browser instance allows users to attach files from the my files area
var browser = null;
var browsebutton = INPUT({'id':'browsebutton', 'type':'button', 'class':'button', 
                          'value':{$getstring['browsemyfiles']}, 'onclick':browsemyfiles});
function browsemyfiles() {
    hideElement('browsebutton');
    insertSiblingNodesAfter('browsebutton', H3(null, {$getstring['myfiles']}));
    showElement('filebrowser');
    browser = new FileBrowser('filebrowser', '{$wwwroot}artefact/file/myfiles.json.php', null, 
                              function () {}, {$getstring['attach']}, attachtopost);
    browser.init();
}
addLoadEvent(function () {insertSiblingNodesBefore('filebrowser', browsebutton);});



// List of attachments to the blog post
var attached = new TableRenderer(
    'attachedfiles',
    'attachedfiles.json.php',
    [
     function (r) { return TD(null, IMG({'src':config.themeurl + r.artefacttype + '.gif'})); },
     'title',
     'description',
     function (r) { 
         return TD(null, INPUT({'type':'button', 'class':'button',
                                'value':{$getstring['remove']},
                                'onclick':"removefrompost('artefact:"+r.id+"')"}));
     }
    ]
);
attached.emptycontent = {$getstring['nofilesattachedtothispost']};
attached.paginate = false;
attached.blogpost = {$blogpost};
attached.statevars.push('blogpost');
attached.rowfunction = function (r) { return TR({'id':'artefact:' + r.id}); };
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

// Currently this function does not check whether names of files
// attached from my files clash with files already in the attached
// files list.  This should be done here if names of attached files
// need to be unique.
function attachtopost(data) {
    var rowid = data.uploadnumber ? 'uploaded:' + data.uploadnumber : 'artefact:' + data.id;
    if (fileattached_id(rowid) || data.error) {
        return;
    }
    appendChildNodes(attached.tbody,
                     TR({'id':rowid},
                        map(partial(TD,null), 
                            [IMG({'src':config.themeurl+'unknown.gif'}), data.title, data.description,
                             INPUT({'type':'button', 'class':'button',
                                    'value':{$getstring['remove']},
                                    'onclick':"removefrompost('"+rowid+"')"})])));
    checknoattachments();
}


// Remove a row from the attached files list.
function removefrompost(rowid) {
    removeElement(rowid);
    checknoattachments();
}


// Check if there's already a file attached to the post with the given name
function fileattached(filename) {
    return some(map(function (e) { return e.firstChild; }, attached.tbody.childNodes),
                function (cell) { return scrapeText(cell) == filename; });
}


// Check if there's already a file attached to the post with the given id
function fileattached_id(id) {
    return some(attached.tbody.childNodes, function (r) { return getNodeAttribute(r,'id') == id; });
}


// Save the blog post.
function saveblogpost() {
    var data = {'title' : $('editpost_title').value,
                'draft' : $('editpost_thisisdraft').checked,
                'createid'  : {$createid},
                'blog'  : {$blog},
                'blogpost'  : {$blogpost}};
    // attachments
    var uploads = [];
    var artefacts = [];
    for (var i = 0; i < attached.tbody.childNodes.length; i++) {
        var idparts = attached.tbody.childNodes[i].id.split(':');
        if (idparts[0] == 'artefact') {
            artefacts.push(idparts[1]);
        }
        else { // uploaded file
            var record = {'id':idparts[1],
                          'title':scrapeText(attached.tbody.childNodes[i].childNodes[1]),
                          'description':scrapeText(attached.tbody.childNodes[i].childNodes[2])};
            uploads.push(record);
        }
    }
    data.uploads = serializeJSON(uploads);
    data.artefacts = serializeJSON(artefacts);
    // content
    if (typeof(tinyMCE) != 'undefined') { 
        tinyMCE.triggerSave();
    }
    data.body = $('editpost_description').value;
    sendjsonrequest('saveblogpost.json.php', data,
                    function () { window.location = '{$wwwroot}artefact/blog/view/?id={$blog}';});
}


function canceledit() {  // Uploaded files will deleted by cron cleanup.
     window.location = '{$wwwroot}artefact/blog/view/?id={$blog}';
}

EOF;



$smarty = smarty(array('tablerenderer', 'artefact/file/js/file.js'));
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
function editpost_submit(Pieform $form, array $values) {
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
