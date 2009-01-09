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
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');

safe_require('artefact', 'blog');

/* 
 * For a new post, the 'blog' parameter will be set to the blog's
 * artefact id.  For an existing post, the 'blogpost' parameter will
 * be set to the blogpost's artefact id.
 */
$blogpost = param_integer('blogpost', 0);
if (!$blogpost) {
    $blog = param_integer('blog');
    if (!get_record('artefact', 'id', $blog, 'owner', $USER->get('id'))) {
        // Blog security is also checked closer to when blogs are added, this 
        // check ensures that malicious users do not even see the screen for 
        // adding a post to a blog that is not theirs
        throw new AccessDeniedException(get_string('youarenottheownerofthisblog', 'artefact.blog'));
    }
    $title = '';
    $description = '';
    $checked = '';
    $tags = array();
    $pagetitle = get_string('newblogpost', 'artefact.blog', get_field('artefact', 'title', 'id', $blog));
    $focuselement = 'title';
    define('TITLE', $pagetitle);
}
else {
    $blogpostobj = new ArtefactTypeBlogPost($blogpost);
    $blogpostobj->check_permission();
    $blog = $blogpostobj->get('parent');
    $title = $blogpostobj->get('title');
    $description = $blogpostobj->get('description');
    $tags = $blogpostobj->get('tags');
    $checked = !$blogpostobj->get('published');
    $pagetitle = get_string('editblogpost', 'artefact.blog');
    $focuselement = 'description'; // Doesn't seem to work with tinyMCE.
    define('TITLE', get_string('editblogpost','artefact.blog'));
}



/*
 * The main form has the text inputs and no submit button.  The submit
 * and cancel buttons are in their own form at the bottom of the page,
 * with the file upload form appearing in between.
 */
$textinputform = pieform(array(
    'name' => 'editpost',
    'method' => 'post',
    'action' => '',
    'autofocus' => $focuselement,
    'plugintype' => 'artefact',
    'pluginname' => 'blog',
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
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => $title,
        ),
        'description' => array(
            'type' => 'wysiwyg',
            'rows' => 20,
            'cols' => 70,
            'title' => get_string('postbody', 'artefact.blog'),
            'description' => get_string('postbodydesc', 'artefact.blog'),
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => $description,
        ),
        'tags'       => array(
            'defaultvalue' => $tags,
            'type'         => 'tags',
            'title'        => get_string('tags'),
            'description'  => get_string('tagsdesc'),
            'help' => true,
        ),
    )
));



/*
 * Strings used in the inline javascript for this page.
 */
$getstring = quotestrings(array(
    'mahara' => array(
    ),
    'artefact.blog' => array(
        'attach',
        'blogpost',
        'cancel',
        'mustspecifycontent',
        'mustspecifytitle',
        'name',
        'nofilesattachedtothispost',
        'remove',
        'update',
        'noimageshavebeenattachedtothispost',
    )));



// These variables are needed by file.js.  They should really be set
// automatically when file.js is included.
$copyright = get_field('site_content', 'content', 'name', 'uploadcopyright');
$copyright = json_encode($copyright);
$wwwroot = get_config('wwwroot');



/*
 * Javascript specific to this page.  Creates the list of files
 * attached to the blog post.
 */
$javascript = <<<EOF



// The file uploader uploads files to the list of blog post attachments
var copyrightnotice = {$copyright};
// The fourth parameter below is just a hack so that the user sees
// "Uploading file to blog post" in the upload status line.
var uploader = new FileUploader('uploader', 'upload.php', null, {$getstring['blogpost']}, false, 
                                attachtopost, fileattached);


// File browser instance allows users to attach files from the my files area
var browser = null;

function browsemyfiles() {
    hideElement('browsebuttonstuff');
    showElement('browsemyfiles');
    if (!elementDimensions('foldernav')) {
        browser = new FileBrowser('filebrowser', '{$wwwroot}artefact/file/myfiles.json.php', null, 
                                  function () {}, {$getstring['attach']}, attachtopost);
        browser.init();
        insertSiblingNodesBefore('foldernav', 
                                 INPUT({'type':'button','class':'button','value':{$getstring['cancel']},
                                        'onclick':function () {
                                     hideElement('browsemyfiles');
                                     showElement('browsebuttonstuff');
                                 }}));
    }
}

addLoadEvent(function () {connect('browsebutton', 'onclick', browsemyfiles);});





