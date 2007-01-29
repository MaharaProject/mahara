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
    $createid = 1;
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
    $focuselement = 'title';
    define('TITLE', get_string('newblogpost','artefact.blog'));
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
            'rows' => 20,
            'cols' => 70,
            'title' => get_string('postbody', 'artefact.blog'),
            'description' => get_string('postbodydesc', 'artefact.blog'),
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => $description
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
        'absolutemiddle',
        'absolutebottom',
        'alignment',
        'attach',
        'baseline',
        'blogpost',
        'border',
        'bottom',
        'cancel',
        'dimensions',
        'horizontalspace',
        'insert',
        'insertimage',
        'left',
        'middle',
        'name',
        'nofilesattachedtothispost',
        'remove',
        'right',
        'texttop',
        'top',
        'update',
        'verticalspace',
        'noimageshavebeenattachedtothispost',
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
// The fourth parameter below is just a hack so that the user sees
// "Uploading file to blog post" in the upload status line.
var uploader = new FileUploader('uploader', 'upload.php', null, {$getstring['blogpost']}, false, 
                                attachtopost, fileattached);
uploader.createid = {$createid};




// File browser instance allows users to attach files from the my files area
var browser = null;

function browsemyfiles() {
    hideElement('browsebutton');
    showElement('browsemyfiles');
    if (!elementDimensions('foldernav')) {
        browser = new FileBrowser('filebrowser', '{$wwwroot}artefact/file/myfiles.json.php', null, 
                                  function () {}, {$getstring['attach']}, attachtopost);
        browser.init();
        insertSiblingNodesBefore('foldernav', 
                                 INPUT({'type':'button','class':'button','value':{$getstring['cancel']},
                                        'onclick':function () {
                                     hideElement('browsemyfiles');
                                     showElement('browsebutton');
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
                            [IMG({'src':get_themeurl('images/'+data.artefacttype+'.gif'),
                                  'alt':data.artefacttype}), 
                             data.title, data.description,
                             INPUT({'type':'button', 'class':'button',
                                    'value':{$getstring['remove']},
                                    'onclick':"removefrompost('"+rowid+"')"})])));
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
    var data = {'title' : $('editpost_title').value,
                'draft' : $('draftpost_thisisdraft').checked,
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
    sendjsonrequest('saveblogpost.json.php', data, function (result) { 
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
// normal image popup, open up a form below the blogpost body form
// which allows the user to select an image from the list of image
// files attached to the post.

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
// recognised as images.
function attachedImageList() {
    // All the rows in the attached files list:
    var attachrows = getElementsByTagAndClassName('tbody', null, 'attachedfiles')[0].childNodes;
    // Go through the rows, and for all the rows where the first cell
    // contains an 'image' image, return the row id (id attribute) and
    // the filename (contents of the second cell)
    return map(function(r) { return {'id':r.id, 'name':scrapeText(r.childNodes[1])}; },
               filter(function(r) { return r.firstChild.firstChild.alt == 'image'; }, attachrows));
}


function insertImage() {
    var form = $('insertimageform');
    var alt = scrapeText(form.imageselector.options[form.imageselector.selectedIndex]);
    var src = imageSrcFromId(form.imgid.value);
    var border = form.border.value;
    var vspace = form.vspace.value;
    var hspace = form.hspace.value;
    var height = form.height.value;
    var width = form.width.value;
    var align = form.align.value;
    // Insert image doesn't work in IE without first focusing the editor:
    tinyMCE.execCommand('mceFocus', false, 'mce_editor_0'); 
    tinyMCE.themes['advanced']._insertImage(src, alt, border, hspace, vspace, 
                                            width, height, align, '', '', '');
    replaceChildNodes('insertimage', null);
}


function resetImageData() {
	var form = $('insertimageform');
	form.width.value = form.height.value = "";	
}


var preloadImage = new Image();


function updateImageData() {
	var form = $('insertimageform');
	if (form.width.value == "") {
	    form.width.value = preloadImage.width;
    }
	if (form.height.value == "") {
	    form.height.value = preloadImage.height;
    }
}


function imageSrcFromId(imageid) {
    var idparts = imageid.split(':');
    if (idparts[0] == 'artefact') {
        return config.wwwroot + 'artefact/file/download.php?file=' + idparts[1];
    }
    if (idparts[0] == 'uploaded') {
        return config.wwwroot + 'artefact/blog/downloadtemp.php?uploadnumber=' + idparts[1] + 
            '&createid=' + {$createid};
    }
    return '';
}


function getImageData(imageid) {
	preloadImage = new Image();
    preloadImage.onload = updateImageData;
    preloadImage.onerror = function () {
        var form = $('insertimageform');
        form.width.value = form.height.value = "";
    };
    var imgsrc = imageSrcFromId(imageid);
    $('insertimageform').imgsrc.value = imgsrc;
    $('insertimageform').imgid.value = imageid;
	preloadImage.src = imgsrc;
}


function imageIdFromSrc(src) {
    var artefactstring = 'download.php?file=';
    var ind = src.indexOf(artefactstring);
    if (ind != -1) {
        return 'artefact:' + src.substring(ind+artefactstring.length, src.length);
    }
    var uploadstring = 'downloadtemp.php?uploadnumber=';
    ind = src.indexOf(uploadstring);
    if (ind != -1) {
        return 'uploaded:' + src.substring(ind+uploadstring.length, src.length).split('&')[0];
    }
    return '';
}

function imageSelector(src) {
    var imageid = imageIdFromSrc(src);
    var imagefiles = attachedImageList();
    if (imagefiles.length == 0) {
        return false;
    }
    else {
        var sel = SELECT({'class':'select', 'id':'imageselector'});
        appendChildNodes(sel, OPTION({'value':''}, '--'));
        for (var i = 0; i < imagefiles.length; i++) {
            if (imageid == imagefiles[i].id) {
                appendChildNodes(sel, OPTION({'value':imagefiles[i].id, 'selected':true}, 
                                             imagefiles[i].name));
            }
            else {
                appendChildNodes(sel, OPTION({'value':imagefiles[i].id}, imagefiles[i].name));
            }
        }
        sel.onchange = function () {
            resetImageData();
            $('insertimageform').imgid.value = sel.value;
            getImageData(sel.value);
        };
        return sel;
    }
}



function alignSelector(align) {
    var sel = SELECT({'name':'align', 'class':'select'});
    var options = {'' : '--',
                   'baseline': {$getstring['baseline']},
                   'top': {$getstring['top']},
                   'middle': {$getstring['middle']},
                   'bottom': {$getstring['bottom']},
                   'texttop': {$getstring['texttop']},
                   'absmiddle': {$getstring['absolutemiddle']},
                   'absbottom': {$getstring['absolutebottom']},
                   'left': {$getstring['left']},
                   'right': {$getstring['right']}};
    for (option in options) {
        if (align == option) {
            appendChildNodes(sel, OPTION({'value':option, 'selected':true}, options[option]));
        }
        else {
            appendChildNodes(sel, OPTION({'value':option}, options[option]));
        }
    }
    return sel;
}



function blogpostExecCommandHandler(editor_id, elm, command, user_interface, value) {
    var linkElm, imageElm, inst;
    switch (command) {
    case "mceImage":
        a = getSelectedImgAttributes(editor_id);
        var sel = imageSelector(a.src);
        if (!sel) {
            alert({$getstring['noimageshavebeenattachedtothispost']});
            return true;
        }
        var tbody = TBODY(null,
          TR(null, TH({'colSpan':2}, LABEL(null,{$getstring['insertimage']}))),
          TR(null, TH(null, LABEL(null,{$getstring['name']})),
             TD(null, sel)),
          TR(null, TH(null, LABEL(null,{$getstring['alignment']})),
             TD(null, alignSelector(a.align))),
          TR(null, TH(null, LABEL(null,{$getstring['dimensions']})),
             TD(null,INPUT({'type':'text', 'class':'text', 'name':'width', 'size':3, 'value':a.width}),
                ' x ', INPUT({'type':'text', 'class':'text', 'name':'height', 'size':3, 'value':a.height}))),
          TR(null, TH(null, LABEL(null,{$getstring['border']})),
             TD(null,INPUT({'type':'text', 'class':'text', 'name':'border', 'size':3, 'value':a.border}))),
          TR(null, TH(null, LABEL(null,{$getstring['verticalspace']})),
             TD(null,INPUT({'type':'text', 'class':'text', 'name':'vspace', 'size':3, 'value':a.vspace}))),
          TR(null, TH(null, LABEL(null,{$getstring['horizontalspace']})),
             TD(null,INPUT({'type':'text', 'class':'text', 'name':'hspace', 'size':3, 'value':a.hspace}))));

        var imageform = FORM({'id':'insertimageform'},
                             INPUT({'type':'hidden', 'name':'imgsrc', 'value':a.src}),
                             INPUT({'type':'hidden', 'name':'imgid', 'value':imageIdFromSrc(a.src)}),
                             TABLE(null,tbody));
        appendChildNodes(tbody, TR(null,TD({'colSpan':2},
                         INPUT({'type':'button', 'class':'button', 
                                'value':(a.src == '' ? {$getstring['insert']} : {$getstring['update']}),
                                'onclick':function () { insertImage(); }}),
                         INPUT({'type':'button', 'class':'button', 'value':{$getstring['cancel']},
                                'onclick':function () { replaceChildNodes('insertimage', null); }}))));
        replaceChildNodes('insertimage', imageform);

        return true;
    }
    return false;
}


EOF;


// Override the default Mahara tinyMCE.init();  Add an image button and
// the execcommand_callback.

$content_css = json_encode(theme_get_url('style/tinymce.css'));
$tinymceinit = <<<EOF
<script type="text/javascript">
tinyMCE.init({
    mode: "textareas",
    editor_selector: 'wysiwyg',
    button_tile_map: true,
    theme: "advanced",
    plugins: "table,emotions,iespell,inlinepopups",
    theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,forecolor,backcolor,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,hr,emotions,iespell,cleanup,separator,link,unlink,image",
    theme_advanced_buttons2 : "tablecontrols,separator,cut,copy,paste",
    theme_advanced_buttons3 : "fontselect,separator,fontsizeselect,separator,formatselect",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "center",
    content_css : {$content_css},
    execcommand_callback : "blogpostExecCommandHandler"
});
</script>
EOF;


$draftform = pieform(array(
    'name' => 'draftpost',
    'method' => 'post',
    'action' => '',
    'elements' => array(
        'thisisdraft' => array(
            'type' => 'checkbox',
            'title' => get_string('thisisdraft', 'artefact.blog'),
            'description' => get_string('thisisdraftdesc', 'artefact.blog'),
            'defaultvalue' => $checked
        ),
    )
));



$smarty = smarty(array('tablerenderer', 'artefact/file/js/file.js'), 
                 array(), array(), array('tinymceinit' => $tinymceinit));
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
    if ((!empty($values['id']) && ArtefactTypeBlogPost::edit_post($USER, $values))
        || (empty($values['id']) && ArtefactTypeBlogPost::new_post($USER, $values))) {
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