// List of attachments to the blog post
var attached = new TableRenderer(
    'attachedfiles',
    'attachedfiles.json.php',
    [
     function (r) { return TD(null, IMG({'src':get_themeurl('images/' + r.artefacttype + '.gif'),
                                         'alt':r.artefacttype})); },
     'title',
     'description',
     'tags',
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
attached.rowfunction = function (r, n) { return TR({'id':'artefact:' + r.id,
                                                    'class':'r'+(n%2)}); };
attached.updateOnLoad();


// Show/hide the 'no attachments' message if there are no/some attachments
function redrawAttachList() {
    if (attached.tbody.hasChildNodes()) {
        hideElement(attached.table.previousSibling);
        setDisplayForElement('', attached.table);
        //showElement(attached.table);
        // Make sure row classes alternate 'r1', 'r0', 'r1', etc.
        for (var i = 0; i < attached.tbody.childNodes.length; i++) {
            setElementClass(attached.tbody.childNodes[i], 'r'+(i+1)%2);
        }
    }
    else {
        showElement(attached.table.previousSibling);
        hideElement(attached.table);
    }
}


// Add a newly uploaded file to the attached files list.

var uploaddata = {};

// Currently this function does not check whether names of files
// attached from my files clash with files already in the attached
// files list.  This should be done here if names of attached files
// need to be unique.
function attachtopost(data) {
    var rowid = data.tempfilename ? 'uploaded:' + data.tempfilename : 'artefact:' + data.id;
    if (fileattached_id(rowid) || data.error) {
        return;
    }
    var tags;
    if (typeof(data.tags) == "string") {
        tags = data.tags;
    }
    else {
        tags = data.tags.join(', ');
    }

    appendChildNodes(
        attached.tbody,
        TR(
            {'id':rowid},
            map(
                partial(TD,null), 
                [
                    IMG({'src':get_themeurl('images/'+data.artefacttype+'.gif'), 'alt':data.artefacttype}), 
                    data.title,
                    data.description,
                    tags,
                    INPUT({'type':'button', 'class':'button', 'value':{$getstring['remove']}, 'onclick':"removefrompost('"+rowid+"')"})
                ]
            )
        )
    );
    uploaddata[rowid] = data;
    redrawAttachList();
}


// Remove a row from the attached files list.
function removefrompost(rowid) {
    removeElement(rowid);
    redrawAttachList();
}


// Check if there's already a file attached to the post with the given name
function fileattached(filename) {
    return some(map(function (e) { return e.childNodes[1]; }, attached.tbody.childNodes),
                function (cell) { return scrapeText(cell) == filename; });
}


// Check if there's already a file attached to the post with the given id
function fileattached_id(id) {
    return some(attached.tbody.childNodes, function (r) { return getNodeAttribute(r,'id') == id; });
}


// Save the blog post.
function saveblogpost() {
    // Hacky inline validation - see bug #380
    if ($('editpost_title').value == '') {
        alert({$getstring['mustspecifytitle']});
        return false;
    }
    var data = {'title' : $('editpost_title').value,
                'draft' : $('draftpost_thisisdraft').checked,
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
            var record = {
                'id':idparts[1],
                'data':uploaddata[attached.tbody.childNodes[i].id]
            };
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
    if (data.body == '') {
        alert({$getstring['mustspecifycontent']});
        return false;
    }
    data.tags = $('editpost_tags').value;
    sendjsonrequest('saveblogpost.json.php', data, 'POST', function (result) {
        if (result.error) {
            // Error messages should appear near the save button so
            // that users can actually see them.
            map(removeElement, getElementsByTagAndClassName('div', null, 'savecancel'));
            appendChildNodes('savecancel', DIV({'class':'error'}, result.message));
        }
        else {
            window.location = '{$wwwroot}artefact/blog/view/?id={$blog}';
        }
    });
}


function canceledit() {  // Uploaded files will deleted by cron cleanup.
     window.location = '{$wwwroot}artefact/blog/view/?id={$blog}';
}




// Override the image button on the tinyMCE editor.  Rather than the
// normal image popup, open up a modified popup which allows the user
// to select an image from the list of image files attached to the
// post.

// The contents of this function is stolen straight out of the tinyMCE
// code in tinymce/themes/advanced/editor_template_src.js
function getSelectedImgAttributes (editorid) {
    var src = "", alt = "", border = "", hspace = "", vspace = "", width = "", height = "", align = "";
    var title = "", onmouseover = "", onmouseout = "", action = "insert";
    var img = tinyMCE.imgElement;
    var inst = tinyMCE.getInstanceById(editorid);

    if (tinyMCE.selectedElement != null && tinyMCE.selectedElement.nodeName.toLowerCase() == "img") {
        img = tinyMCE.selectedElement;
        tinyMCE.imgElement = img;
    }

    if (img) {
        // Is it a internal MCE visual aid image, then skip this one.
        if (tinyMCE.getAttrib(img, 'name').indexOf('mce_') == 0)
            return true;

        src = tinyMCE.getAttrib(img, 'src');
        alt = tinyMCE.getAttrib(img, 'alt');

        // Try polling out the title
        if (alt == "")
            alt = tinyMCE.getAttrib(img, 'title');

        // Fix width/height attributes if the styles is specified
        if (tinyMCE.isGecko) {
            var w = img.style.width;
            if (w != null && w != "")
                img.setAttribute("width", w);

            var h = img.style.height;
            if (h != null && h != "")
                img.setAttribute("height", h);
        }

        border = tinyMCE.getAttrib(img, 'border');
        hspace = tinyMCE.getAttrib(img, 'hspace');
        vspace = tinyMCE.getAttrib(img, 'vspace');
        width = tinyMCE.getAttrib(img, 'width');
        height = tinyMCE.getAttrib(img, 'height');
        align = tinyMCE.getAttrib(img, 'align');
        onmouseover = tinyMCE.getAttrib(img, 'onmouseover');
        onmouseout = tinyMCE.getAttrib(img, 'onmouseout');
        title = tinyMCE.getAttrib(img, 'title');

        // Is realy specified?
        if (tinyMCE.isMSIE) {
            width = img.attributes['width'].specified ? width : "";
            height = img.attributes['height'].specified ? height : "";
        }

        src = eval(tinyMCE.settings['urlconverter_callback'] + "(src, img, true);");

        // Use mce_src if defined
        mceRealSrc = tinyMCE.getAttrib(img, 'mce_src');
        if (mceRealSrc != "") {
            src = mceRealSrc;

            if (tinyMCE.getParam('convert_urls'))
                src = eval(tinyMCE.settings['urlconverter_callback'] + "(src, img, true);");
        }

        action = "update";
    }
    return {'src' : src, 'alt' : alt, 'border' : border, 'hspace' : hspace, 'vspace' : vspace, 
            'width' : width, 'height' : height, 'align' : align, 'title' : title, 
            'onmouseover' : onmouseover, 'onmouseout' : onmouseout, 'action' : action};
}



// Get all the files in the attached files list that have been
// recognised as images.  This function is called by the the popup
// window, but needs access to the attachment list on this page
function attachedImageList() {
    // All the rows in the attached files list:
    var attachrows = getElementsByTagAndClassName('tbody', null, 'attachedfiles')[0].childNodes;
    // Go through the rows, and for all the rows where the first cell
    // contains an 'image' image, return the row id (id attribute) and
    // the filename (contents of the second cell)
    return map(function(r) { return {'id':r.id, 'name':scrapeText(r.childNodes[1])}; },
               filter(function(r) { return r.firstChild.firstChild.alt == 'image'; }, attachrows));
}


function imageSrcFromId(imageid) {
    var idparts = imageid.split(':');
    if (idparts[0] == 'artefact') {
        return config.wwwroot + 'artefact/file/download.php?file=' + idparts[1];
    }
    if (idparts[0] == 'uploaded') {
        return config.wwwroot + 'artefact/blog/downloadtemp.php?tempfile=' + idparts[1];
    }
    return '';
}

function imageIdFromSrc(src) {
    var artefactstring = 'download.php?file=';
    var ind = src.indexOf(artefactstring);
    if (ind != -1) {
        return 'artefact:' + src.substring(ind+artefactstring.length, src.length);
    }
    var uploadstring = 'downloadtemp.php?tempfile=';
    ind = src.indexOf(uploadstring);
    if (ind != -1) {
        return 'uploaded:' + src.substring(ind+uploadstring.length, src.length).split('&')[0];
    }
    return '';
}

var imageList = {};

function blogpostExecCommandHandler(editor_id, elm, command, user_interface, value) {
    var linkElm, imageElm, inst;
    switch (command) {
    case "mceImage":
        a = getSelectedImgAttributes(editor_id);

        imageList = attachedImageList();
        if (imageList.length == 0) {
            alert({$getstring['noimageshavebeenattachedtothispost']});
            return true;
        }

        var template = new Array();

        template['file'] = '{$wwwroot}artefact/blog/image_popup.php?src=\{\$src\}';
        template['width'] = 355;
        template['height'] = 265 + (tinyMCE.isMSIE ? 25 : 0);

        // Language specific width and height addons
        template['width'] += tinyMCE.getLang('lang_insert_image_delta_width', 0);
        template['height'] += tinyMCE.getLang('lang_insert_image_delta_height', 0);

        a.inline = "yes";
        tinyMCE.openWindow(template, a);

        return true;
    }
    return false;
}


EOF;


$draftform = pieform(array(
    'name' => 'draftpost',
    'plugintype' => 'artefact',
    'pluginname' => 'blog',
    'method' => 'post',
    'action' => '',
    'elements' => array(
        'thisisdraft' => array(
            'type' => 'checkbox',
            'title' => get_string('thisisdraft', 'artefact.blog'),
            'description' => get_string('thisisdraftdesc', 'artefact.blog'),
            'defaultvalue' => $checked,
            'help' => true,
        ),
    )
));



$smarty = smarty(array('tablerenderer', 'artefact/file/js/file.js', 'tinymce'), 
                 array(), array(), array('tinymcecommandcallback' => 'blogpostExecCommandHandler'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign_by_ref('textinputform', $textinputform);
$smarty->assign_by_ref('draftform', $draftform);
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
    if (
        (!empty($values['id']) && ArtefactTypeBlogPost::edit_post($USER, $values))
        || (empty($values['id']) && ArtefactTypeBlogPost::new_post($USER, $values))
    ) {
        // Redirect to the blog page.
        redirect('/artefact/blog/view/?id=' . $values['parent']);
    }

    redirect('/artefact/blog/');
}



/** 
 * This function get called to cancel the form submission. It returns to the
 * blog list.
 */
function editpost_cancel_submit() {
    $blog = param_integer('parent');
    redirect('/artefact/blog/view/?id=' . $blog);
}
 
?>
